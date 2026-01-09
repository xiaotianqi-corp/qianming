<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use Illuminate\Http\JsonResponse;

class TicketActivityController extends Controller
{
    // Obtener actividades del ticket
    public function index(SupportTicket $ticket): JsonResponse
    {
        $activities = $ticket->activities()
            ->with('actor')
            ->orderBy('created_at', 'desc')
            ->paginate(50);

        return response()->json($activities);
    }
}