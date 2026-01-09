<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\TicketTimeEntry;
use App\Models\AuditEvent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TicketTimeEntryController extends Controller
{
    // Crear time entry
    public function store(Request $request, SupportTicket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'time_spent' => 'required|integer|min:1',
            'billable' => 'sometimes|boolean',
            'executed_at' => 'required|date',
            'note' => 'nullable|string',
            'task_id' => 'nullable|exists:ticket_tasks,id',
        ]);

        $timeEntry = $ticket->timeEntries()->create([
            'agent_id' => auth()->id(),
            'time_spent' => $validated['time_spent'],
            'billable' => $validated['billable'] ?? true,
            'executed_at' => $validated['executed_at'],
            'note' => $validated['note'] ?? null,
            'task_id' => $validated['task_id'] ?? null,
        ]);

        AuditEvent::log('time_entry_created', [
            'ticket_id' => $ticket->id,
            'time_entry_id' => $timeEntry->id,
            'time_spent' => $timeEntry->time_spent,
        ]);

        return response()->json([
            'message' => 'Time entry created',
            'time_entry' => $timeEntry->load('agent')
        ], 201);
    }

    // Listar time entries
    public function index(SupportTicket $ticket): JsonResponse
    {
        $timeEntries = $ticket->timeEntries()
            ->with('agent')
            ->orderBy('executed_at', 'desc')
            ->get();

        $totalTime = $timeEntries->sum('time_spent');
        $billableTime = $timeEntries->where('billable', true)->sum('time_spent');

        return response()->json([
            'time_entries' => $timeEntries,
            'summary' => [
                'total_time_spent' => $totalTime,
                'billable_time' => $billableTime,
                'non_billable_time' => $totalTime - $billableTime,
            ]
        ]);
    }

    // Ver time entry
    public function show(SupportTicket $ticket, TicketTimeEntry $timeEntry): JsonResponse
    {
        if ($timeEntry->ticket_id !== $ticket->id) {
            return response()->json(['error' => 'Time entry not found'], 404);
        }

        return response()->json(['time_entry' => $timeEntry->load('agent')]);
    }

    // Actualizar time entry
    public function update(Request $request, SupportTicket $ticket, TicketTimeEntry $timeEntry): JsonResponse
    {
        if ($timeEntry->ticket_id !== $ticket->id) {
            return response()->json(['error' => 'Time entry not found'], 404);
        }

        $validated = $request->validate([
            'time_spent' => 'sometimes|integer|min:1',
            'billable' => 'sometimes|boolean',
            'executed_at' => 'sometimes|date',
            'note' => 'nullable|string',
        ]);

        $timeEntry->update($validated);

        AuditEvent::log('time_entry_updated', [
            'ticket_id' => $ticket->id,
            'time_entry_id' => $timeEntry->id,
        ]);

        return response()->json([
            'message' => 'Time entry updated',
            'time_entry' => $timeEntry
        ]);
    }

    // Eliminar time entry
    public function destroy(SupportTicket $ticket, TicketTimeEntry $timeEntry): JsonResponse
    {
        if ($timeEntry->ticket_id !== $ticket->id) {
            return response()->json(['error' => 'Time entry not found'], 404);
        }

        $timeEntry->delete();

        AuditEvent::log('time_entry_deleted', [
            'ticket_id' => $ticket->id,
            'time_entry_id' => $timeEntry->id,
        ]);

        return response()->json(['message' => 'Time entry deleted']);
    }
}