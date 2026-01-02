<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\AuditEvent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    public function index(): JsonResponse
    {
        $customers = Customer::where('user_id', auth()->id())->get();
        return response()->json($customers);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type'           => 'required|in:natural,juridical',
            'identification' => 'required|string|unique:customers,identification',
            'name'           => 'required|string|max:255',
            'email'          => 'required|email',
            'phone'          => 'nullable|string',
            'address'        => 'nullable|string'
        ]);

        $customer = Customer::create(array_merge($validated, [
            'user_id' => auth()->id()
        ]));

        AuditEvent::log('customer_created', [
            'customer_id' => $customer->id,
            'identification' => $customer->identification
        ]);

        return response()->json([
            'message' => 'Cliente registrado exitosamente',
            'data' => $customer
        ], 201);
    }

    public function show(Customer $customer): JsonResponse
    {
        $this->authorizeOwner($customer);
        return response()->json($customer);
    }

    public function update(Request $request, Customer $customer): JsonResponse
    {
        $this->authorizeOwner($customer);

        $validated = $request->validate([
            'type'           => 'sometimes|in:natural,juridical',
            'identification' => 'sometimes|string|unique:customers,identification,' . $customer->id,
            'name'           => 'sometimes|string|max:255',
            'email'          => 'sometimes|email',
            'phone'          => 'nullable|string',
            'address'        => 'nullable|string'
        ]);

        $customer->update($validated);

        AuditEvent::log('customer_updated', ['customer_id' => $customer->id]);

        return response()->json([
            'message' => 'Datos actualizados',
            'data' => $customer
        ]);
    }

    public function destroy(Customer $customer): JsonResponse
    {
        $this->authorizeOwner($customer);
        
        AuditEvent::log('customer_deleted', ['customer_id' => $customer->id]);
        $customer->delete();

        return response()->json(['message' => 'Cliente eliminado']);
    }
    
    private function authorizeOwner(Customer $customer): void
    {
        if ($customer->user_id !== auth()->id()) {
            abort(403, 'No tienes permiso para acceder a este recurso.');
        }
    }
}