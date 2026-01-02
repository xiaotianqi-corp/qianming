<?php

namespace App\Policies;

use App\Models\SupportTicket;
use App\Models\User;

class SupportTicketPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, SupportTicket $ticket): bool
    {
        if ($user->hasAnyRole(['admin', 'support'])) {
            return true;
        }

        return $ticket->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, SupportTicket $ticket): bool
    {
        return $user->hasAnyRole(['admin', 'support']);
    }

    public function respond(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'support']);
    }

    public function close(User $user, SupportTicket $ticket): bool
    {
        if ($user->hasAnyRole(['admin', 'support'])) {
            return true;
        }

        return $ticket->user_id === $user->id;
    }
}