<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use HasFactory;

    protected $fillable = [
        'cardnumber',
        'checked_in',
        'checked_out',
    ];

    protected function casts(): array
    {
        return [
            'checked_in' => 'datetime',
            'checked_out' => 'datetime',
        ];
    }

    /**
     * Scope to currently active visits (checked in, not checked out yet).
     */
    public function scopeActive(Builder $query): void
    {
        $query->whereNull('checked_out');
    }

    /**
     * Scope to completed visits.
     */
    public function scopeCompleted(Builder $query): void
    {
        $query->whereNotNull('checked_out');
    }

    /**
     * Scope to today's visits.
     */
    public function scopeToday(Builder $query): void
    {
        $query->whereDate('checked_in', Carbon::today());
    }

    /**
     * Calculate duration of visit in human-readable format.
     */
    public function getDurationAttribute(): string
    {
        $end = $this->checked_out ?? now();
        $minutes = $this->checked_in->diffInMinutes($end);

        if ($minutes < 1) {
            return '< 1 min';
        }

        if ($minutes < 60) {
            return "{$minutes}m";
        }

        $hours = floor($minutes / 60);
        $remainingMins = $minutes % 60;

        return "{$hours}h {$remainingMins}m";
    }
}
