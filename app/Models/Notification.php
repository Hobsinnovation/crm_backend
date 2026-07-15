<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id', 'type', 'title', 'message',
        'notifiable_type', 'notifiable_id', 'read_at', 'data',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'data'    => 'array',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function notifiable()
    {
        return $this->morphTo();
    }

    /**
     * Helper — kahin se bhi notification bhejne ke liye
     */
    public static function send(
        int $userId,
        string $type,
        string $title,
        string $message,
        ?Model $notifiable = null,
        array $data = []
    ): self {
        return static::create([
            'user_id'         => $userId,
            'type'            => $type,
            'title'           => $title,
            'message'         => $message,
            'notifiable_type' => $notifiable ? get_class($notifiable) : null,
            'notifiable_id'   => $notifiable?->id,
            'data'            => $data,
        ]);
    }
}