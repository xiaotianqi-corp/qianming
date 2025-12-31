<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditEvent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class AuditController extends Controller
{
    
    public function index(Request $request): JsonResponse
    {
        $query = AuditEvent::query();

        if ($request->has('event')) {
            $query->where('event', $request->event);
        }

        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->has('date_from') && $request->has('date_to')) {
            $query->whereBetween('created_at', [$request->date_from, $request->date_to]);
        }

        $events = $query->orderBy('created_at', 'desc')
                        ->paginate(50);

        return response()->json($events);
    }

    public function show(AuditEvent $auditEvent): JsonResponse
    {
        return response()->json($auditEvent);
    }
}