<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\TicketConversation;
use App\Models\AuditEvent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class TicketConversationController extends Controller
{
    // Crear una respuesta (Reply)
    public function createReply(Request $request, SupportTicket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'body' => 'required|string',
            'to_emails' => 'nullable|array',
            'cc_emails' => 'nullable|array',
            'bcc_emails' => 'nullable|array',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240',
        ]);

        $conversation = $ticket->conversations()->create([
            'user_id' => auth()->id(),
            'type' => 'reply',
            'body' => $validated['body'],
            'body_text' => strip_tags($validated['body']),
            'incoming' => false,
            'private' => false,
            'to_emails' => $validated['to_emails'] ?? [],
            'cc_emails' => $validated['cc_emails'] ?? [],
            'bcc_emails' => $validated['bcc_emails'] ?? [],
        ]);

        // Guardar attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store("tickets/{$ticket->id}/conversations", 's3');
                $conversation->attachments()->create([
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'content_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        // Actualizar first_responded_at si es la primera respuesta
        if (!$ticket->first_responded_at) {
            $ticket->update(['first_responded_at' => now()]);
        }

        AuditEvent::log('ticket_reply_created', [
            'ticket_id' => $ticket->id,
            'conversation_id' => $conversation->id,
        ]);

        return response()->json([
            'message' => 'Reply created successfully',
            'conversation' => $conversation->load('attachments')
        ], 201);
    }

    // Crear una nota (Note)
    public function createNote(Request $request, SupportTicket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'body' => 'required|string',
            'attachments' => 'nullable|array',
            'attachments.*' => 'file|max:10240',
        ]);

        $conversation = $ticket->conversations()->create([
            'user_id' => auth()->id(),
            'type' => 'note',
            'body' => $validated['body'],
            'body_text' => strip_tags($validated['body']),
            'incoming' => false,
            'private' => true,
        ]);

        // Guardar attachments
        if ($request->hasFile('attachments')) {
            foreach ($request->file('attachments') as $file) {
                $path = $file->store("tickets/{$ticket->id}/conversations", 's3');
                $conversation->attachments()->create([
                    'name' => $file->getClientOriginalName(),
                    'path' => $path,
                    'content_type' => $file->getMimeType(),
                    'size' => $file->getSize(),
                ]);
            }
        }

        AuditEvent::log('ticket_note_created', [
            'ticket_id' => $ticket->id,
            'conversation_id' => $conversation->id,
        ]);

        return response()->json([
            'message' => 'Note created successfully',
            'conversation' => $conversation->load('attachments')
        ], 201);
    }

    // Listar todas las conversaciones
    public function index(SupportTicket $ticket): JsonResponse
    {
        $conversations = $ticket->conversations()
            ->with(['user', 'attachments'])
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json(['conversations' => $conversations]);
    }

    // Actualizar conversación
    public function update(Request $request, SupportTicket $ticket, TicketConversation $conversation): JsonResponse
    {
        if ($conversation->ticket_id !== $ticket->id) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }

        $validated = $request->validate([
            'body' => 'sometimes|string',
        ]);

        $conversation->update([
            'body' => $validated['body'] ?? $conversation->body,
            'body_text' => strip_tags($validated['body'] ?? $conversation->body),
        ]);

        AuditEvent::log('conversation_updated', [
            'ticket_id' => $ticket->id,
            'conversation_id' => $conversation->id,
        ]);

        return response()->json([
            'message' => 'Conversation updated',
            'conversation' => $conversation
        ]);
    }

    // Eliminar conversación
    public function destroy(SupportTicket $ticket, TicketConversation $conversation): JsonResponse
    {
        if ($conversation->ticket_id !== $ticket->id) {
            return response()->json(['error' => 'Conversation not found'], 404);
        }

        // Eliminar archivos adjuntos de S3
        foreach ($conversation->attachments as $attachment) {
            Storage::disk('s3')->delete($attachment->path);
        }

        $conversation->delete();

        AuditEvent::log('conversation_deleted', [
            'ticket_id' => $ticket->id,
            'conversation_id' => $conversation->id,
        ]);

        return response()->json(['message' => 'Conversation deleted']);
    }
}