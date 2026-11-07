<?php

namespace Tests\Feature;

use App\Enums\PracticeStatus;
use App\Enums\PracticeType;
use App\Filament\Resources\Practices\Pages\CreatePractice;
use App\Filament\Resources\Practices\Pages\EditPractice;
use App\Filament\Resources\Practices\Pages\ListPractices;
use App\Models\Practice;
use App\Models\Provider;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PracticeResourceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a panel user that passes the role-based practice policies.
     */
    private function createUserWithRole(string $role = 'Super Admin'): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate($role, 'web'));

        return $user;
    }

    public function test_practice_list_page_shows_practices_and_provider_counts(): void
    {
        $user = $this->createUserWithRole();

        $practice = Practice::factory()->create(['name' => 'Coastal Health Partners']);
        Provider::factory()->count(2)->create(['practice_id' => $practice->id]);

        $this->actingAs($user)
            ->get('/admin/practices')
            ->assertOk()
            ->assertSee('Coastal Health Partners');

        $this->actingAs($user);

        Livewire::test(ListPractices::class)
            ->assertCanSeeTableRecords([$practice])
            ->assertTableColumnStateSet('providers_count', 2, $practice);
    }

    public function test_creating_a_practice_encrypts_the_tax_id(): void
    {
        $user = $this->createUserWithRole();

        $this->actingAs($user);

        Livewire::test(CreatePractice::class)
            ->fillForm([
                'name' => 'Sunrise Medical Group',
                'type' => PracticeType::Group->value,
                'contact_person' => 'Dana Reed',
                'status' => PracticeStatus::Active->value,
                'address' => '100 Main Street',
                'city' => 'Springfield',
                'state' => 'IL',
                'zip' => '62701',
                'tax_id' => '12-3456789',
                'phone' => '555-0100',
                'email' => 'billing@sunrise.example.com',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $practice = Practice::query()->where('name', 'Sunrise Medical Group')->firstOrFail();

        $this->assertSame(PracticeType::Group, $practice->type);
        $this->assertSame('12-3456789', $practice->tax_id);

        $raw = DB::table('practices')->where('id', $practice->id)->first();

        $this->assertNotSame('12-3456789', $raw->tax_id, 'Tax ID must be encrypted at rest.');
    }

    public function test_editing_a_practice_saves_changes(): void
    {
        $user = $this->createUserWithRole();
        $practice = Practice::factory()->create();

        $this->actingAs($user);

        Livewire::test(EditPractice::class, ['record' => $practice->getKey()])
            ->fillForm(['city' => 'Shelbyville'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('practices', [
            'id' => $practice->id,
            'city' => 'Shelbyville',
        ]);
    }

    public function test_type_and_status_filters_limit_the_table(): void
    {
        $user = $this->createUserWithRole();

        $solo = Practice::factory()->create([
            'type' => PracticeType::Solo,
            'status' => PracticeStatus::Active,
        ]);

        $group = Practice::factory()->create([
            'type' => PracticeType::Group,
            'status' => PracticeStatus::Inactive,
        ]);

        $this->actingAs($user);

        Livewire::test(ListPractices::class)
            ->filterTable('type', PracticeType::Solo->value)
            ->assertCanSeeTableRecords([$solo])
            ->assertCanNotSeeTableRecords([$group]);

        Livewire::test(ListPractices::class)
            ->filterTable('status', PracticeStatus::Inactive->value)
            ->assertCanSeeTableRecords([$group])
            ->assertCanNotSeeTableRecords([$solo]);
    }

    public function test_users_without_a_role_are_denied_access_to_practices(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/practices')->assertForbidden();
    }

    public function test_read_only_users_can_view_but_not_create_practices(): void
    {
        $user = $this->createUserWithRole('Read-only');
        Practice::factory()->create();

        $this->actingAs($user)->get('/admin/practices')->assertOk();
        $this->actingAs($user)->get('/admin/practices/create')->assertForbidden();
    }
}
