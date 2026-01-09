<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\TicketApprovalGroup;
use App\Models\AuditEvent;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TicketApprovalGroupController extends Controller
{
    // Crear grupo de aprobación
    public function store(Request $request, SupportTicket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'approval_type' => 'required|in:everyone,anyone,majority',
            'group_level' => 'sometimes|integer|min:1',
            'approver_ids' => 'required|array|min:1',
            'approver_ids.*' => 'exists:users,id',
        ]);

        $group = $ticket->approvalGroups()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'approval_type' => $validated['approval_type'],
            'group_level' => $validated['group_level'] ?? 1,
            'status' => 'pending',
        ]);

        // Crear aprobaciones individuales dentro del grupo
        foreach ($validated['approver_ids'] as $approverId) {
            $ticket->approvals()->create([
                'approver_id' => $approverId,
                'approval_status' => 'pending',
                'level' => $group->group_level,
            ]);
        }

        AuditEvent::log('approval_group_created', [
            'ticket_id' => $ticket->id,
            'group_id' => $group->id,
        ]);

        return response()->json([
            'message' => 'Approval group created',
            'approval_group' => $group->load('approvals')
        ], 201);
    }

    // Listar grupos de aprobación
    public function index(SupportTicket $ticket): JsonResponse
    {
        $groups = $ticket->approvalGroups()
            ->with(['approvals.approver'])
            ->orderBy('group_level')
            ->get();

        return response()->json(['approval_groups' => $groups]);
    }

    // Actualizar grupo de aprobación
    public function update(Request $request, SupportTicket $ticket, TicketApprovalGroup $group): JsonResponse
    {
        if ($group->ticket_id !== $ticket->id) {
            return response()->json(['error' => 'Approval group not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'approval_type' => 'sometimes|in:everyone,anyone,majority',
        ]);

        $group->update($validated);

        AuditEvent::log('approval_group_updated', [
            'ticket_id' => $ticket->id,
            'group_id' => $group->id,
        ]);

        return response()->json([
            'message' => 'Approval group updated',
            'approval_group' => $group
        ]);
    }

    // Cancelar grupo de aprobación
    public function cancel(SupportTicket $ticket, TicketApprovalGroup $group): JsonResponse
    {
        if ($group->ticket_id !== $ticket->id) {
            return response()->json(['error' => 'Approval group not found'], 404);
        }

        // Cancelar todas las aprobaciones del grupo
        $ticket->approvals()
            ->where('level', $group->group_level)
            ->delete();

        $group->delete();

        AuditEvent::log('approval_group_cancelled', [
            'ticket_id' => $ticket->id,
            'group_id' => $group->id,
        ]);

        return response()->json(['message' => 'Approval group cancelled']);
    }

    // Actualizar regla de cadena de aprobación
    public function updateChainRule(Request $request, SupportTicket $ticket): JsonResponse
    {
        $validated = $request->validate([
            'chain_rule' => 'required|in:sequential,parallel',
        ]);

        // Guardar en custom_fields o en una tabla específica
        $ticket->update([
            'custom_fields' => array_merge(
                $ticket->custom_fields ?? [],
                ['approval_chain_rule' => $validated['chain_rule']]
            )
        ]);

        AuditEvent::log('approval_chain_rule_updated', [
            'ticket_id' => $ticket->id,
            'chain_rule' => $validated['chain_rule'],
        ]);

        return response()->json([
            'message' => 'Chain rule updated',
            'chain_rule' => $validated['chain_rule']
        ]);
    }
}