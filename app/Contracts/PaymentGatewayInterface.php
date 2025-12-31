<?php

namespace App\Contracts;

use App\Models\Order;
use App\DTOs\PaymentRedirect; 
use App\DTOs\PaymentResult;

interface PaymentGateway
{
    public function createPayment(Order $order): PaymentRedirect;
    
    public function verify(array $payload): PaymentResult;
}