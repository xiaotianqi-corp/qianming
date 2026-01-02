<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\CertificateRequest;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    /**
     * Lista de tickets del usuario.
     */
    public function index(): Response
    {
        return Inertia::render('support/tickets/index', [
            'tickets' => SupportTicket::where('user_id', auth()->id())
                ->orderBy('created_at', 'desc')
                ->get(),
        ]);
    }

    /**
     * Formulario para crear un nuevo ticket.
     */
    public function create(): Response
    {
        $certificateRequests = CertificateRequest::whereHas('orderItem.order', function ($q) {
            $q->where('order_id', auth()->id());
        })->with('orderItem.product')->get();

        return Inertia::render('support/tickets/create', [
            'certificateRequests' => $certificateRequests,
            'categories' => ['identity', 'payment', 'issuance', 'technical'],
            'priorities' => ['low', 'medium', 'high']
        ]);
    }

    /**
     * Detalle del ticket.
     */
    public function show(SupportTicket $supportTicket): Response
    {
        if ($supportTicket->user_id !== auth()->id() && !auth()->user()->hasAnyRole(['admin', 'support'])) {
            abort(403);
        }

        return Inertia::render('support/tickets/show', [
            'ticket' => $supportTicket->load(['user', 'certificateRequest']),
        ]);
    }
}