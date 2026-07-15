<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $clients = Client::with(['creator:id,name', 'assignee:id,name'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('company', 'like', "%{$search}%");
                });
            })
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data'    => $clients,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:50',
            'company'        => 'nullable|string|max:255',
            'website'        => 'nullable|string|max:255',
            'country'        => 'nullable|string|max:100',
            'city'           => 'nullable|string|max:100',
            'postal_code'    => 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone'  => 'nullable|string|max:50',
            'status'         => 'required|in:active,inactive,suspended',
            'notes'          => 'nullable|string',
            'credit_balance' => 'nullable|numeric',
        ]);

        $validated['created_by'] = $request->user()->id;
        $validated['assigned_to'] = $request->user()->id;

        $client = Client::create($validated);
        \App\Models\ActivityLog::record('created', $client);

        return response()->json([
            'success' => true,
            'message' => 'Client created successfully',
            'data'    => $client,
        ], 201);
    }

    public function show(Client $client)
    {
        return response()->json([
            'success' => true,
            'data'    => $client->load(['creator:id,name', 'assignee:id,name']),
        ]);
    }

    public function update(Request $request, Client $client)
    {
        $validated = $request->validate([
            'name'           => 'sometimes|string|max:255',
            'email'          => 'nullable|email|max:255',
            'phone'          => 'nullable|string|max:50',
            'company'        => 'nullable|string|max:255',
            'website'        => 'nullable|string|max:255',
            'country'        => 'nullable|string|max:100',
            'city'           => 'nullable|string|max:100',
            'postal_code'    => 'nullable|string|max:20',
            'address'        => 'nullable|string',
            'contact_person' => 'nullable|string|max:255',
            'contact_phone'  => 'nullable|string|max:50',
            'status'         => 'sometimes|in:active,inactive,suspended',
            'notes'          => 'nullable|string',
            'credit_balance' => 'nullable|numeric',
        ]);

        $oldValues = $client->only(array_keys($validated));
        $client->update($validated);
        \App\Models\ActivityLog::record('updated', $client, $oldValues, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Client updated successfully',
            'data'    => $client->fresh(),
        ]);
    }

    public function destroy(Client $client)
    {
     \App\Models\ActivityLog::record('deleted', $client);    
    $client->delete();

        return response()->json([
            'success' => true,
            'message' => 'Client deleted successfully',
        ]);
    }
}