<?php

namespace App\Policies;

use App\Models\Identity;
use App\Models\User;
use App\Enums\IdentityStatus;

class IdentityPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Identity $identity): bool
    {
        if ($user->hasAnyRole(['admin', 'compliance'])) {
            return true;
        }

        return $identity->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Identity $identity): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $identity->user_id === $user->id
            && in_array($identity->status, [IdentityStatus::DRAFT, IdentityStatus::REJECTED]);
    }

    public function delete(User $user, Identity $identity): bool
    {
        if ($user->hasRole('admin')) {
            return true;
        }

        return $identity->user_id === $user->id
            && $identity->status !== IdentityStatus::VERIFIED;
    }

    public function viewDocuments(User $user, Identity $identity): bool
    {
        if ($user->hasAnyRole(['admin', 'compliance'])) {
            return true;
        }

        return $identity->user_id === $user->id;
    }

    public function verify(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'compliance']);
    }
}