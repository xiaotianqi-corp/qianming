<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\CertificateRequest;
use App\Models\AuditEvent;
use App\Enums\TicketStatus;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class TicketController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('support/tickets/index', [
            'tickets' => SupportTicket::with('user')
                ->where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->get(),
        ]);
    }

    public function create(): Response
    {
        $certificateRequests = CertificateRequest::whereHas('orderItem.order', function ($q) {
            $q->where('customer_id', auth()->id());
        })->with('orderItem.product')->get();

        return Inertia::render('support/tickets/create', [
            'certificateRequests' => $certificateRequests,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        if ($request->certificate_request_id === 'none' || $request->certificate_request_id === '') {
            $request->merge(['certificate_request_id' => null]);
        }
        
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'category' => 'required|string',
            'priority' => 'required|string',
            'description' => 'required|string|min:10',
            'certificate_request_id' => 'nullable|exists:certificate_requests,id',
            'source' => 'sometimes|string',
            'urgency' => 'sometimes|string',
            'impact' => 'sometimes|string',
            'group' => 'sometimes|string',
        ]);

        $validated['priority'] = strtolower($validated['priority']);
        $validated['category'] = strtolower($validated['category']);

        $ticket = SupportTicket::create(array_merge($validated, [
            'user_id' => auth()->id(),
            'status' => TicketStatus::OPEN,
        ]));
        
        AuditEvent::log('ticket_created', [
            'ticket_id' => $ticket->id,
            'ticket_number' => $ticket->ticket_number,
            'subject' => $ticket->subject,
        ]);

        return redirect()->route('support.tickets.index')
            ->with('success', "Ticket {$ticket->ticket_number} created successfully.");
    }

    public function show(SupportTicket $supportTicket): Response
    {
        if ($supportTicket->user_id !== auth()->id() && !auth()->user()->hasAnyRole(['admin', 'support'])) {
            abort(403);
        }

        return Inertia::render('support/tickets/show', [
            'ticket' => $supportTicket->load(['user', 'certificateRequest']),
            'tickets' => SupportTicket::where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->get(),
        ]);
    }

    public function update(Request $request, SupportTicket $supportTicket): RedirectResponse
    {
        if (!auth()->user()->hasAnyRole(['admin', 'support'])) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|string|in:open,in_progress,waiting_provider,closed',
            'priority' => 'sometimes|string|in:low,medium,high',
            'provider_payload' => 'sometimes|array'
        ]);

        $supportTicket->update($validated);

        AuditEvent::log('ticket_updated_by_staff', [
            'ticket_id' => $supportTicket->id,
            'new_status' => $supportTicket->status
        ]);

        return back()->with('success', 'Ticket updated successfully');
    }
}