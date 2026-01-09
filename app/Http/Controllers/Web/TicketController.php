<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\{SupportTicket, CertificateRequest, User, AgentGroup};
use App\Enums\{TicketStatus, TicketPriority, TicketCategory};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{DB, Storage};
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class TicketController extends Controller
{
    /**
     * Lista de tickets del usuario o todos si es admin/soporte
     */
    public function index(Request $request): Response
    {
        $query = SupportTicket::query()
            ->with(['user:id,name,email', 'assignedTo:id,name', 'certificateRequest:id,status']);

        // Si no es admin/soporte, solo ver propios tickets
        if (!auth()->user()->hasAnyRole(['admin', 'support'])) {
            $query->where('user_id', auth()->id());
        }

        // Filtros
        if ($request->filled('status')) {
            $query->byStatus($request->status);
        }

        if ($request->filled('category')) {
            $query->byCategory($request->category);
        }

        if ($request->filled('priority')) {
            $query->byPriority($request->priority);
        }

        // Filtros solo para admin/soporte
        if (auth()->user()->hasAnyRole(['admin', 'support'])) {
            if ($request->filled('assigned_to')) {
                if ($request->assigned_to === 'me') {
                    $query->assignedTo(auth()->id());
                } elseif ($request->assigned_to === 'unassigned') {
                    $query->unassigned();
                } else {
                    $query->assignedTo($request->assigned_to);
                }
            }

            if ($request->boolean('overdue')) {
                $query->overdue();
            }
        }

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('ticket_number', 'ILIKE', "%{$search}%")
                  ->orWhere('subject', 'ILIKE', "%{$search}%");
            });
        }

        $tickets = $query->latest()->paginate(20)->withQueryString();

        return Inertia::render('support/tickets/index', [
            'tickets' => $tickets,
            'filters' => $request->only(['status', 'category', 'priority', 'assigned_to', 'search']),
            'categories' => SupportTicket::categoriesForSelect(),
            'priorities' => SupportTicket::prioritiesForSelect(),
            'statuses' => SupportTicket::statusesForSelect(),
        ]);
    }

    /**
     * Formulario de crear ticket
     */
    public function create(): Response
    {
        // Obtener certificados del usuario para vincular
        $certificateRequests = CertificateRequest::whereHas('orderItem.order', function ($q) {
            $q->where('user_id', auth()->id());
        })->with('orderItem.product')->get();

        return Inertia::render('support/tickets/create', [
            'categories' => SupportTicket::categoriesForSelect(),
            'priorities' => SupportTicket::prioritiesForSelect(),
            'certificateRequests' => $certificateRequests,
        ]);
    }

    /**
     * Crear nuevo ticket
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'description' => 'required|string|min:10',
            'category' => ['required', Rule::in(TicketCategory::values())],
            'priority' => ['required', Rule::in(TicketPriority::values())],
            'certificate_request_id' => 'nullable|exists:certificate_requests,id',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
        ]);

        $ticket = DB::transaction(function () use ($validated, $request) {
            $ticket = SupportTicket::create([
                'user_id' => auth()->id(),
                'subject' => $validated['subject'],
                'description' => $validated['description'],
                'category' => TicketCategory::from($validated['category']),
                'priority' => TicketPriority::from($validated['priority']),
                'status' => TicketStatus::OPEN,
                'source' => 'portal',
                'certificate_request_id' => $validated['certificate_request_id'] ?? null,
            ]);

            // Primera conversación
            $conversation = $ticket->conversations()->create([
                'user_id' => auth()->id(),
                'type' => 'reply',
                'body' => $validated['description'],
                'is_private' => false,
            ]);

            // Attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store("tickets/{$ticket->id}", 's3');
                    $conversation->attachments()->create([
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }
            }

            $ticket->logActivity('created', [
                'category' => $ticket->category->label(),
                'priority' => $ticket->priority->label(),
            ]);

            return $ticket;
        });

        return redirect()->route('support.tickets.show', $ticket)
            ->with('success', "Ticket {$ticket->ticket_number} creado exitosamente.");
    }

    /**
     * Ver detalle del ticket
     */
    public function show(SupportTicket $ticket): Response
    {
        // Verificar permisos
        if (!$ticket->canBeEditedBy(auth()->user())) {
            abort(403, 'No tienes permiso para ver este ticket.');
        }

        $ticket->load([
            'user:id,name,email',
            'assignedTo:id,name',
            'certificateRequest:id,status,external_id',
            'group:id,name',
            'location:id,name',
            'slaPolicy:id,name,first_response_time,resolution_time',
            'conversations' => function($query) {
                if (!auth()->user()->hasAnyRole(['admin', 'support'])) {
                    $query->where('is_private', false);
                }
                $query->with(['user:id,name', 'attachments']);
            },
            'activities.user:id,name'
        ]);

        // Solo admin/soporte puede ver estas opciones
        $canManage = auth()->user()->hasAnyRole(['admin', 'support']);
        
        return Inertia::render('support/tickets/show', [
            'ticket' => $ticket,
            'canManage' => $canManage,
            'agents' => $canManage ? User::role(['admin', 'support'])->get(['id', 'name']) : [],
            'groups' => $canManage ? AgentGroup::active()->get(['id', 'name']) : [],
            'statuses' => $canManage ? SupportTicket::statusesForSelect() : [],
            'priorities' => $canManage ? SupportTicket::prioritiesForSelect() : [],
        ]);
    }

    /**
     * Actualizar ticket
     */
    public function update(Request $request, SupportTicket $ticket): RedirectResponse
    {
        if (!$ticket->canBeEditedBy(auth()->user())) {
            abort(403);
        }

        $rules = [];

        // Usuario puede actualizar subject si está abierto
        if ($ticket->status === TicketStatus::OPEN) {
            $rules['subject'] = 'sometimes|string|max:255';
        }

        // Solo admin/soporte puede cambiar estos campos
        if (auth()->user()->hasAnyRole(['admin', 'support'])) {
            $rules = array_merge($rules, [
                'status' => ['sometimes', Rule::in(TicketStatus::values())],
                'priority' => ['sometimes', Rule::in(TicketPriority::values())],
                'category' => ['sometimes', Rule::in(TicketCategory::values())],
                'assigned_to' => 'sometimes|nullable|exists:users,id',
                'group_id' => 'sometimes|nullable|exists:agent_groups,id',
            ]);
        }

        $validated = $request->validate($rules);

        // Convertir enums
        if (isset($validated['status'])) {
            $validated['status'] = TicketStatus::from($validated['status']);
        }
        if (isset($validated['priority'])) {
            $validated['priority'] = TicketPriority::from($validated['priority']);
        }
        if (isset($validated['category'])) {
            $validated['category'] = TicketCategory::from($validated['category']);
        }

        $oldValues = $ticket->only(array_keys($validated));
        $ticket->update($validated);

        // Log cambios
        $changes = array_diff_assoc(
            array_map(fn($v) => is_object($v) ? $v->value : $v, $validated),
            array_map(fn($v) => is_object($v) ? $v->value : $v, $oldValues)
        );

        if (!empty($changes)) {
            $ticket->logActivity('updated', ['changes' => $changes]);
        }

        // Auto-resolver
        if (isset($validated['status']) && $validated['status'] === TicketStatus::CLOSED && !$ticket->resolved_at) {
            $ticket->update(['resolved_at' => now()]);
        }

        return back()->with('success', 'Ticket actualizado correctamente.');
    }

    /**
     * Responder a un ticket
     */
    public function reply(Request $request, SupportTicket $ticket): RedirectResponse
    {
        if (!$ticket->canBeEditedBy(auth()->user())) {
            abort(403);
        }

        $validated = $request->validate([
            'body' => 'required|string|min:3',
            'is_private' => 'sometimes|boolean',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => 'file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240',
        ]);

        DB::transaction(function () use ($validated, $request, $ticket) {
            $isPrivate = ($validated['is_private'] ?? false) 
                && auth()->user()->hasAnyRole(['admin', 'support']);

            $conversation = $ticket->conversations()->create([
                'user_id' => auth()->id(),
                'type' => $isPrivate ? 'note' : 'reply',
                'body' => $validated['body'],
                'is_private' => $isPrivate,
            ]);

            // Attachments
            if ($request->hasFile('attachments')) {
                foreach ($request->file('attachments') as $file) {
                    $path = $file->store("tickets/{$ticket->id}", 's3');
                    $conversation->attachments()->create([
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'size' => $file->getSize(),
                    ]);
                }
            }

            // Primera respuesta del soporte
            if (!$ticket->first_responded_at && auth()->user()->hasAnyRole(['admin', 'support'])) {
                $ticket->update(['first_responded_at' => now()]);
            }

            // Cambiar estado si corresponde
            if ($ticket->status === TicketStatus::WAITING_CUSTOMER && !$isPrivate) {
                $ticket->update(['status' => TicketStatus::IN_PROGRESS]);
            }

            $ticket->logActivity($isPrivate ? 'note_added' : 'replied');
        });

        return back()->with('success', 'Respuesta agregada correctamente.');
    }

    /**
     * Asignar ticket (admin/soporte)
     */
    public function assign(Request $request, SupportTicket $ticket): RedirectResponse
    {
        if (!auth()->user()->hasAnyRole(['admin', 'support'])) {
            abort(403);
        }

        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($validated['user_id']);
        $ticket->assignTo($user);

        return back()->with('success', "Ticket asignado a {$user->name}");
    }

    /**
     * Resolver/Cerrar ticket
     */
    public function resolve(Request $request, SupportTicket $ticket): RedirectResponse
    {
        if (!$ticket->canBeResolvedBy(auth()->user())) {
            abort(403);
        }

        $validated = $request->validate([
            'resolution_notes' => 'nullable|string',
        ]);

        $ticket->markAsResolved($validated['resolution_notes'] ?? null);

        return back()->with('success', 'Ticket cerrado exitosamente.');
    }

    /**
     * Reabrir ticket
     */
    public function reopen(SupportTicket $ticket): RedirectResponse
    {
        if (!auth()->user()->hasAnyRole(['admin', 'support'])) {
            abort(403);
        }

        $ticket->reopen();

        return back()->with('success', 'Ticket reabierto.');
    }

    /**
     * Descargar attachment
     */
    public function downloadAttachment(SupportTicket $ticket, int $attachmentId)
    {
        if (!$ticket->canBeEditedBy(auth()->user())) {
            abort(403);
        }

        $attachment = $ticket->conversations()
            ->with('attachments')
            ->get()
            ->pluck('attachments')
            ->flatten()
            ->firstWhere('id', $attachmentId);

        if (!$attachment) {
            abort(404);
        }

        return Storage::disk('s3')->download($attachment->path, $attachment->name);
    }
}