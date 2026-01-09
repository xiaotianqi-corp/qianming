<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\TicketApproval;
use App\Models\AuditEvent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TicketApprovalController extends Controller
{
    // Solicitar aprobación
    public function store(Request $request, SupportTicket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'approver_ids' => 'required|array|min:1',
            'approver_ids.*' => 'exists:users,id',
        ]);

        $approvals = [];
        foreach ($validated['approver_ids'] as $approverId) {
            $approvals[] = $ticket->approvals()->create([
                'approver_id' => $approverId,
                'approval_status' => 'pending',
            ]);
        }

        AuditEvent::log('approval_requested', [
            'ticket_id' => $ticket->id,
            'approvers' => $validated['approver_ids'],
        ]);

        return response()->json([
            'message' => 'Approval requested',
            'approvals' => $approvals
        ], 201);
    }

    // Listar aprobaciones
    public function index(SupportTicket $ticket): JsonResponse
    {
        $approvals = $ticket->approvals()
            ->with('approver')
            ->orderBy('level')
            ->get();

        return response()->json(['approvals' => $approvals]);
    }

    // Ver aprobación específica
    public function show(SupportTicket $ticket, TicketApproval $approval): JsonResponse
    {
        if ($approval->ticket_id !== $ticket->id) {
            return response()->json(['error' => 'Approval not found'], 404);
        }

        return response()->json(['approval' => $approval->load('approver')]);
    }

    // Aprobar/Rechazar
    public function update(Request $request, SupportTicket $ticket, TicketApproval $approval): JsonResponse
    {
        if ($approval->ticket_id !== $ticket->id) {
            return response()->json(['error' => 'Approval not found'], 404);
        }

        // Verificar que el usuario es el aprobador
        if ($approval->approver_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'approval_status' => 'required|in:approved,rejected',
            'approval_notes' => 'nullable|string',
        ]);

        $approval->update([
            'approval_status' => $validated['approval_status'],
            'approval_notes' => $validated['approval_notes'] ?? null,
            'responded_at' => now(),
        ]);

        AuditEvent::log('approval_responded', [
            'ticket_id' => $ticket->id,
            'approval_id' => $approval->id,
            'status' => $validated['approval_status'],
        ]);

        return response()->json([
            'message' => 'Approval updated',
            'approval' => $approval
        ]);
    }

    // Enviar recordatorio
    public function sendReminder(SupportTicket $ticket, TicketApproval $approval): JsonResponse
    {
        if ($approval->ticket_id !== $ticket->id) {
            return response()->json(['error' => 'Approval not found'], 404);
        }

        // Aquí enviarías el email/notificación
        // Notification::send($approval->approver, new ApprovalReminderNotification($approval));

        AuditEvent::log('approval_reminder_sent', [
            'ticket_id' => $ticket->id,
            'approval_id' => $approval->id,
        ]);

        return response()->json(['message' => 'Reminder sent']);
    }

    // Cancelar aprobación
    public function cancel(SupportTicket $ticket, TicketApproval $approval): JsonResponse
    {
        if ($approval->ticket_id !== $ticket->id) {
            return response()->json(['error' => 'Approval not found'], 404);
        }

        $approval->delete();

        AuditEvent::log('approval_cancelled', [
            'ticket_id' => $ticket->id,
            'approval_id' => $approval->id,
        ]);

        return response()->json(['message' => 'Approval cancelled']);
    }
}