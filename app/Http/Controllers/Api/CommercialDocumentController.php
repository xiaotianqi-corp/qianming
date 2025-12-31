<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CommercialDocument;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CommercialDocumentController extends Controller
{
    
    public function index(Request $request): JsonResponse
    {
        $documents = CommercialDocument::whereHas('order', function ($q) {
            $q->where('user_id', auth()->id());
        })
        ->when($request->order_id, fn($q) => $q->where('order_id', $request->order_id))
        ->with(['country'])
        ->orderBy('created_at', 'desc')
        ->get();

        return response()->json($documents);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id'    => 'required|exists:orders,id',
            'type'        => 'required|in:invoice,credit_note,receipt',
            'external_id' => 'nullable|string',
            'pdf_url'     => 'nullable|url',
            'xml_url'     => 'nullable|url',
        ]);

        $order = Order::findOrFail($request->order_id);

        if ($order->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return DB::transaction(function () use ($validated, $order) {
            $document = CommercialDocument::create([
                'order_id'    => $order->id,
                'country_id'  => $order->country_id,
                'type'        => $validated['type'],
                'number'      => 'TEMP-' . now()->timestamp,
                'subtotal'    => $order->subtotal,
                'tax'         => $order->tax,
                'total'       => $order->total,
                'currency'    => $order->currency,
                'status'      => 'draft',
                'external_id' => $validated['external_id'] ?? null,
                'pdf_url'     => $validated['pdf_url'] ?? null,
                'xml_url'     => $validated['xml_url'] ?? null,
            ]);

            AuditEvent::log('commercial_document_created', [
                'document_id' => $document->id,
                'order_id'    => $order->id,
                'total'       => $document->total
            ]);

            return response()->json([
                'message' => 'Commercial document generated',
                'data'    => $document
            ], 201);
        });
    }
    
    public function show(CommercialDocument $commercialDocument): JsonResponse
    {
        if ($commercialDocument->order->user_id !== auth()->id()) {
            abort(403);
        }

        return response()->json($commercialDocument->load(['order', 'country']));
    }
    
    public function update(Request $request, CommercialDocument $commercialDocument): JsonResponse
    {
        if (!auth()->user()->hasRole('admin', 'system')) {
            abort(403);
        }

        $validated = $request->validate([
            'status'      => 'sometimes|string|in:draft,issued,cancelled,failed',
            'number'      => 'sometimes|string',
            'external_id' => 'sometimes|string',
            'pdf_url'     => 'sometimes|url',
            'xml_url'     => 'sometimes|url',
        ]);

        $commercialDocument->update($validated);

        if ($commercialDocument->status === 'issued') {
            AuditEvent::log('commercial_document_issued', [
                'document_id' => $commercialDocument->id,
                'number'      => $commercialDocument->number
            ]);
        }

        return response()->json([
            'message' => 'Updated document',
            'data'    => $commercialDocument
        ]);
    }
    
    public function destroy(CommercialDocument $commercialDocument): JsonResponse
    {
        return response()->json(['error' => 'Accounting documents cannot be deleted, only cancelled.'], 422);
    }
}