<?php

namespace App\Filament\Resources\Providers\Csv;

use App\Enums\Gender;
use App\Enums\ProviderStatus;
use App\Models\Practice;
use App\Models\Provider;
use DateTime;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * CSV export / import for providers (requirement.md §3.3).
 */
class ProviderCsv
{
    /**
     * Columns required in an import file, in export order.
     *
     * @var list<string>
     */
    public const array REQUIRED_COLUMNS = [
        'first_name',
        'last_name',
        'npi',
        'primary_email',
        'phone',
        'date_of_birth',
        'ssn_last4',
        'practice',
    ];

    /**
     * Optional import columns, in export order.
     *
     * @var list<string>
     */
    public const array OPTIONAL_COLUMNS = [
        'middle_name',
        'suffix',
        'gender',
        'status',
        'caqh_id',
        'primary_specialty',
        'secondary_specialty',
        'taxonomy_code',
    ];

    /**
     * All export columns in display order.
     *
     * @return list<string>
     */
    public static function columns(): array
    {
        return [...self::REQUIRED_COLUMNS, ...self::OPTIONAL_COLUMNS];
    }

    /**
     * Render providers as a CSV document.
     *
     * @param  iterable<Provider>  $providers
     */
    public static function toCsv(iterable $providers): string
    {
        $handle = fopen('php://temp', 'r+');

        fputcsv($handle, self::columns());

        foreach ($providers as $provider) {
            $row = self::row($provider);

            fputcsv($handle, array_map(
                static fn (string $column): mixed => $row[$column] ?? '',
                self::columns(),
            ));
        }

        rewind($handle);
        $csv = (string) stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    /**
     * Stream the CSV as a file download.
     *
     * @param  iterable<Provider>  $providers
     */
    public static function download(iterable $providers): StreamedResponse
    {
        $csv = self::toCsv($providers);

        return response()->streamDownload(
            static fn (): bool => print ($csv),
            'providers-'.now()->format('Ymd-His').'.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    /**
     * Import providers from a CSV file. Invalid rows are skipped and
     * reported; valid rows are created (sensitive fields encrypted).
     *
     * @return array{created: int, errors: list<string>}
     */
    public static function import(string $absolutePath): array
    {
        $handle = fopen($absolutePath, 'r');

        if ($handle === false) {
            return ['created' => 0, 'errors' => ['The file could not be opened.']];
        }

        $header = fgetcsv($handle);

        if ($header === false || $header === null || $header === []) {
            fclose($handle);

            return ['created' => 0, 'errors' => ['The file is empty.']];
        }

        $header = array_map(
            static fn (mixed $column): string => strtolower(trim((string) $column)),
            $header,
        );

        $missing = array_diff(self::REQUIRED_COLUMNS, $header);

        if ($missing !== []) {
            fclose($handle);

            return ['created' => 0, 'errors' => ['Missing required columns: '.implode(', ', $missing).'.']];
        }

        $created = 0;
        $errors = [];
        $rowNumber = 1;

        while (($values = fgetcsv($handle)) !== false) {
            $rowNumber++;

            if ($values === null || $values === [] || (count($values) === 1 && trim((string) $values[0]) === '')) {
                continue;
            }

            if (count($values) !== count($header)) {
                $errors[] = "Row {$rowNumber}: expected ".count($header).' columns, got '.count($values).'.';

                continue;
            }

            $data = array_combine($header, $values);

            $error = self::importRow($data, $rowNumber);

            if ($error !== null) {
                $errors[] = $error;

                continue;
            }

            $created++;
        }

        fclose($handle);

        return ['created' => $created, 'errors' => $errors];
    }

    /**
     * Create one provider from a CSV row, returning an error message on failure.
     *
     * @param  array<string, string>  $data
     */
    private static function importRow(array $data, int $rowNumber): ?string
    {
        $practiceName = trim(self::value($data, 'practice'));

        if ($practiceName === '') {
            return "Row {$rowNumber}: practice is required.";
        }

        $practice = Practice::query()->where('name', $practiceName)->first();

        if ($practice === null) {
            return "Row {$rowNumber}: unknown practice [{$practiceName}].";
        }

        $npi = trim(self::value($data, 'npi'));

        if (! preg_match('/^\d{10}$/', $npi)) {
            return "Row {$rowNumber}: NPI must be exactly 10 digits.";
        }

        foreach (['first_name', 'last_name', 'primary_email'] as $field) {
            if (trim(self::value($data, $field)) === '') {
                return "Row {$rowNumber}: {$field} is required.";
            }
        }

        if (! filter_var(trim(self::value($data, 'primary_email')), FILTER_VALIDATE_EMAIL)) {
            return "Row {$rowNumber}: primary_email must be a valid email address.";
        }

        $dateOfBirth = trim(self::value($data, 'date_of_birth'));

        if (! self::isValidDate($dateOfBirth)) {
            return "Row {$rowNumber}: date_of_birth must be a valid YYYY-MM-DD date.";
        }

        $ssnLast4 = trim(self::value($data, 'ssn_last4'));

        if (! preg_match('/^\d{4}$/', $ssnLast4)) {
            return "Row {$rowNumber}: ssn_last4 must be exactly 4 digits.";
        }

        $phone = trim(self::value($data, 'phone'));

        if ($phone === '') {
            return "Row {$rowNumber}: phone is required.";
        }

        $statusInput = trim(self::value($data, 'status'));
        $status = $statusInput === ''
            ? ProviderStatus::Pending
            : ProviderStatus::tryFrom(strtolower($statusInput));

        if (! $status instanceof ProviderStatus) {
            return "Row {$rowNumber}: unknown status [{$statusInput}].";
        }

        $genderInput = trim(self::value($data, 'gender'));
        $gender = $genderInput === '' ? null : Gender::tryFrom(strtolower($genderInput));

        if ($genderInput !== '' && $gender === null) {
            return "Row {$rowNumber}: unknown gender [{$genderInput}].";
        }

        try {
            Provider::create([
                'first_name' => trim(self::value($data, 'first_name')),
                'middle_name' => self::nullable($data, 'middle_name'),
                'last_name' => trim(self::value($data, 'last_name')),
                'suffix' => self::nullable($data, 'suffix'),
                'date_of_birth' => $dateOfBirth,
                'gender' => $gender,
                'ssn_last4' => $ssnLast4,
                'npi' => $npi,
                'caqh_id' => self::nullable($data, 'caqh_id'),
                'primary_email' => trim(self::value($data, 'primary_email')),
                'phone' => $phone,
                'status' => $status,
                'practice_id' => $practice->id,
                'primary_specialty' => self::nullable($data, 'primary_specialty'),
                'secondary_specialty' => self::nullable($data, 'secondary_specialty'),
                'taxonomy_code' => self::nullable($data, 'taxonomy_code'),
            ]);
        } catch (ValidationException $exception) {
            return 'Row '.$rowNumber.': '.implode(' ', Arr::flatten($exception->errors()));
        }

        return null;
    }

    /**
     * @return array<string, mixed>
     */
    private static function row(Provider $provider): array
    {
        return [
            'first_name' => $provider->first_name,
            'last_name' => $provider->last_name,
            'npi' => $provider->npi,
            'primary_email' => $provider->primary_email,
            'phone' => $provider->phone,
            'date_of_birth' => $provider->date_of_birth,
            'ssn_last4' => $provider->ssn_last4,
            'practice' => $provider->practice?->name ?? '',
            'middle_name' => $provider->middle_name ?? '',
            'suffix' => $provider->suffix ?? '',
            'gender' => $provider->gender?->value ?? '',
            'status' => $provider->status->value,
            'caqh_id' => $provider->caqh_id ?? '',
            'primary_specialty' => $provider->primary_specialty ?? '',
            'secondary_specialty' => $provider->secondary_specialty ?? '',
            'taxonomy_code' => $provider->taxonomy_code ?? '',
        ];
    }

    /**
     * @param  array<string, string>  $data
     */
    private static function value(array $data, string $key): string
    {
        return (string) ($data[$key] ?? '');
    }

    /**
     * @param  array<string, string>  $data
     */
    private static function nullable(array $data, string $key): ?string
    {
        $value = trim(self::value($data, $key));

        return $value === '' ? null : $value;
    }

    private static function isValidDate(string $value): bool
    {
        if ($value === '') {
            return false;
        }

        return DateTime::createFromFormat('!Y-m-d', $value)?->format('Y-m-d') === $value;
    }
}
