<?php

namespace App\Enums;

enum TicketSource: string
{
    case PORTAL = 'portal';
    case EMAIL = 'email';
    case PHONE = 'phone';
    case CHAT = 'chat';
    case API = 'api';
    case SYSTEM = 'system';

    public function label(): string
    {
        return match($this) {
            self::PORTAL => 'Portal Web',
            self::EMAIL => 'Correo Electrónico',
            self::PHONE => 'Teléfono',
            self::CHAT => 'Chat en Vivo',
            self::API => 'API',
            self::SYSTEM => 'Sistema',
        };
    }

    public function icon(): string
    {
        return match($this) {
            self::PORTAL => 'globe',
            self::EMAIL => 'mail',
            self::PHONE => 'phone',
            self::CHAT => 'message-circle',
            self::API => 'code',
            self::SYSTEM => 'cpu',
        };
    }
}