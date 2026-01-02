<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\AuditEvent;
use App\Enums\TicketStatus;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SupportTicketController extends Controller
{
    
    public function index(): JsonResponse
    {
        $tickets = SupportTicket::where('user_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();
            
        return response()->json($tickets);
    }
    
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'category' => 'required|in:identity,payment,issuance,technical',
            'priority' => 'required|in:low,medium,high',
            'description' => 'required|string|min:10',
            'certificate_request_id' => 'nullable|exists:certificate_requests,id',
        ]);

        $ticket = SupportTicket::create(array_merge($validated, [
            'user_id' => auth()->id(),
            'status' => TicketStatus::OPEN,
        ]));
        
        AuditEvent::log('ticket_created', [
            'ticket_id' => $ticket->id,
            'category' => $ticket->category
        ]);

        return response()->json([
            'message' => 'Support ticket created successfully.',
            'data' => $ticket
        ], 201);
    }
    
    public function show(SupportTicket $supportTicket): JsonResponse
    {
        if ($supportTicket->user_id !== auth()->id() && !auth()->user()->hasRole('admin', 'support')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        if (!auth()->user()->hasRole('admin', 'support')) {
            $supportTicket->makeHidden('provider_payload');
        }

        return response()->json($supportTicket);
    }
    
    public function update(Request $request, SupportTicket $supportTicket): JsonResponse
    {
        if (!auth()->user()->hasRole('admin', 'support')) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:open,pending,resolved,closed',
            'priority' => 'sometimes|string|in:low,medium,high',
            'provider_payload' => 'sometimes|array'
        ]);

        $supportTicket->update($validated);

        AuditEvent::log('ticket_updated_by_staff', [
            'ticket_id' => $supportTicket->id,
            'new_status' => $supportTicket->status
        ]);

        return response()->json([
            'message' => 'Ticket updated successfully',
            'data' => $supportTicket
        ]);
    }
}