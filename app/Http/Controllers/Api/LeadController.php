<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Lead;
use App\Models\User;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function index(Request $request)
    {
        $leads = Lead::with(['assignee:id,name', 'creator:id,name', 'client:id,name'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('company', 'like', "%{$search}%");
                });
            })
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->source, fn ($q, $source) => $q->where('source', $source))
            ->when($request->boolean('mine'), fn ($q) => $q->where('assigned_to', $request->user()->id))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data'    => $leads,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'            => 'required|string|max:255',
            'email'           => 'nullable|email|max:255',
            'phone'           => 'nullable|string|max:50',
            'company'         => 'nullable|string|max:255',
            'country'         => 'nullable|string|max:100',
            'source'          => 'required|in:website,facebook,google,referral,whatsapp,other',
            'status'          => 'sometimes|in:new,contacted,proposal_sent,won,lost',
            'notes'           => 'nullable|string',
            'estimated_value' => 'nullable|numeric|min:0',
            'assigned_to'     => 'nullable|exists:users,id',
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['assigned_to'] = $validated['assigned_to'] ?? $request->user()->id;

        $lead = Lead::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Lead created successfully',
            'data'    => $lead->load('assignee:id,name'),
        ], 201);
    }

    public function show(Lead $lead)
    {
        return response()->json([
            'success' => true,
            'data'    => $lead->load(['assignee:id,name', 'creator:id,name', 'client:id,name']),
        ]);
    }

    public function update(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'name'            => 'sometimes|string|max:255',
            'email'           => 'nullable|email|max:255',
            'phone'           => 'nullable|string|max:50',
            'company'         => 'nullable|string|max:255',
            'country'         => 'nullable|string|max:100',
            'source'          => 'sometimes|in:website,facebook,google,referral,whatsapp,other',
            'status'          => 'sometimes|in:new,contacted,proposal_sent,won,lost',
            'notes'           => 'nullable|string',
            'estimated_value' => 'nullable|numeric|min:0',
        ]);

        $lead->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Lead updated successfully',
            'data'    => $lead->fresh()->load('assignee:id,name'),
        ]);
    }

    /**
     * Assign lead to a user (leads.assign permission required)
     */
    public function assign(Request $request, Lead $lead)
    {
        $validated = $request->validate([
            'assigned_to' => 'required|exists:users,id',
        ]);

        $lead->update(['assigned_to' => $validated['assigned_to']]);

        // Assigned user ko notification bhejein
        if ($validated['assigned_to'] != $request->user()->id) {
            \App\Models\Notification::send(
                $validated['assigned_to'],
                'lead_assigned',
                'New Lead Assigned',
                "Lead \"{$lead->name}\" has been assigned to you by {$request->user()->name}.",
                $lead
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Lead assigned successfully',
            'data'    => $lead->fresh()->load('assignee:id,name'),
        ]);
    }

    /**
     * Convert a lead into a client
     */
    public function convert(Request $request, Lead $lead)
    {
        if ($lead->converted_to_client_id) {
            return response()->json([
                'success' => false,
                'message' => 'This lead has already been converted.',
            ], 422);
        }

        $client = Client::create([
            'name'        => $lead->name,
            'email'       => $lead->email,
            'phone'       => $lead->phone,
            'company'     => $lead->company,
            'country'     => $lead->country,
            'status'      => 'active',
            'notes'       => $lead->notes,
            'created_by'  => $request->user()->id,
            'assigned_to' => $lead->assigned_to,
        ]);

        $lead->update([
            'status'                 => 'won',
            'converted_to_client_id' => $client->id,
            'conversion_date'        => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Lead converted to client successfully',
            'data'    => [
                'lead'   => $lead->fresh()->load('client:id,name'),
                'client' => $client,
            ],
        ]);
    }

    public function destroy(Lead $lead)
    {
        $lead->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lead deleted successfully',
        ]);
    }

    /**
     * Assignable users list (for assign dropdown)
     */
    public function assignableUsers()
    {
        $users = User::where('is_active', true)
            ->select('id', 'name', 'role')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $users,
        ]);
    }
}