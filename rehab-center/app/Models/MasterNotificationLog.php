<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterNotificationLog extends Model
{
    protected $fillable = [
        'appointment_id',
        'master_id',
        'chat_id',
        'phone',
        'status',
        'resolution_source',
        'message',
        'error_message',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'json',
    ];

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function master()
    {
        return $this->belongsTo(User::class);
    }

    public function markAsSent(string $chatId, string $resolutionSource): void
    {
        $this->update([
            'status' => 'sent',
            'chat_id' => $chatId,
            'resolution_source' => $resolutionSource,
        ]);
    }

    public function markAsFailed(string $errorMessage): void
    {
        $this->update([
            'status' => 'failed',
            'error_message' => $errorMessage,
        ]);
    }
}
