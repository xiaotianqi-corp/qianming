<?php

namespace App\Enums;

enum TicketStatus: string
{
    case OPEN = 'open';
    case IN_PROGRESS = 'in_progress';
    case WAITING_CUSTOMER = 'waiting_customer';
    case WAITING_PROVIDER = 'waiting_provider';
    case CLOSED = 'closed';

    public function label(): string
    {
        return match($this) {
            self::OPEN => 'Abierto',
            self::IN_PROGRESS => 'En Progreso',
            self::WAITING_CUSTOMER => 'Esperando Cliente',
            self::WAITING_PROVIDER => 'Esperando Proveedor',
            self::CLOSED => 'Cerrado',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::OPEN => 'blue',
            self::IN_PROGRESS => 'yellow',
            self::WAITING_CUSTOMER => 'orange',
            self::WAITING_PROVIDER => 'purple',
            self::CLOSED => 'green',
        };
    }

    public function isOpen(): bool
    {
        return $this !== self::CLOSED;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}