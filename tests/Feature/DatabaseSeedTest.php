<?php

namespace Tests\Feature;

use App\Enums\DocumentStatus;
use App\Enums\EnrollmentStage;
use App\Models\Attestation;
use App\Models\Document;
use App\Models\Enrollment;
use App\Models\LoginEvent;
use App\Models\Note;
use App\Models\Payer;
use App\Models\Practice;
use App\Models\Provider;
use App\Models\ProviderLicense;
use App\Models\Setting;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DatabaseSeedTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Roles from requirement.md §12.1.
     *
     * @var list<string>
     */
    private const array ROLES = [
        'Super Admin',
        'Admin',
        'Credentialing Manager',
        'Specialist',
        'Read-only',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed();
    }

    public function test_seed_meets_the_demo_dataset_requirements(): void
    {
        // requirement.md §15: demo dataset sizes.
        $this->assertSame(5, Practice::count(), 'Expected 5 demo practices.');
        $this->assertSame(35, Provider::count(), 'Expected 35 demo providers.');
        $this->assertSame(120, Document::count(), 'Expected ~120 demo documents.');
        $this->assertSame(10, Payer::count(), 'Expected 10 demo payers.');
        $this->assertSame(60, Enrollment::count(), 'Expected ~60 demo enrollments.');
        $this->assertSame(35, Attestation::count(), 'Expected one attestation per provider.');
        $this->assertSame(30, LoginEvent::count(), 'Expected 30 login events.');
        $this->assertSame(15, Task::count(), 'Expected 15 tasks.');
        $this->assertSame(20, Note::count(), 'Expected 20 provider notes.');
        $this->assertSame(14, Setting::count(), 'Expected 14 default settings.');

        // requirement.md §15: 100+ audit log entries.
        $this->assertGreaterThanOrEqual(100, Activity::count(), 'Expected 100+ audit entries.');

        // Attestation dates spread green/amber/red for the dashboard.
        $this->assertEquals(
            ['current' => 15, 'due_soon' => 10, 'overdue' => 10],
            Attestation::query()->selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status')->all(),
            'Expected attestation statuses to be spread across current, due soon and overdue.',
        );

        // All five enrollment stages are represented.
        $stageCounts = Enrollment::query()->selectRaw('stage, count(*) c')->groupBy('stage')->pluck('c', 'stage');

        foreach (EnrollmentStage::cases() as $stage) {
            $this->assertGreaterThan(0, $stageCounts[$stage->value] ?? 0, "No enrollments in stage [{$stage->value}].");
        }
    }

    public function test_one_user_exists_per_role_with_shared_password(): void
    {
        $this->assertSame(5, User::count(), 'Expected exactly one user per role.');
        $this->assertSame(count(self::ROLES), Role::count(), 'Expected all five roles to be seeded.');

        foreach (self::ROLES as $role) {
            $user = User::query()
                ->whereHas('roles', fn ($query) => $query->where('name', $role))
                ->first();

            $this->assertNotNull($user, "No user assigned to role [{$role}].");
            $this->assertTrue(
                Hash::check('password', $user->password),
                "User [{$user->email}] does not have the demo password.",
            );
            $this->assertTrue($user->is_active, "User [{$user->email}] should be active.");
        }
    }

    public function test_sensitive_fields_are_encrypted_at_rest(): void
    {
        $provider = Provider::factory()->create([
            'date_of_birth' => '1985-03-12',
            'ssn_last4' => '4321',
        ]);

        $rawProvider = DB::table('providers')->where('id', $provider->id)->first();

        $this->assertNotSame('1985-03-12', $rawProvider->date_of_birth, 'DOB must not be stored in plaintext.');
        $this->assertNotSame('4321', $rawProvider->ssn_last4, 'SSN last 4 must not be stored in plaintext.');
        $this->assertSame('1985-03-12', Provider::query()->find($provider->id)->date_of_birth, 'DOB must round-trip.');
        $this->assertSame('4321', Provider::query()->find($provider->id)->ssn_last4, 'SSN last 4 must round-trip.');

        $practice = Practice::factory()->create(['tax_id' => '12-3456789']);

        $this->assertNotSame(
            '12-3456789',
            DB::table('practices')->where('id', $practice->id)->value('tax_id'),
            'Practice tax ID must not be stored in plaintext.',
        );
        $this->assertSame('12-3456789', Practice::query()->find($practice->id)->tax_id);

        $license = ProviderLicense::factory()->create([
            'provider_id' => $provider->id,
            'number' => 'DEA-AB1234567',
        ]);

        $this->assertNotSame(
            'DEA-AB1234567',
            DB::table('provider_licenses')->where('id', $license->id)->value('number'),
            'License number must not be stored in plaintext.',
        );
        $this->assertSame('DEA-AB1234567', ProviderLicense::query()->find($license->id)->number);
    }

    public function test_provider_npi_must_be_ten_digits(): void
    {
        try {
            Provider::factory()->create(['npi' => '12345']);

            $this->fail('A 5-digit NPI was accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('npi', $exception->errors());
        }
    }

    public function test_provider_npi_must_be_unique(): void
    {
        $existingNpi = Provider::query()->firstOrFail()->npi;

        try {
            Provider::factory()->create(['npi' => $existingNpi]);

            $this->fail('A duplicate NPI was accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('npi', $exception->errors());
        }
    }

    public function test_document_statuses_cover_every_state(): void
    {
        $statusCounts = Document::query()->selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status');

        foreach (DocumentStatus::cases() as $status) {
            $this->assertGreaterThan(
                0,
                $statusCounts[$status->value] ?? 0,
                "No documents in status [{$status->value}].",
            );
        }

        // Documents beyond the warning window are stored with a real file.
        $stored = Document::query()->where('status', DocumentStatus::Valid)->firstOrFail();

        Storage::disk('local')->assertExists($stored->file);
    }

    public function test_settings_and_required_relationships_are_seeded(): void
    {
        $this->assertSame(120, Setting::get('attestation.cycle_days'), 'Attestation cycle setting is wrong.');
        $this->assertSame(60, Setting::get('documents.expiry_warning_days'), 'Expiry warning setting is wrong.');
        $this->assertSame('TrioRCM', Setting::get('general.company_name'));

        // Every provider belongs to a practice and has licenses, locations and work history.
        $this->assertSame(0, Provider::query()->whereNull('practice_id')->count());
        $this->assertSame(0, Provider::query()->whereDoesntHave('licenses')->count());
        $this->assertSame(0, Provider::query()->whereDoesntHave('locations')->count());
        $this->assertSame(0, Provider::query()->whereDoesntHave('workHistory')->count());
    }
}
