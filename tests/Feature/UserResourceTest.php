<?php

namespace Tests\Feature;

use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserResourceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a panel user that passes the role-based user policies.
     */
    private function createUserWithRole(string $role = 'Super Admin'): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate($role, 'web'));

        return $user;
    }

    public function test_user_list_page_renders_users_and_their_roles(): void
    {
        $admin = $this->createUserWithRole();

        $specialist = User::factory()->create([
            'name' => 'Sam Specialist',
            'email' => 'sam@example.com',
        ]);
        $specialist->assignRole(Role::findOrCreate('Specialist', 'web'));

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk()
            ->assertSee('Sam Specialist')
            ->assertSee('sam@example.com')
            ->assertSee('Specialist');
    }

    public function test_creating_a_user_hashes_the_password_and_assigns_roles(): void
    {
        $admin = $this->createUserWithRole();
        $specialistRole = Role::findOrCreate('Specialist', 'web');

        $this->actingAs($admin);

        Livewire::test(CreateUser::class)
            ->fillForm([
                'name' => 'New Specialist',
                'email' => 'new.specialist@example.com',
                'password' => 'secret-password',
                'is_active' => true,
                'two_factor_enabled' => false,
                'roles' => [$specialistRole->id],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $user = User::query()->where('email', 'new.specialist@example.com')->firstOrFail();

        $this->assertSame('New Specialist', $user->name);
        $this->assertTrue(Hash::check('secret-password', $user->password));
        $this->assertTrue($user->is_active);
        $this->assertTrue($user->hasRole('Specialist'));
    }

    public function test_editing_a_user_without_a_password_keeps_the_existing_password(): void
    {
        $admin = $this->createUserWithRole();

        $user = User::factory()->create([
            'name' => 'Before Edit',
            'password' => 'original-password',
        ]);
        $user->assignRole(Role::findOrCreate('Specialist', 'web'));

        $originalHash = $user->fresh()->password;

        $this->actingAs($admin);

        Livewire::test(EditUser::class, ['record' => $user->getKey()])
            ->fillForm([
                'name' => 'After Edit',
                'password' => '',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $user->refresh();

        $this->assertSame('After Edit', $user->name);
        $this->assertSame($originalHash, $user->password);
        $this->assertTrue(Hash::check('original-password', $user->password));
    }

    public function test_editing_a_user_can_replace_the_password_and_roles(): void
    {
        $admin = $this->createUserWithRole();

        $user = User::factory()->create(['password' => 'original-password']);
        $user->assignRole(Role::findOrCreate('Specialist', 'web'));

        $managerRole = Role::findOrCreate('Credentialing Manager', 'web');

        $this->actingAs($admin);

        Livewire::test(EditUser::class, ['record' => $user->getKey()])
            ->fillForm([
                'password' => 'brand-new-password',
                'roles' => [$managerRole->id],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $user->refresh();

        $this->assertTrue(Hash::check('brand-new-password', $user->password));
        $this->assertTrue($user->hasRole('Credentialing Manager'));
        $this->assertFalse($user->hasRole('Specialist'));
    }

    public function test_role_filter_limits_the_table(): void
    {
        $admin = $this->createUserWithRole();

        $specialist = User::factory()->create();
        $specialist->assignRole(Role::findOrCreate('Specialist', 'web'));

        $viewer = User::factory()->create();
        $viewer->assignRole(Role::findOrCreate('Read-only', 'web'));

        $this->actingAs($admin);

        Livewire::test(ListUsers::class)
            ->filterTable('roles', [Role::findOrCreate('Specialist', 'web')->id])
            ->assertCanSeeTableRecords([$specialist])
            ->assertCanNotSeeTableRecords([$viewer]);
    }

    public function test_users_without_a_role_are_denied_access_to_the_users_module(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/admin/users')->assertForbidden();
    }

    public function test_read_only_users_can_view_but_not_create_users(): void
    {
        $user = $this->createUserWithRole('Read-only');

        $this->actingAs($user)->get('/admin/users')->assertOk();
        $this->actingAs($user)->get('/admin/users/create')->assertForbidden();
    }

    public function test_credentialing_managers_cannot_manage_users(): void
    {
        $manager = $this->createUserWithRole('Credentialing Manager');

        $this->actingAs($manager)->get('/admin/users')->assertOk();
        $this->actingAs($manager)->get('/admin/users/create')->assertForbidden();
    }

    public function test_inactive_users_cannot_access_the_panel(): void
    {
        $user = $this->createUserWithRole();
        $user->update(['is_active' => false]);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_users_cannot_delete_their_own_account(): void
    {
        $admin = $this->createUserWithRole();
        $other = User::factory()->create();
        $other->assignRole(Role::findOrCreate('Specialist', 'web'));

        $this->assertFalse($admin->can('delete', $admin));
        $this->assertTrue($admin->can('delete', $other));
    }

    public function test_user_management_bulk_actions_are_hidden_from_read_only_users(): void
    {
        $viewer = $this->createUserWithRole('Read-only');

        $this->actingAs($viewer);

        Livewire::test(ListUsers::class)
            ->assertTableBulkActionHidden('activate')
            ->assertTableBulkActionHidden('deactivate')
            ->assertTableBulkActionHidden('delete');
    }

    public function test_administrators_can_activate_and_deactivate_users_in_bulk(): void
    {
        $admin = $this->createUserWithRole();

        $first = User::factory()->create(['is_active' => true]);
        $second = User::factory()->create(['is_active' => true]);

        $this->actingAs($admin);

        Livewire::test(ListUsers::class)
            ->assertTableBulkActionVisible('activate')
            ->assertTableBulkActionVisible('deactivate')
            ->callTableBulkAction('deactivate', [$first, $second]);

        $this->assertFalse($first->refresh()->is_active);
        $this->assertFalse($second->refresh()->is_active);
    }
}
