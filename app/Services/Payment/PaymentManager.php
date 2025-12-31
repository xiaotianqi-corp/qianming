<?php

namespace App\Services\Payment;

use App\Contracts\PaymentGateway;
use App\Integrations\Payments\StripeGateway;
use App\Integrations\Payments\PayPhoneGateway;
use Exception;

class PaymentManager {
    public function driver(string $driver = null): PaymentGateway {
        $driver = $driver ?: config('services.payments.default');

        return match ($driver) {
            'stripe' => new StripeGateway(),
            'payphone' => new PayPhoneGateway(),
            default => throw new \Exception("Payment driver [$name] not supported.")
        };
    }
}