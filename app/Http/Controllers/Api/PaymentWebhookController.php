<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PaymentWebhookController extends Controller
{
    public function handle(Request $request, PaymentGateway $gateway)
    {
        $result = $gateway->verify($request->all());

        if (! $result->successful()) {
            return response()->noContent();
        }

        $order = Order::findOrFail($result->orderId());

        $order->update([
            'status' => OrderStatus::PAID
        ]);

        dispatch(new ActivateSignatureProductJob($order));
    }
}