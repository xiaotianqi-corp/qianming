<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\TicketWatcher;
use App\Models\AuditEvent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TicketWatcherController extends Controller
{
    // Agregar watcher
    public function store(Request $request, SupportTicket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $watcher = $ticket->watchers()->firstOrCreate([
            'user_id' => $validated['user_id'],
        ]);

        AuditEvent::log('watcher_added', [
            'ticket_id' => $ticket->id,
            'user_id' => $validated['user_id'],
        ]);

        return response()->json([
            'message' => 'Watcher added',
            'watcher' => $watcher->load('user')
        ], 201);
    }

    // Listar watchers
    public function index(SupportTicket $ticket): JsonResponse
    {
        $watchers = $ticket->watchers()->with('user')->get();
        return response()->json(['watchers' => $watchers]);
    }

    // Eliminar watcher
    public function destroy(SupportTicket $ticket, TicketWatcher $watcher): JsonResponse
    {
        if ($watcher->ticket_id !== $ticket->id) {
            return response()->json(['error' => 'Watcher not found'], 404);
        }

        $watcher->delete();

        AuditEvent::log('watcher_removed', [
            'ticket_id' => $ticket->id,
            'user_id' => $watcher->user_id,
        ]);

        return response()->json(['message' => 'Watcher removed']);
    }
}