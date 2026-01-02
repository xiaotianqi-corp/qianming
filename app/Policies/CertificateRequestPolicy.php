<?php

namespace App\Policies;

use App\Models\CertificateRequest;
use App\Models\User;

class CertificateRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, CertificateRequest $certificate): bool
    {
        if ($user->hasRole('admin') || $user->hasRole('support')) {
            return true;
        }

        return $certificate->orderItem->order->customer_id === $user->id;
    }

    public function download(User $user, CertificateRequest $certificate): bool
    {
        if (!$this->view($user, $certificate)) {
            return false;
        }

        return in_array($certificate->status, ['issued', 'active']);
    }

    public function renew(User $user, CertificateRequest $certificate): bool
    {
        if (!$this->view($user, $certificate)) {
            return false;
        }

        return in_array($certificate->status, ['issued', 'active', 'expiring_soon']);
    }

    public function revoke(User $user, CertificateRequest $certificate): bool
    {
        if (!$this->view($user, $certificate)) {
            return false;
        }

        return $certificate->status !== 'revoked';
    }
}