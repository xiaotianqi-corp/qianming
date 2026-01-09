<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\TicketTask;
use App\Models\AuditEvent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TicketTaskController extends Controller
{
    // Crear task
    public function store(Request $request, SupportTicket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'agent_id' => 'nullable|exists:users,id',
            'group_id' => 'nullable|exists:groups,id',
            'status' => 'sometimes|in:open,in_progress,completed',
            'due_date' => 'nullable|date',
            'notify_before' => 'sometimes|boolean',
        ]);

        $task = $ticket->tasks()->create($validated);

        AuditEvent::log('task_created', [
            'ticket_id' => $ticket->id,
            'task_id' => $task->id,
        ]);

        return response()->json([
            'message' => 'Task created',
            'task' => $task->load(['agent', 'group'])
        ], 201);
    }

    // Listar tasks
    public function index(SupportTicket $ticket): JsonResponse
    {
        $tasks = $ticket->tasks()
            ->with(['agent'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json(['tasks' => $tasks]);
    }

    // Ver task
    public function show(SupportTicket $ticket, TicketTask $task): JsonResponse
    {
        if ($task->ticket_id !== $ticket->id) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        return response()->json(['task' => $task->load(['agent', 'timeEntries'])]);
    }

    // Actualizar task
    public function update(Request $request, SupportTicket $ticket, TicketTask $task): JsonResponse
    {
        if ($task->ticket_id !== $ticket->id) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'agent_id' => 'nullable|exists:users,id',
            'status' => 'sometimes|in:open,in_progress,completed',
            'due_date' => 'nullable|date',
        ]);

        $task->update($validated);

        AuditEvent::log('task_updated', [
            'ticket_id' => $ticket->id,
            'task_id' => $task->id,
        ]);

        return response()->json([
            'message' => 'Task updated',
            'task' => $task
        ]);
    }

    // Eliminar task
    public function destroy(SupportTicket $ticket, TicketTask $task): JsonResponse
    {
        if ($task->ticket_id !== $ticket->id) {
            return response()->json(['error' => 'Task not found'], 404);
        }

        $task->delete();

        AuditEvent::log('task_deleted', [
            'ticket_id' => $ticket->id,
            'task_id' => $task->id,
        ]);

        return response()->json(['message' => 'Task deleted']);
    }
}