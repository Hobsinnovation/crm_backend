<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
    protected $fillable = [
        'user_id', 'action', 'model', 'model_id',
        'old_values', 'new_values', 'ip_address', 'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Helper — kahin se bhi ek line mein activity log karne ke liye
     *
     * ActivityLog::record('created', $client);
     * ActivityLog::record('updated', $user, $oldValues, $newValues);
     * ActivityLog::record('login');
     */
    public static function record(
        string $action,
        ?Model $model = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): self {
        $request = request();

        return static::create([
            'user_id'    => $request->user()?->id,
            'action'     => $action,
            'model'      => $model ? class_basename($model) : null,
            'model_id'   => $model?->id,
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 255),
        ]);
    }
}