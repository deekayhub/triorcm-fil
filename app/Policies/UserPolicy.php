<?php

namespace App\Policies;

use App\Models\User;

/**
 * Role-based access for the Users & Roles module (requirement.md §12.1).
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->canView($user);
    }

    public function view(User $user, User $model): bool
    {
        return $this->canView($user);
    }

    public function create(User $user): bool
    {
        return $this->canManage($user);
    }

    public function update(User $user, User $model): bool
    {
        return $this->canManage($user);
    }

    public function delete(User $user, User $model): bool
    {
        return $this->canManage($user) && $user->isNot($model);
    }

    public function deleteAny(User $user): bool
    {
        return $this->canManage($user);
    }

    public function restore(User $user, User $model): bool
    {
        return false;
    }

    public function restoreAny(User $user): bool
    {
        return false;
    }

    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }

    public function forceDeleteAny(User $user): bool
    {
        return false;
    }

    /**
     * Every panel role may browse the user directory.
     */
    private function canView(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Admin', 'Credentialing Manager', 'Specialist', 'Read-only']);
    }

    /**
     * Only administrators may create, edit or remove accounts.
     */
    private function canManage(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Admin']);
    }
}
