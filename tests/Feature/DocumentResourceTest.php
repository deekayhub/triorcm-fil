<?php

namespace Tests\Feature;

use App\Filament\Resources\Documents\Pages\EditDocument;
use App\Filament\Resources\Documents\Pages\ListDocuments;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class DocumentResourceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Create a panel user that passes the role-based resource policies.
     */
    private function createUserWithRole(string $role = 'Super Admin'): User
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate($role, 'web'));

        return $user;
    }

    public function test_document_list_page_renders(): void
    {
        $user = $this->createUserWithRole();
        $document = Document::factory()->create();

        $this->actingAs($user)->get('/admin/documents')->assertOk();

        Livewire::test(ListDocuments::class)
            ->assertCanSeeTableRecords([$document]);
    }

    public function test_document_edit_page_renders_the_form(): void
    {
        $user = $this->createUserWithRole();
        $document = Document::factory()->create(['notes' => 'Renewal packet sent.']);

        $this->actingAs($user)
            ->get('/admin/documents/'.$document->getKey().'/edit')
            ->assertOk()
            ->assertSee('Notes');

        Livewire::test(EditDocument::class, ['record' => $document->getKey()])
            ->assertFormSet(['notes' => 'Renewal packet sent.']);
    }
}
