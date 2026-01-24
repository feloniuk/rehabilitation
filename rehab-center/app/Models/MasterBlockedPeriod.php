<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class MasterBlockedPeriod extends Model
{
    protected $fillable = [
        'master_id',
        'start_date',
        'end_date',
        'reason',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function master()
    {
        return $this->belongsTo(User::class, 'master_id');
    }

    public function isActiveOn(Carbon $date): bool
    {
        return $date->between($this->start_date, $this->end_date);
    }

    public function overlaps(Carbon $startDate, Carbon $endDate): bool
    {
        return $this->start_date->lte($endDate) && $this->end_date->gte($startDate);
    }

    public function getFormattedPeriod(): string
    {
        return $this->start_date->format('d.m.Y').' - '.$this->end_date->format('d.m.Y');
    }

    public function getDurationInDays(): int
    {
        return $this->start_date->diffInDays($this->end_date) + 1;
    }
}
