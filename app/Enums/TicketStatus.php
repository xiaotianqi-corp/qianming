<?php

namespace App\Enums;

enum TicketStatus: string
{
    case OPEN = 'open';
    case IN_PROGRESS = 'in_progress';
    case WAITING_PROVIDER = 'waiting_provider';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match($this) {
            self::OPEN => 'Open',
            self::IN_PROGRESS => 'In Progress',
            self::WAITING_PROVIDER => 'Waiting for the Supplier',
            self::CLOSED => 'Closed',
        };
    }
}