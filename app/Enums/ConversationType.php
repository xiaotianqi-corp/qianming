<?php

namespace App\Enums;

enum ConversationType: string
{
    case REPLY = 'reply';
    case NOTE = 'note';

    public function label(): string
    {
        return match($this) {
            self::REPLY => 'Respuesta',
            self::NOTE => 'Nota Interna',
        };
    }

    public function isPrivate(): bool
    {
        return $this === self::NOTE;
    }
}