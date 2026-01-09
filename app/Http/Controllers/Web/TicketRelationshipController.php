<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\AuditEvent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TicketRelationshipController extends Controller
{
    // Crear ticket hijo
    public function createChild(Request $request, SupportTicket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|in:low,medium,high,urgent',
            'status' => 'sometimes|string',
        ]);

        $childTicket = SupportTicket::create(array_merge($validated, [
            'parent_id' => $ticket->id,
            'user_id' => auth()->id(),
            'status' => $validated['status'] ?? 'open',
        ]));

        AuditEvent::log('child_ticket_created', [
            'parent_ticket_id' => $ticket->id,
            'child_ticket_id' => $childTicket->id,
        ]);

        return response()->json([
            'message' => 'Child ticket created',
            'ticket' => $childTicket
        ], 201);
    }

    // Listar tickets relacionados
    public function relatedTickets(SupportTicket $ticket): JsonResponse
    {
        $related = [
            'parent' => $ticket->parent,
            'children' => $ticket->children,
        ];

        return response()->json(['related_tickets' => $related]);
    }

    // Mover ticket
    public function move(Request $request, SupportTicket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'workspace_id' => 'required|exists:workspaces,id',
        ]);

        $oldWorkspace = $ticket->workspace_id;
        $ticket->update(['workspace_id' => $validated['workspace_id']]);

        AuditEvent::log('ticket_moved', [
            'ticket_id' => $ticket->id,
            'from_workspace' => $oldWorkspace,
            'to_workspace' => $validated['workspace_id'],
        ]);

        return response()->json([
            'message' => 'Ticket moved successfully',
            'ticket' => $ticket
        ]);
    }
}