<?php

namespace App\Livewire;

use App\Models\Visit;
use Carbon\Carbon;
use Livewire\Component;

class VisitorKiosk extends Component
{
    public string $cardnumber = '';
    public ?string $feedbackMessage = null;
    public string $feedbackType = ''; // 'checkin', 'checkout', 'error'
    public ?string $lastActionCard = null;
    public ?string $lastActionTime = null;
    
    // Log controls
    public string $searchQuery = '';
    public string $statusFilter = 'all'; // 'all', 'inside', 'completed'
    public string $dateFilter = 'today'; // 'today', 'all_time'
    public int $perPage = 10;
    
    // Active View Mode
    public string $currentView = 'kiosk'; // 'kiosk', 'analytics'

    public function processCard(): void
    {
        $card = strtoupper(trim($this->cardnumber));

        if (empty($card)) {
            $this->feedbackType = 'error';
            $this->feedbackMessage = 'Card ID cannot be empty. Please enter or swipe a valid card.';
            $this->dispatch('play-audio', sound: 'error');
            return;
        }

        // Check if there is an active session
        $activeVisit = Visit::where('cardnumber', $card)
            ->whereNull('checked_out')
            ->latest('checked_in')
            ->first();

        if ($activeVisit) {
            // Check out
            $activeVisit->update([
                'checked_out' => now(),
            ]);

            $duration = $activeVisit->fresh()->duration;
            $this->feedbackType = 'checkout';
            $this->lastActionCard = $card;
            $this->lastActionTime = now()->format('H:i:s');
            $this->feedbackMessage = "Visitor #{$card} checked out successfully. Session duration: {$duration}.";
            $this->dispatch('play-audio', sound: 'checkout');
        } else {
            // Check in
            Visit::create([
                'cardnumber' => $card,
                'checked_in' => now(),
            ]);

            $this->feedbackType = 'checkin';
            $this->lastActionCard = $card;
            $this->lastActionTime = now()->format('H:i:s');
            $this->feedbackMessage = "Visitor #{$card} checked in successfully. Welcome!";
            $this->dispatch('play-audio', sound: 'checkin');
        }

        $this->reset('cardnumber');
    }

    public function loadMore(): void
    {
        $this->perPage += 10;
    }

    public function forceCheckout(int $visitId): void
    {
        $visit = Visit::find($visitId);
        if ($visit && is_null($visit->checked_out)) {
            $visit->update(['checked_out' => now()]);
            $this->feedbackType = 'checkout';
            $this->lastActionCard = $visit->cardnumber;
            $this->lastActionTime = now()->format('H:i:s');
            $this->feedbackMessage = "Visitor #{$visit->cardnumber} was checked out manually.";
            $this->dispatch('play-audio', sound: 'checkout');
        }
    }

    public function quickScan(string $sampleCard): void
    {
        $this->cardnumber = $sampleCard;
        $this->processCard();
    }

    public function dismissBanner(): void
    {
        $this->feedbackMessage = null;
        $this->feedbackType = '';
    }

    public function render()
    {
        // 1. Data Query for Visitor Log
        $query = Visit::latest('checked_in');

        if (!empty($this->searchQuery)) {
            $query->where('cardnumber', 'like', '%' . trim($this->searchQuery) . '%');
        }

        if ($this->statusFilter === 'inside') {
            $query->whereNull('checked_out');
        } elseif ($this->statusFilter === 'completed') {
            $query->whereNotNull('checked_out');
        }

        if ($this->dateFilter === 'today') {
            $query->whereDate('checked_in', Carbon::today());
        }

        $totalMatching = (clone $query)->count();
        $visits = $query->take($this->perPage)->get();

        // 2. Core Operational Metrics
        $currentlyInside = Visit::whereNull('checked_out')->count();
        $todayTotal = Visit::whereDate('checked_in', Carbon::today())->count();
        $todayCompleted = Visit::whereDate('checked_in', Carbon::today())
            ->whereNotNull('checked_out')
            ->get();

        $avgMinutes = 0;
        if ($todayCompleted->count() > 0) {
            $totalMins = $todayCompleted->sum(function ($v) {
                return $v->checked_in->diffInMinutes($v->checked_out);
            });
            $avgMinutes = (int) round($totalMins / $todayCompleted->count());
        }

        // 3. Deep Analytics Calculations
        $allVisitsToday = Visit::whereDate('checked_in', Carbon::today())->get();
        
        // Hourly traffic breakdown for today (9 AM to 9 PM)
        $hourlyDistribution = [];
        $peakHourName = '—';
        $peakCount = 0;

        for ($h = 8; $h <= 20; $h++) {
            $label = Carbon::today()->setHour($h)->format('g A');
            $count = $allVisitsToday->filter(function ($v) use ($h) {
                return (int)$v->checked_in->format('H') === $h;
            })->count();

            $hourlyDistribution[] = [
                'hour' => $label,
                'count' => $count,
            ];

            if ($count > $peakCount) {
                $peakCount = $count;
                $peakHourName = $label;
            }
        }

        // Peak percentage scale (for visual bars)
        $maxHourlyCount = max(1, collect($hourlyDistribution)->max('count'));

        // Visit duration cohorts (Quick, Medium, Long, Extended)
        $durationBuckets = [
            'under_30m' => 0,
            '30m_to_1h' => 0,
            '1h_to_2h' => 0,
            'over_2h' => 0,
        ];

        foreach ($todayCompleted as $v) {
            $mins = $v->checked_in->diffInMinutes($v->checked_out);
            if ($mins < 30) {
                $durationBuckets['under_30m']++;
            } elseif ($mins <= 60) {
                $durationBuckets['30m_to_1h']++;
            } elseif ($mins <= 120) {
                $durationBuckets['1h_to_2h']++;
            } else {
                $durationBuckets['over_2h']++;
            }
        }

        // Top Frequent Visitors (All time or today)
        $topVisitors = Visit::selectRaw('cardnumber, COUNT(*) as visit_count, MAX(checked_in) as last_seen')
            ->groupBy('cardnumber')
            ->orderByDesc('visit_count')
            ->limit(5)
            ->get();

        return view('livewire.visitor-kiosk', [
            'visits' => $visits,
            'totalMatching' => $totalMatching,
            'hasMore' => $this->perPage < $totalMatching,
            'currentlyInside' => $currentlyInside,
            'todayTotal' => $todayTotal,
            'todayCompletedCount' => $todayCompleted->count(),
            'avgMinutes' => $avgMinutes,
            'hourlyDistribution' => $hourlyDistribution,
            'maxHourlyCount' => $maxHourlyCount,
            'peakHourName' => $peakHourName,
            'peakCount' => $peakCount,
            'durationBuckets' => $durationBuckets,
            'topVisitors' => $topVisitors,
        ]);
    }
}
