<?php

namespace App\Policies;

use App\Models\Practice;
use App\Models\User;

/**
 * Role-based access for Practices.
 *
 * Phase 5 replaces the role-name checks below with permission strings
 * (spatie/laravel-permission) once the permissions are seeded.
 */
class PracticePolicy
{
    public function viewAny(User $user): bool
    {
        return $this->grants($user, ['Super Admin', 'Admin', 'Credentialing Manager', 'Specialist', 'Read-only']);
    }

    public function view(User $user, Practice $practice): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->grants($user, ['Super Admin', 'Admin', 'Credentialing Manager', 'Specialist']);
    }

    public function update(User $user, Practice $practice): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Practice $practice): bool
    {
        return $this->grants($user, ['Super Admin', 'Admin', 'Credentialing Manager']);
    }

    public function deleteAny(User $user): bool
    {
        return $this->grants($user, ['Super Admin', 'Admin', 'Credentialing Manager']);
    }

    /**
     * @param  list<string>  $allowedRoles
     */
    private function grants(User $user, array $allowedRoles): bool
    {
        return $user->hasAnyRole($allowedRoles);
    }
}
