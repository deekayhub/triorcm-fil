<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class SidebarNavigationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a panel user that passes the role-based resource policies.
     */
    private function createUserWithRole(): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('Super Admin', 'web'));

        return $user;
    }

    /**
     * Sidebar structure from requirement.md §1: label => path.
     *
     * @var array<string, string>
     */
    private const array SIDEBAR_ITEMS = [
        'Dashboard' => '/admin',
        'Providers' => '/admin/providers',
        'Practices' => '/admin/practices',
        'Documents' => '/admin/documents',
        'Enrollments' => '/admin/enrollments',
        'Attestations' => '/admin/attestations',
        'Tasks' => '/admin/tasks',
        'Payers' => '/admin/payers',
        'Reports' => '/admin/reports',
        'Notifications' => '/admin/notifications',
        'Users & Roles' => '/admin/users',
        'Audit Logs' => '/admin/audit-logs',
        'Login Activity' => '/admin/login-activity',
        'Settings' => '/admin/settings',
    ];

    /**
     * Navigation groups in the order required by requirement.md §1.
     *
     * @var list<string>
     */
    private const array NAVIGATION_GROUPS = [
        'Provider Management',
        'Credentialing',
        'Network',
        'Insights',
        'Security',
        'Settings',
    ];

    /**
     * Sidebar items that are roadmap-only and must show the "Soon" badge.
     *
     * @var list<string>
     */
    private const array ROADMAP_MODULES = [
        'Enrollments',
        'Attestations',
        'Tasks',
        'Payers',
        'Reports',
        'Notifications',
    ];

    public function test_every_sidebar_item_is_reachable(): void
    {
        $user = $this->createUserWithRole();

        foreach (self::SIDEBAR_ITEMS as $label => $path) {
            $response = $this->actingAs($user)->get($path);

            $response->assertOk("Expected sidebar item [{$label}] to be reachable at [{$path}].");
        }
    }

    public function test_sidebar_renders_every_navigation_item_label(): void
    {
        $user = $this->createUserWithRole();

        $response = $this->actingAs($user)->get('/admin');

        $response->assertOk();

        foreach (array_keys(self::SIDEBAR_ITEMS) as $label) {
            $response->assertSee($label);
        }
    }

    public function test_navigation_groups_appear_in_documented_order(): void
    {
        $user = $this->createUserWithRole();

        $html = $this->actingAs($user)->get('/admin')->assertOk()->getContent();

        $positions = [];

        foreach (self::NAVIGATION_GROUPS as $group) {
            $position = strpos($html, $group);

            $this->assertNotFalse($position, "Navigation group [{$group}] is missing from the sidebar.");

            $positions[] = $position;
        }

        $sorted = $positions;

        sort($sorted);

        $this->assertSame($sorted, $positions, 'Navigation groups are not in the documented order.');
    }

    public function test_sidebar_items_appear_in_documented_order(): void
    {
        $user = $this->createUserWithRole();

        $html = $this->actingAs($user)->get('/admin')->assertOk()->getContent();

        $positions = [];

        foreach (['Dashboard', 'Providers', 'Practices', 'Documents'] as $label) {
            $position = strpos($html, $label);

            $this->assertNotFalse($position, "Sidebar item [{$label}] is missing from the sidebar.");

            $positions[] = $position;
        }

        $sorted = $positions;

        sort($sorted);

        $this->assertSame($sorted, $positions, 'Sidebar items are not in the documented order.');
    }

    public function test_roadmap_modules_show_coming_soon_badge(): void
    {
        $user = $this->createUserWithRole();

        $html = $this->actingAs($user)->get('/admin')->assertOk()->getContent();

        $this->assertSame(
            count(self::ROADMAP_MODULES),
            substr_count($html, 'Soon'),
            'Expected exactly one [Soon] badge per roadmap module in the sidebar.',
        );
    }

    public function test_roadmap_module_page_shows_roadmap_message(): void
    {
        $user = $this->createUserWithRole();

        $this->actingAs($user)
            ->get('/admin/enrollments')
            ->assertOk()
            ->assertSee('This module is in the roadmap.')
            ->assertSee('Planned for Phase 7 of 9');
    }

    public function test_mvp_module_page_shows_under_construction_message(): void
    {
        $user = $this->createUserWithRole();

        $this->actingAs($user)
            ->get('/admin/users')
            ->assertOk()
            ->assertSee('This module is under construction.')
            ->assertSee('Ships in Phase 5 of 9');
    }

    public function test_login_page_is_available_for_guests(): void
    {
        $this->get('/admin/login')->assertOk();
    }

    public function test_guests_are_redirected_from_the_panel_to_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }
}
