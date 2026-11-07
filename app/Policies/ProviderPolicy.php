<?php

namespace App\Policies;

use App\Models\Provider;
use App\Models\User;

/**
 * Role-based access for Providers.
 *
 * Phase 5 replaces the role-name checks below with permission strings
 * (spatie/laravel-permission) once the permissions are seeded.
 */
class ProviderPolicy
{
    public function viewAny(User $user): bool
    {
        return $this->grants($user, ['Super Admin', 'Admin', 'Credentialing Manager', 'Specialist', 'Read-only']);
    }

    public function view(User $user, Provider $provider): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $this->grants($user, ['Super Admin', 'Admin', 'Credentialing Manager', 'Specialist']);
    }

    public function update(User $user, Provider $provider): bool
    {
        return $this->create($user);
    }

    public function delete(User $user, Provider $provider): bool
    {
        return $this->grants($user, ['Super Admin', 'Admin', 'Credentialing Manager']);
    }

    public function deleteAny(User $user): bool
    {
        return $this->grants($user, ['Super Admin', 'Admin', 'Credentialing Manager']);
    }

    public function restore(User $user, Provider $provider): bool
    {
        return $this->delete($user, $provider);
    }

    public function restoreAny(User $user): bool
    {
        return $this->deleteAny($user);
    }

    /**
     * @param  list<string>  $allowedRoles
     */
    private function grants(User $user, array $allowedRoles): bool
    {
        return $user->hasAnyRole($allowedRoles);
    }
}
