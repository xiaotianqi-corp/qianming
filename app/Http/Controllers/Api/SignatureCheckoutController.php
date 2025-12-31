<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Enums\OrderStatus;
use Illuminate\Http\Request;

class SignatureCheckoutController extends Controller
{
    public function store(CreateOrderRequest $request, PaymentManager $paymentManager)
    {
        return DB::transaction(function () use ($request) {

            $order = Order::create([
                'user_id' => auth()->id(),
                'status' => OrderStatus::PAYMENT_PENDING,
                'total' => 0,
                'currency' => 'USD'
            ]);

            $total = 0;

            foreach ($request->items as $item) {
                $product = SignatureProduct::findOrFail($item['product_id']);

                $order->items()->create([
                    'signature_product_id' => $product->id,
                    'unit_price' => $product->pvp,
                    'quantity' => $item['quantity'],
                ]);

                $total += $product->pvp * $item['quantity'];
            }

            $order->update(['total' => $total]);
            $paymentRedirect = $paymentManager->driver()->createPayment($order);

            return response()->json([
                'order_id' => $order->id,
                'payment_url' => $paymentRedirect->url
            ]);
        });
    }
}