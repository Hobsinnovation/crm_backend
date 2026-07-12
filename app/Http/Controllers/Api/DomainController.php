<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Domain;
use Illuminate\Http\Request;

class DomainController extends Controller
{
    public function index(Request $request)
    {
        $domains = Domain::with(['client:id,name', 'creator:id,name'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('registrar', 'like', "%{$search}%");
                });
            })
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->boolean('expiring_soon'), function ($q) {
                $q->whereNotNull('expiry_date')
                  ->whereBetween('expiry_date', [now(), now()->addDays(30)]);
            })
            ->when($request->boolean('critical'), fn ($q) => $q->where('is_critical', true))
            ->orderBy('expiry_date', 'asc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data'    => $domains,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'         => 'nullable|exists:clients,id',
            'name'              => 'required|string|max:255|unique:domains,name',
            'registrar'         => 'nullable|string|max:255',
            'registrar_account' => 'nullable|string|max:255',
            'nameservers'       => 'nullable|string',
            'registered_date'   => 'nullable|date',
            'expiry_date'       => 'nullable|date',
            'renewal_date'      => 'nullable|date',
            'auto_renewal'      => 'sometimes|boolean',
            'annual_cost'       => 'nullable|numeric|min:0',
            'status'            => 'sometimes|in:active,expired,expiring,renewal_pending',
            'is_critical'       => 'sometimes|boolean',
            'notes'             => 'nullable|string',
        ]);

        $validated['created_by'] = $request->user()->id;

        $domain = Domain::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Domain created successfully',
            'data'    => $domain->load('client:id,name'),
        ], 201);
    }

    public function show(Domain $domain)
    {
        return response()->json([
            'success' => true,
            'data'    => $domain->load(['client:id,name', 'creator:id,name']),
        ]);
    }

    public function update(Request $request, Domain $domain)
    {
        $validated = $request->validate([
            'client_id'         => 'nullable|exists:clients,id',
            'name'              => 'sometimes|string|max:255|unique:domains,name,' . $domain->id,
            'registrar'         => 'nullable|string|max:255',
            'registrar_account' => 'nullable|string|max:255',
            'nameservers'       => 'nullable|string',
            'registered_date'   => 'nullable|date',
            'expiry_date'       => 'nullable|date',
            'renewal_date'      => 'nullable|date',
            'auto_renewal'      => 'sometimes|boolean',
            'annual_cost'       => 'nullable|numeric|min:0',
            'status'            => 'sometimes|in:active,expired,expiring,renewal_pending',
            'is_critical'       => 'sometimes|boolean',
            'notes'             => 'nullable|string',
        ]);

        $domain->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Domain updated successfully',
            'data'    => $domain->fresh()->load('client:id,name'),
        ]);
    }

    /**
     * Auto-renewal toggle
     */
    public function toggleAutoRenewal(Domain $domain)
    {
        $domain->update(['auto_renewal' => ! $domain->auto_renewal]);

        return response()->json([
            'success' => true,
            'message' => $domain->auto_renewal
                ? 'Auto-renewal enabled'
                : 'Auto-renewal disabled',
            'data'    => $domain,
        ]);
    }

    public function destroy(Domain $domain)
    {
        $domain->delete();

        return response()->json([
            'success' => true,
            'message' => 'Domain deleted successfully',
        ]);
    }

    /**
     * Clients list (domain form ke dropdown ke liye)
     */
    public function clientsList()
    {
        $clients = Client::select('id', 'name', 'company')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data'    => $clients,
        ]);
    }
}