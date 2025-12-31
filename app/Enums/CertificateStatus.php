<?php

namespace App\Enums;

enum CertificateStatus: string
{
    case PENDING = 'pending';
    case SUBMITTED = 'submitted';
    case ISSUED = 'issued';
    case ACTIVE = 'active';
    case EXPIRING_SOON = 'expiring_soon';
    case EXPIRED = 'expired';
    case REVOKED = 'revoked';
    case REJECTED = 'rejected';
}
