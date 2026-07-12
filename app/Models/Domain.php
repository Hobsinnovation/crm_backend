<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Domain extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'client_id', 'name', 'registrar', 'registrar_account',
        'nameservers', 'registered_date', 'expiry_date', 'renewal_date',
        'auto_renewal', 'annual_cost', 'status', 'is_critical',
        'notes', 'created_by',
    ];

    protected function casts(): array
    {
        return [
            'registered_date' => 'date',
            'expiry_date'     => 'date',
            'renewal_date'    => 'date',
            'auto_renewal'    => 'boolean',
            'is_critical'     => 'boolean',
            'annual_cost'     => 'decimal:2',
        ];
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Expiry tak kitne din baqi hain
     */
    public function getDaysToExpiryAttribute(): ?int
    {
        if (! $this->expiry_date) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->expiry_date, false);
    }

    protected $appends = ['days_to_expiry'];
}