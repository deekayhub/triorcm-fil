<?php

namespace Tests\Feature;

use App\Enums\AttestationStatus;
use App\Enums\ProviderStatus;
use App\Filament\Resources\Providers\Csv\ProviderCsv;
use App\Filament\Resources\Providers\Pages\CreateProvider;
use App\Filament\Resources\Providers\Pages\EditProvider;
use App\Filament\Resources\Providers\Pages\ListProviders;
use App\Filament\Resources\Providers\Pages\ViewProvider;
use App\Models\Attestation;
use App\Models\Practice;
use App\Models\Provider;
use App\Models\ProviderLicense;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProviderResourceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a panel user that passes the role-based provider policies.
     */
    private function createUserWithRole(string $role = 'Super Admin'): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate($role, 'web'));

        return $user;
    }

    /**
     * Build a CSV row containing every export column.
     *
     * @param  array<string, string>  $overrides
     * @return array<string, string>
     */
    private function csvRow(array $overrides = []): array
    {
        $row = array_merge(array_fill_keys(ProviderCsv::columns(), ''), [
            'first_name' => 'John',
            'last_name' => 'Importer',
            'npi' => '1112223334',
            'primary_email' => 'john@example.com',
            'phone' => '555-000-1111',
            'date_of_birth' => '1990-01-01',
            'ssn_last4' => '1234',
            'practice' => 'Import Target Clinic',
        ]);

        return array_merge($row, $overrides);
    }

    public function test_provider_list_page_renders_for_authorized_users(): void
    {
        $user = $this->createUserWithRole();

        $provider = Provider::factory()->create([
            'first_name' => 'Zora',
            'middle_name' => null,
            'last_name' => 'Hale',
            'suffix' => null,
        ]);

        $this->actingAs($user)
            ->get('/admin/providers')
            ->assertOk()
            ->assertSee('Zora Hale')
            ->assertSee('Docs complete')
            ->assertSee('Attestation due');
    }

    public function test_provider_table_lists_every_record_with_default_filters(): void
    {
        $user = $this->createUserWithRole();
        $providers = Provider::factory()->count(3)->create();

        $this->actingAs($user);

        Livewire::test(ListProviders::class)
            ->assertCanSeeTableRecords($providers)
            ->assertCountTableRecords(3);
    }

    public function test_creating_a_provider_requires_a_valid_ten_digit_npi(): void
    {
        $user = $this->createUserWithRole();
        $practice = Practice::factory()->create();

        $this->actingAs($user);

        Livewire::test(CreateProvider::class)
            ->fillForm([
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
                'date_of_birth' => '1985-12-10',
                'ssn_last4' => '4321',
                'npi' => '12345',
                'primary_email' => 'ada@example.com',
                'phone' => '555-0100',
                'practice_id' => $practice->id,
            ])
            ->call('create')
            ->assertHasFormErrors(['npi']);

        $this->assertDatabaseCount('providers', 0);
    }

    public function test_creating_a_provider_persists_encrypted_sensitive_fields(): void
    {
        $user = $this->createUserWithRole();
        $practice = Practice::factory()->create();

        $this->actingAs($user);

        Livewire::test(CreateProvider::class)
            ->fillForm([
                'first_name' => 'Ada',
                'last_name' => 'Lovelace',
                'date_of_birth' => '1815-12-10',
                'ssn_last4' => '4321',
                'npi' => '1234567890',
                'primary_email' => 'ada@example.com',
                'phone' => '555-0100',
                'status' => ProviderStatus::Active->value,
                'practice_id' => $practice->id,
                // Clear the repeater's default empty item so it doesn't fail required validation.
                'licenses' => [],
                'locations' => [],
                'workHistory' => [],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $provider = Provider::query()->where('npi', '1234567890')->firstOrFail();

        $this->assertSame('Ada', $provider->first_name);
        $this->assertSame(ProviderStatus::Active, $provider->status);
        $this->assertSame($practice->id, $provider->practice_id);

        $raw = DB::table('providers')->where('id', $provider->id)->first();

        $this->assertNotSame('1815-12-10', $raw->date_of_birth, 'DOB must be encrypted at rest.');
        $this->assertNotSame('4321', $raw->ssn_last4, 'SSN last 4 must be encrypted at rest.');
    }

    public function test_editing_a_provider_saves_changes(): void
    {
        $user = $this->createUserWithRole();
        $provider = Provider::factory()->create(['first_name' => 'Before']);

        $this->actingAs($user);

        Livewire::test(EditProvider::class, ['record' => $provider->getKey()])
            ->fillForm(['first_name' => 'After'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('providers', [
            'id' => $provider->id,
            'first_name' => 'After',
        ]);
    }

    public function test_view_page_displays_every_profile_tab(): void
    {
        $user = $this->createUserWithRole();

        $provider = Provider::factory()->create([
            'first_name' => 'Zora',
            'middle_name' => null,
            'last_name' => 'Hale',
            'suffix' => null,
            'ssn_last4' => '4321',
        ]);

        $this->actingAs($user);

        Livewire::test(ViewProvider::class, ['record' => $provider->getKey()])
            ->assertSee('Basic Info')
            ->assertSee('Specialties')
            ->assertSee('Licenses and IDs')
            ->assertSee('Practice Locations')
            ->assertSee('Education and Work History')
            ->assertSee('Documents')
            ->assertSee('Enrollments')
            ->assertSee('Notes and Activity')
            ->assertSee('Zora')
            ->assertSee('Hale')
            ->assertSee($provider->npi);
    }

    public function test_mark_attested_action_sets_dates_and_logs_attestation(): void
    {
        $user = $this->createUserWithRole();

        Setting::set('attestation.cycle_days', 90);

        $provider = Provider::factory()->create([
            'attested_at' => null,
            'attestation_due_at' => null,
        ]);

        $this->actingAs($user);

        Livewire::test(ListProviders::class)
            ->callTableAction('markAttested', $provider);

        $provider->refresh();

        $this->assertSame(today()->toDateString(), $provider->attested_at->toDateString());
        $this->assertSame(today()->addDays(90)->toDateString(), $provider->attestation_due_at->toDateString());

        $attestation = Attestation::query()->where('provider_id', $provider->id)->sole();

        $this->assertSame(today()->toDateString(), $attestation->attested_at->toDateString());
        $this->assertSame(today()->addDays(90)->toDateString(), $attestation->due_at->toDateString());
        $this->assertSame($user->id, $attestation->attested_by);
        $this->assertSame(AttestationStatus::Current, $attestation->status);
    }

    public function test_soft_deleted_providers_can_be_restored_with_the_trashed_filter(): void
    {
        $user = $this->createUserWithRole();
        $provider = Provider::factory()->create();

        $this->actingAs($user);

        Livewire::test(ListProviders::class)
            ->callTableAction('delete', $provider);

        $this->assertSoftDeleted('providers', ['id' => $provider->id]);

        Livewire::test(ListProviders::class)
            ->filterTable('trashed', 'only')
            ->assertCanSeeTableRecords([$provider])
            ->callTableAction('restore', $provider);

        $this->assertDatabaseHas('providers', [
            'id' => $provider->id,
            'deleted_at' => null,
        ]);
    }

    public function test_status_filter_limits_the_table_to_matching_providers(): void
    {
        $user = $this->createUserWithRole();

        $active = Provider::factory()->create(['status' => ProviderStatus::Active]);
        $inactive = Provider::factory()->create(['status' => ProviderStatus::Inactive]);

        $this->actingAs($user);

        Livewire::test(ListProviders::class)
            ->filterTable('status', ProviderStatus::Active->value)
            ->assertCanSeeTableRecords([$active])
            ->assertCanNotSeeTableRecords([$inactive]);
    }

    public function test_license_state_filter_only_applies_once_chosen(): void
    {
        $user = $this->createUserWithRole();

        $california = Provider::factory()->create();
        ProviderLicense::factory()->create(['provider_id' => $california->id, 'state' => 'CA']);

        $texas = Provider::factory()->create();
        ProviderLicense::factory()->create(['provider_id' => $texas->id, 'state' => 'TX']);

        $this->actingAs($user);

        // An untouched filter must not hide any rows.
        Livewire::test(ListProviders::class)
            ->assertCanSeeTableRecords([$california, $texas])
            ->filterTable('license_state', 'CA')
            ->assertCanSeeTableRecords([$california])
            ->assertCanNotSeeTableRecords([$texas]);
    }

    public function test_bulk_actions_assign_a_specialist_and_change_status(): void
    {
        $user = $this->createUserWithRole();
        $specialist = User::factory()->create(['is_active' => true]);

        $first = Provider::factory()->create(['status' => ProviderStatus::Active]);
        $second = Provider::factory()->create(['status' => ProviderStatus::Active]);

        $this->actingAs($user);

        Livewire::test(ListProviders::class)
            ->callTableBulkAction('assignSpecialist', [$first, $second], [
                'assigned_user_id' => $specialist->id,
            ])
            ->callTableBulkAction('changeStatus', [$first, $second], [
                'status' => ProviderStatus::Inactive->value,
            ]);

        $this->assertSame($specialist->id, $first->refresh()->assigned_user_id);
        $this->assertSame($specialist->id, $second->refresh()->assigned_user_id);
        $this->assertSame(ProviderStatus::Inactive, $first->refresh()->status);
        $this->assertSame(ProviderStatus::Inactive, $second->refresh()->status);
    }

    public function test_csv_export_includes_the_header_and_every_provider_row(): void
    {
        $provider = Provider::factory()->create([
            'first_name' => 'Export',
            'middle_name' => null,
            'last_name' => 'Me',
            'suffix' => null,
        ]);

        $csv = ProviderCsv::toCsv([$provider]);

        $lines = array_values(array_filter(
            explode("\n", $csv),
            fn (string $line): bool => trim($line) !== '',
        ));

        $this->assertSame(implode(',', ProviderCsv::columns()), $lines[0]);
        $this->assertCount(2, $lines);
        $this->assertStringContainsString('Export', $lines[1]);
        $this->assertStringContainsString($provider->npi, $csv);
        $this->assertStringContainsString($provider->practice->name, $csv);
    }

    public function test_csv_import_creates_valid_rows_and_reports_skipped_ones(): void
    {
        $practice = Practice::factory()->create(['name' => 'Import Target Clinic']);

        $valid = $this->csvRow();
        $badNpi = $this->csvRow(['first_name' => 'Bad', 'npi' => '12345']);
        $unknownPractice = $this->csvRow(['first_name' => 'Ghost', 'practice' => 'Missing Clinic']);

        $path = tempnam(sys_get_temp_dir(), 'providers');

        file_put_contents($path, implode("\n", [
            implode(',', ProviderCsv::columns()),
            implode(',', $valid),
            implode(',', $badNpi),
            implode(',', $unknownPractice),
        ]));

        $result = ProviderCsv::import($path);

        unlink($path);

        $this->assertSame(1, $result['created']);
        $this->assertCount(2, $result['errors']);
        $this->assertStringContainsString('NPI must be exactly 10 digits', implode(' ', $result['errors']));
        $this->assertStringContainsString('unknown practice', implode(' ', $result['errors']));

        $imported = Provider::query()->where('npi', '1112223334')->firstOrFail();

        $this->assertSame('John', $imported->first_name);
        $this->assertSame($practice->id, $imported->practice_id);

        $raw = DB::table('providers')->where('id', $imported->id)->first();

        $this->assertNotSame('1990-01-01', $raw->date_of_birth, 'Imported DOB must be encrypted at rest.');
        $this->assertNotSame('1234', $raw->ssn_last4, 'Imported SSN last 4 must be encrypted at rest.');
    }

    public function test_users_without_a_role_are_denied_access_to_providers(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/providers')->assertForbidden();
    }

    public function test_read_only_users_can_view_but_not_create_providers(): void
    {
        $user = $this->createUserWithRole('Read-only');
        Provider::factory()->create();

        $this->actingAs($user)->get('/admin/providers')->assertOk();
        $this->actingAs($user)->get('/admin/providers/create')->assertForbidden();
    }
}
