<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id', 'invoice_number', 'issue_date', 'due_date',
        'subtotal', 'tax', 'discount', 'total',
        'status', 'amount_paid', 'paid_at',
        'notes', 'terms', 'created_by', 'sent_at', 'viewed_at',
    ];

    protected function casts(): array
    {
        return [
            'issue_date'  => 'date',
            'due_date'    => 'date',
            'paid_at'     => 'datetime',
            'sent_at'     => 'datetime',
            'viewed_at'   => 'datetime',
            'subtotal'    => 'decimal:2',
            'tax'         => 'decimal:2',
            'discount'    => 'decimal:2',
            'total'       => 'decimal:2',
            'amount_paid' => 'decimal:2',
        ];
    }

    /**
     * Invoice belongs to a client.
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Invoice was created by a user (staff member).
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Kya invoice overdue hai? (due date guzar gayi aur paid nahi)
     */
    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date
            && $this->due_date->isPast()
            && ! in_array($this->status, ['paid', 'cancelled']);
    }

    /**
     * Baqi amount
     */
    public function getBalanceDueAttribute(): float
    {
        return (float) $this->total - (float) $this->amount_paid;
    }

    protected $appends = ['is_overdue', 'balance_due'];

    /**
     * Auto invoice number: INV-2026-0001
     */
    public static function generateInvoiceNumber(): string
    {
        $year = now()->year;
        $lastInvoice = static::withTrashed()
            ->where('invoice_number', 'like', "INV-{$year}-%")
            ->orderBy('invoice_number', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastInvoice) {
            $lastNumber = (int) substr($lastInvoice->invoice_number, -4);
            $nextNumber = $lastNumber + 1;
        }

        return sprintf('INV-%d-%04d', $year, $nextNumber);
    }
}