<?php

namespace App\Integrations\Uanataca\DTO;

class PaymentRedirect {
    public function __construct(public string $url) {}
}