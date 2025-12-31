<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    
    public function index(Request $request): JsonResponse
    {
        $products = Product::query()
            ->when($request->has('active'), function ($q) {
                return $q->active();
            })
            ->get();

        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'code'            => 'required|string|unique:signature_products,code',
            'container'       => 'required|string',
            'validity_years'  => 'required|integer|min:1',
            'subscriber_type' => 'required|string',
            'pvp'             => 'required|numeric|min:0',
            'active'          => 'boolean'
        ]);

        $product = Product::create($validated);

        return response()->json([
            'message' => 'Product successfully created.',
            'data'    => $product
        ], 201);
    }

    public function show(Product $product): JsonResponse
    {
        return response()->json($product->load('priceRanges.providerCountryConfig.country'));
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'code'            => 'sometimes|string|unique:signature_products,code,' . $product->id,
            'container'       => 'sometimes|string',
            'validity_years'  => 'sometimes|integer|min:1',
            'subscriber_type' => 'sometimes|string',
            'pvp'             => 'sometimes|numeric|min:0',
            'active'          => 'boolean'
        ]);

        $product->update($validated);

        return response()->json([
            'message' => 'Product successfully updated.',
            'data'    => $product
        ]);
    }
    
    public function destroy(Product $product): JsonResponse
    {
        if ($product->priceRanges()->exists()) {
            return response()->json([
                'error' => 'You cannot delete a product that has price ranges configured. Deactivate them instead.'
            ], 422);
        }

        $product->delete();

        return response()->json(['message' => 'Product successfully removed.']);
    }

    public function signatures(): JsonResponse
    {
        $products = Product::active()->get();
        return response()->json($products);
    }
}