<?php

namespace App\Enums;

enum IdentityStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case VERIFIED = 'verified';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'Draft',
            self::SUBMITTED => 'Pending Verification',
            self::VERIFIED => 'Verified',
            self::REJECTED => 'Refused',
        };
    }
    
    public function color(): string
    {
        return match($this) {
            self::DRAFT => 'gray',
            self::SUBMITTED => 'blue',
            self::VERIFIED => 'green',
            self::REJECTED => 'red',
        };
    }
}