<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Enums\{TicketStatus, TicketCategory};
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    
    public function index(): Response
    {
        if (!auth()->user()->hasAnyRole(['admin', 'support'])) {
            abort(403);
        }

        $metrics = [
            'open' => SupportTicket::open()->count(),
            'my_assigned' => SupportTicket::assignedTo(auth()->id())->open()->count(),
            'unassigned' => SupportTicket::unassigned()->count(),
            'overdue' => SupportTicket::overdue()->count(),
            'resolved_today' => SupportTicket::closed()
                ->whereDate('resolved_at', today())
                ->count(),
            'by_category' => SupportTicket::select('category', DB::raw('count(*) as total'))
                ->groupBy('category')
                ->get()
                ->map(fn($item) => [
                    'category' => TicketCategory::from($item->category)->label(),
                    'value' => TicketCategory::from($item->category)->value,
                    'total' => $item->total,
                    'color' => TicketCategory::from($item->category)->color(),
                ]),
            'by_status' => SupportTicket::select('status', DB::raw('count(*) as total'))
                ->groupBy('status')
                ->get()
                ->map(fn($item) => [
                    'status' => TicketStatus::from($item->status)->label(),
                    'value' => TicketStatus::from($item->status)->value,
                    'total' => $item->total,
                    'color' => TicketStatus::from($item->status)->color(),
                ]),
            'avg_response_time_minutes' => (int) SupportTicket::whereNotNull('first_responded_at')
                ->selectRaw('AVG(EXTRACT(EPOCH FROM (first_responded_at - created_at))/60) as avg_minutes')
                ->value('avg_minutes'),
        ];

        $recentTickets = SupportTicket::with(['user:id,name', 'assignedTo:id,name'])
            ->latest()
            ->take(10)
            ->get();

        $unassignedTickets = SupportTicket::with(['user:id,name'])
            ->unassigned()
            ->open()
            ->latest()
            ->take(5)
            ->get();

        $myTickets = SupportTicket::with(['user:id,name'])
            ->assignedTo(auth()->id())
            ->open()
            ->latest()
            ->take(5)
            ->get();

        return Inertia::render('dashboard', [
            'metrics' => $metrics,
            'recentTickets' => $recentTickets,
            'unassignedTickets' => $unassignedTickets,
            'myTickets' => $myTickets,
        ]);
    }
}
