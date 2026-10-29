<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        $minutes = (int) round($this->checked_in->diffInMinutes($end));

        if ($minutes < 1) {
            return '< 1 min';
        }

        if ($minutes < 60) {
            return "{$minutes}m";
        }

        $hours = intdiv($minutes, 60);
        $remainingMins = $minutes % 60;

        return "{$hours}h {$remainingMins}m";
    }

    /**
     * Get the borrower/patron associated with this visit from the Koha database.
     */
    public function patron(): BelongsTo
    {
        return $this->belongsTo(KohaPatron::class, 'cardnumber', 'cardnumber');
    }

    /**
     * Safely resolve patron without throwing if Koha database is offline or unreachable.
     */
    public function getSafePatronAttribute(): ?KohaPatron
    {
        try {
            return $this->patron;
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Get the visitor's display name, falling back to cardnumber if Koha patron is not found.
     */
    public function getVisitorNameAttribute(): string
    {
        return $this->safe_patron?->full_name ?: $this->cardnumber;
    }
}
