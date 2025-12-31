<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Country;
use App\Http\Requests\CreateOrderRequest;
use App\Services\CountryAvailabilityService;
use App\Exceptions\ProductNotAvailableInCountryException;
use App\Events\AuditEvent;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    
    public function index(): JsonResponse
    {
        $orders = Order::with(['items.product'])
            ->where('customer_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($orders);
    }

    public function store(
        CreateOrderRequest $request, 
        CountryAvailabilityService $availability
    ): JsonResponse {
        
        $country = Country::findOrFail($request->country_id);

        try {
            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $availability->validate($product, $country);
            }

            $order = DB::transaction(function () use ($request) {
                $newOrder = Order::create([
                    'customer_id' => auth()->id() ?? 1,
                    'status' => \App\Enums\OrderStatus::PENDING,
                    'total' => 0,
                    'payment_status' => 'pending'
                ]);

                $calculatedTotal = 0;

                foreach ($request->items as $item) {
                    $product = Product::find($item['product_id']);
                    $subtotal = $product->pvp * $item['quantity'];

                    $newOrder->items()->create([
                        'signature_product_id' => $product->id,
                        'unit_price' => $product->pvp,
                        'quantity' => $item['quantity']
                    ]);

                    $calculatedTotal += $subtotal;
                }

                $newOrder->update(['total' => $calculatedTotal]);
                return $newOrder;
            });

            event(new AuditEvent(
                entity: 'order',
                action: 'created',
                entityId: $order->id,
                data: [
                    'total' => $order->total,
                    'country' => $country->name,
                    'items_count' => count($request->items)
                ]
            ));

            return response()->json([
                'message' => 'Orden procesada correctamente',
                'data' => $order->load('items.product')
            ], 201);

        } catch (ProductNotAvailableInCountryException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error interno: ' . $e->getMessage()], 500);
        }
    }
    
    public function show(Order $order): JsonResponse
    {
        if ($order->customer_id !== auth()->id() && !auth()->user()->isAdmin()) {
            return response()->json(['error' => 'No autorizado'], 403);
        }

        return response()->json($order->load(['items.product']));
    }

    public function update(Request $request, Order $order): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'sometimes|string',
            'payment_status' => 'sometimes|string'
        ]);

        $oldStatus = $order->status;
        $order->update($validated);

        if ($order->wasChanged('status')) {
            event(new AuditEvent(
                entity: 'order',
                action: 'status_updated',
                entityId: $order->id,
                data: ['from' => $oldStatus, 'to' => $order->status]
            ));
        }

        return response()->json([
            'message' => 'Orden actualizada',
            'data' => $order
        ]);
    }

    public function destroy(Order $order): JsonResponse
    {
        if ($order->status !== \App\Enums\OrderStatus::PENDING) {
            return response()->json(['error' => 'No se puede eliminar una orden procesada'], 422);
        }

        $order->delete();

        return response()->json(['message' => 'Orden eliminada correctamente']);
    }
}