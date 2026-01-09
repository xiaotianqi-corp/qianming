<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\CannedResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\RedirectResponse;

class CannedResponseController extends Controller
{
    public function index(Request $request): Response
    {
        if (!auth()->user()->hasAnyRole(['admin', 'support'])) {
            abort(403);
        }

        $query = CannedResponse::query();

        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'ILIKE', "%{$request->search}%")
                  ->orWhere('content', 'ILIKE', "%{$request->search}%");
            });
        }

        $responses = $query->latest()->paginate(20);

        return Inertia::render('support/cannedResponses/index', [
            'responses' => $responses,
            'filters' => $request->only(['category', 'search']),
        ]);
    }

    public function create(): Response
    {
        if (!auth()->user()->hasAnyRole(['admin', 'support'])) {
            abort(403);
        }

        return Inertia::render('support/cannedResponses/create');
    }

    public function store(Request $request): RedirectResponse
    {
        if (!auth()->user()->hasAnyRole(['admin', 'support'])) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string',
            'active' => 'boolean',
        ]);

        CannedResponse::create($validated);

        return redirect()->route('support.canned-responses.index')
            ->with('success', 'Respuesta predefinida creada.');
    }

    public function edit(CannedResponse $cannedResponse): Response
    {
        if (!auth()->user()->hasAnyRole(['admin', 'support'])) {
            abort(403);
        }

        return Inertia::render('support/cannedResponses/edit', [
            'response' => $cannedResponse,
        ]);
    }

    public function update(Request $request, CannedResponse $cannedResponse): RedirectResponse
    {
        if (!auth()->user()->hasAnyRole(['admin', 'support'])) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'category' => 'nullable|string',
            'active' => 'boolean',
        ]);

        $cannedResponse->update($validated);

        return redirect()->route('support.canned-responses.index')
            ->with('success', 'Respuesta predefinida actualizada.');
    }

    public function destroy(CannedResponse $cannedResponse): RedirectResponse
    {
        if (!auth()->user()->hasRole('admin')) {
            abort(403);
        }

        $cannedResponse->delete();

        return back()->with('success', 'Respuesta predefinida eliminada.');
    }
}