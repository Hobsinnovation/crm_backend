<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Notification;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $invoices = Invoice::with(['client:id,name,company', 'creator:id,name'])
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%")
                      ->orWhereHas('client', function ($cq) use ($search) {
                          $cq->where('name', 'like', "%{$search}%");
                      });
                });
            })
            ->when($request->client_id, fn ($q, $clientId) => $q->where('client_id', $clientId))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->boolean('overdue'), function ($q) {
                $q->whereNotNull('due_date')
                  ->where('due_date', '<', now())
                  ->whereNotIn('status', ['paid', 'cancelled']);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data'    => $invoices,
        ]);
    }

    /**
     * Client-wise invoice summary
     */
    public function byClient()
    {
        $clients = Client::withCount(['invoices'])
            ->with(['invoices' => function ($q) {
                $q->select('id', 'client_id', 'total', 'amount_paid', 'status', 'due_date');
            }])
            ->having('invoices_count', '>', 0)
            ->orderBy('name')
            ->get()
            ->map(function ($client) {
                $invoices = $client->invoices;
                return [
                    'id'             => $client->id,
                    'name'           => $client->name,
                    'company'        => $client->company,
                    'invoices_count' => $client->invoices_count,
                    'total_amount'   => (float) $invoices->sum('total'),
                    'paid_amount'    => (float) $invoices->where('status', 'paid')->sum('total'),
                    'pending_amount' => (float) $invoices
                        ->whereNotIn('status', ['paid', 'cancelled'])
                        ->sum('total'),
                    'overdue_count'  => $invoices
                        ->filter(fn ($inv) => $inv->due_date
                            && $inv->due_date->isPast()
                            && ! in_array($inv->status, ['paid', 'cancelled']))
                        ->count(),
                ];
            });

        return response()->json([
            'success' => true,
            'data'    => $clients,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_id'  => 'required|exists:clients,id',
            'issue_date' => 'required|date',
            'due_date'   => 'required|date|after_or_equal:issue_date',
            'subtotal'   => 'required|numeric|min:0',
            'tax'        => 'sometimes|numeric|min:0',
            'discount'   => 'sometimes|numeric|min:0',
            'status'     => 'sometimes|in:draft,sent,viewed,paid,unpaid,overdue,cancelled',
            'notes'      => 'nullable|string',
            'terms'      => 'nullable|string',
        ]);

        $tax      = $validated['tax'] ?? 0;
        $discount = $validated['discount'] ?? 0;

        $validated['total'] = $validated['subtotal'] + $tax - $discount;
        $validated['invoice_number'] = Invoice::generateInvoiceNumber();
        $validated['created_by'] = $request->user()->id;
        $validated['amount_paid'] = 0;

        $invoice = Invoice::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Invoice created successfully',
            'data'    => $invoice->load('client:id,name,company'),
        ], 201);
    }

    public function show(Invoice $invoice)
    {
        return response()->json([
            'success' => true,
            'data'    => $invoice->load(['client:id,name,company,email', 'creator:id,name']),
        ]);
    }

    public function update(Request $request, Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Paid invoices cannot be edited.',
            ], 422);
        }

        $validated = $request->validate([
            'client_id'  => 'sometimes|exists:clients,id',
            'issue_date' => 'sometimes|date',
            'due_date'   => 'sometimes|date',
            'subtotal'   => 'sometimes|numeric|min:0',
            'tax'        => 'sometimes|numeric|min:0',
            'discount'   => 'sometimes|numeric|min:0',
            'status'     => 'sometimes|in:draft,sent,viewed,paid,unpaid,overdue,cancelled',
            'notes'      => 'nullable|string',
            'terms'      => 'nullable|string',
        ]);

        // Amounts change hue to total recalculate karein
        $subtotal = $validated['subtotal'] ?? $invoice->subtotal;
        $tax      = $validated['tax'] ?? $invoice->tax;
        $discount = $validated['discount'] ?? $invoice->discount;
        $validated['total'] = $subtotal + $tax - $discount;

        // Status "sent" hua to sent_at set karein
        if (($validated['status'] ?? null) === 'sent' && ! $invoice->sent_at) {
            $validated['sent_at'] = now();
        }

        $invoice->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Invoice updated successfully',
            'data'    => $invoice->fresh()->load('client:id,name,company'),
        ]);
    }

    /**
     * Mark invoice as paid (full payment)
     */
    public function markPaid(Request $request, Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Invoice is already paid.',
            ], 422);
        }

        $invoice->update([
            'status'      => 'paid',
            'amount_paid' => $invoice->total,
            'paid_at'     => now(),
        ]);

        // Invoice creator ko notification (agar khud paid nahi kiya)
        if ($invoice->created_by && $invoice->created_by != $request->user()->id) {
            Notification::send(
                $invoice->created_by,
                'invoice_paid',
                'Invoice Paid',
                "Invoice {$invoice->invoice_number} (\${$invoice->total}) has been marked as paid.",
                $invoice
            );
        }

        return response()->json([
            'success' => true,
            'message' => 'Invoice marked as paid',
            'data'    => $invoice->fresh()->load('client:id,name,company'),
        ]);
    }

    public function destroy(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Paid invoices cannot be deleted.',
            ], 422);
        }

        $invoice->delete();

        return response()->json([
            'success' => true,
            'message' => 'Invoice deleted successfully',
        ]);
    }
}