<div 
    x-data="{
        audioCtx: null,
        initAudio() {
            if (!this.audioCtx) {
                this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
            }
        },
        playTone(type) {
            try {
                this.initAudio();
                const ctx = this.audioCtx;
                if (!ctx) return;

                if (type === 'checkin') {
                    const osc1 = ctx.createOscillator();
                    const osc2 = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc1.type = 'sine';
                    osc2.type = 'sine';
                    osc1.frequency.setValueAtTime(523.25, ctx.currentTime);
                    osc2.frequency.setValueAtTime(783.99, ctx.currentTime + 0.1);
                    gain.gain.setValueAtTime(0.08, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.35);
                    osc1.connect(gain);
                    osc2.connect(gain);
                    gain.connect(ctx.destination);
                    osc1.start(ctx.currentTime);
                    osc1.stop(ctx.currentTime + 0.1);
                    osc2.start(ctx.currentTime + 0.1);
                    osc2.stop(ctx.currentTime + 0.35);
                } else if (type === 'checkout') {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'triangle';
                    osc.frequency.setValueAtTime(783.99, ctx.currentTime);
                    osc.frequency.exponentialRampToValueAtTime(523.25, ctx.currentTime + 0.3);
                    gain.gain.setValueAtTime(0.09, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.35);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start(ctx.currentTime);
                    osc.stop(ctx.currentTime + 0.35);
                } else if (type === 'error') {
                    const osc = ctx.createOscillator();
                    const gain = ctx.createGain();
                    osc.type = 'sawtooth';
                    osc.frequency.setValueAtTime(160, ctx.currentTime);
                    gain.gain.setValueAtTime(0.12, ctx.currentTime);
                    gain.gain.exponentialRampToValueAtTime(0.0001, ctx.currentTime + 0.25);
                    osc.connect(gain);
                    gain.connect(ctx.destination);
                    osc.start(ctx.currentTime);
                    osc.stop(ctx.currentTime + 0.25);
                }
            } catch (e) {
                console.warn('Audio feedback notice:', e);
            }
        }
    }"
    x-on:play-audio.window="playTone($event.detail.sound)"
    class="min-h-screen bg-[#F8FAFC] text-[#0F172A] flex flex-col font-sans antialiased"
>
    <!-- Top Utility Header with Tabbed Navigation -->
    <header class="bg-white border-b border-slate-200 sticky top-0 z-30">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-16 flex items-center justify-between">
            <div class="flex items-center gap-6">
                <!-- Brand -->
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-lg bg-indigo-600 flex items-center justify-center text-white font-bold shadow-xs">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                    </div>
                    <div>
                        <span class="text-base font-semibold text-slate-900 tracking-tight">OrbitEntry</span>
                        <span class="ml-2 px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-600 border border-slate-200">Library Suite</span>
                    </div>
                </div>

                <!-- Navigation Tabs (Kiosk vs Analytics) -->
                <nav class="hidden sm:flex items-center gap-1 border-l border-slate-200 pl-6">
                    <button 
                        type="button" 
                        wire:click="$set('currentView', 'kiosk')"
                        class="px-3.5 py-1.5 rounded-lg text-xs font-medium transition cursor-pointer flex items-center gap-2 {{ $currentView === 'kiosk' ? 'bg-slate-100 text-slate-900 font-semibold' : 'text-slate-500 hover:text-slate-800' }}"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        <span>Visitor Kiosk</span>
                    </button>
                    <button 
                        type="button" 
                        wire:click="$set('currentView', 'analytics')"
                        class="px-3.5 py-1.5 rounded-lg text-xs font-medium transition cursor-pointer flex items-center gap-2 {{ $currentView === 'analytics' ? 'bg-slate-100 text-slate-900 font-semibold' : 'text-slate-500 hover:text-slate-800' }}"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        <span>Traffic Analytics</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-indigo-50 text-indigo-700 font-bold border border-indigo-200">New</span>
                    </button>
                </nav>
            </div>

            <!-- Right Controls: Status & Clock -->
            <div class="flex items-center gap-4">
                <div class="flex items-center gap-2 px-2.5 py-1 rounded-full bg-emerald-50 border border-emerald-200/80 text-emerald-700 text-xs font-medium">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    <span>System Active</span>
                </div>

                <div x-data="{ time: new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', second:'2-digit'}) }" 
                     x-init="setInterval(() => time = new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit', second:'2-digit'}), 1000)"
                     class="hidden sm:block text-xs font-mono font-medium text-slate-500">
                    <span x-text="time"></span>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Body Container -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 w-full flex-1 flex flex-col gap-6">
        
        <!-- Feedback Banner -->
        @if ($feedbackMessage)
            <div 
                x-data="{ show: true }" 
                x-show="show" 
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="opacity-0 -translate-y-1"
                x-transition:enter-end="opacity-100 translate-y-0"
                class="rounded-xl border p-4 shadow-2xs flex items-start justify-between gap-3
                    {{ $feedbackType === 'checkin' ? 'bg-emerald-50/90 border-emerald-200 text-emerald-950' : '' }}
                    {{ $feedbackType === 'checkout' ? 'bg-indigo-50/90 border-indigo-200 text-indigo-950' : '' }}
                    {{ $feedbackType === 'error' ? 'bg-rose-50/90 border-rose-200 text-rose-950' : '' }}"
            >
                <div class="flex items-start gap-3">
                    <div class="mt-0.5">
                        @if ($feedbackType === 'checkin')
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        @elseif ($feedbackType === 'checkout')
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                        @else
                            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        @endif
                    </div>
                    <div>
                        <div class="text-sm font-semibold tracking-tight">{{ $feedbackMessage }}</div>
                        @if ($lastActionTime)
                            <div class="text-xs opacity-75 mt-0.5 font-mono">Recorded at {{ $lastActionTime }}</div>
                        @endif
                    </div>
                </div>
                <button 
                    type="button" 
                    wire:click="dismissBanner" 
                    class="text-slate-400 hover:text-slate-600 p-1 rounded-md transition cursor-pointer"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>
        @endif

        <!-- High-Level Metric KPI Bar (Consistent across both tabs) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Metric 1: Inside Now -->
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs">
                <div class="flex items-center justify-between text-xs font-medium text-slate-500 mb-2">
                    <span>Currently Inside</span>
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 ring-4 ring-emerald-50"></span>
                </div>
                <div class="text-3xl font-bold text-slate-900 tracking-tight">{{ $currentlyInside }}</div>
                <div class="mt-2 text-xs text-slate-500 flex items-center gap-1.5">
                    <span class="font-medium text-slate-700">Active occupants</span>
                    <span>&bull;</span>
                    <span>Live room capacity</span>
                </div>
            </div>

            <!-- Metric 2: Today's Total Check-ins -->
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs">
                <div class="flex items-center justify-between text-xs font-medium text-slate-500 mb-2">
                    <span>Total Check-ins Today</span>
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
                <div class="text-3xl font-bold text-slate-900 tracking-tight">{{ $todayTotal }}</div>
                <div class="mt-2 text-xs text-slate-500 flex items-center gap-1.5">
                    <span class="font-medium text-slate-700">{{ $todayCompletedCount }} departed</span>
                    <span>&bull;</span>
                    <span>Today's foot traffic</span>
                </div>
            </div>

            <!-- Metric 3: Avg Duration -->
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs">
                <div class="flex items-center justify-between text-xs font-medium text-slate-500 mb-2">
                    <span>Avg Visit Duration</span>
                    <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div class="text-3xl font-bold text-slate-900 tracking-tight">
                    {{ $avgMinutes }} <span class="text-lg font-medium text-slate-500">min</span>
                </div>
                <div class="mt-2 text-xs text-slate-500">
                    Calculated from completed sessions
                </div>
            </div>

            <!-- Metric 4: Peak Activity Hour -->
            <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-2xs">
                <div class="flex items-center justify-between text-xs font-medium text-slate-500 mb-2">
                    <span>Peak Rush Hour</span>
                    <span class="px-1.5 py-0.5 rounded text-[10px] font-semibold bg-amber-50 text-amber-700 border border-amber-200">Today</span>
                </div>
                <div class="text-3xl font-bold text-slate-900 tracking-tight">
                    {{ $peakHourName }}
                </div>
                <div class="mt-2 text-xs text-slate-500">
                    Highest check-in spike ({{ $peakCount }} entries)
                </div>
            </div>
        </div>

        <!-- VIEW MODE 1: KIOSK (Scanner + Interactive Log) -->
        @if ($currentView === 'kiosk')
            <!-- Scanner Card -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
                <div class="p-6 sm:p-8">
                    <div class="max-w-2xl mx-auto">
                        <div class="text-center mb-6">
                            <h2 class="text-xl font-bold text-slate-900 tracking-tight">Card Entry & Badge Scanner</h2>
                            <p class="text-sm text-slate-500 mt-1">
                                Swipe an RFID card, scan a barcode, or manually type a visitor ID.
                            </p>
                        </div>

                        <!-- Form -->
                        <form wire:submit="processCard" class="space-y-4">
                            <div class="relative flex items-center">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none text-slate-400">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
                                    </svg>
                                </div>
                                <input 
                                    type="text" 
                                    wire:model="cardnumber"
                                    id="card-input"
                                    autofocus
                                    placeholder="Scan card or enter ID (e.g. LIB-1001)..." 
                                    class="w-full pl-11 pr-28 py-3.5 bg-white border border-slate-300 rounded-lg text-base sm:text-lg font-mono text-slate-900 placeholder-slate-400 focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 shadow-2xs transition"
                                />
                                <button 
                                    type="submit" 
                                    class="absolute right-2 top-2 bottom-2 px-5 bg-indigo-600 hover:bg-indigo-700 active:bg-indigo-800 text-white font-medium text-sm rounded-md shadow-2xs transition cursor-pointer flex items-center gap-1.5"
                                >
                                    <span wire:loading.remove wire:target="processCard">Submit</span>
                                    <span wire:loading wire:target="processCard" class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
                                </button>
                            </div>
                        </form>

                        <!-- Quick Test Chips -->
                        <div class="mt-6 pt-5 border-t border-slate-100 flex flex-wrap items-center justify-between gap-2">
                            <span class="text-xs font-medium text-slate-500">Quick Test Cards:</span>
                            <div class="flex flex-wrap gap-2">
                                <button type="button" wire:click="quickScan('LIB-1001')" class="px-2.5 py-1 text-xs font-mono font-medium rounded-md bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 transition cursor-pointer">#LIB-1001</button>
                                <button type="button" wire:click="quickScan('LIB-1002')" class="px-2.5 py-1 text-xs font-mono font-medium rounded-md bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 transition cursor-pointer">#LIB-1002</button>
                                <button type="button" wire:click="quickScan('LIB-1003')" class="px-2.5 py-1 text-xs font-mono font-medium rounded-md bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 transition cursor-pointer">#LIB-1003</button>
                                <button type="button" wire:click="quickScan('STU-8820')" class="px-2.5 py-1 text-xs font-mono font-medium rounded-md bg-slate-50 hover:bg-slate-100 text-slate-700 border border-slate-200 transition cursor-pointer">#STU-8820</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modern Table with Progressive 'See More' -->
            <div class="bg-white rounded-xl border border-slate-200 shadow-2xs overflow-hidden">
                <!-- Filter Bar -->
                <div class="px-6 py-4 border-b border-slate-200 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex flex-wrap items-center gap-2 w-full md:w-auto">
                        <!-- Status Filter -->
                        <div class="inline-flex rounded-lg border border-slate-200 p-0.5 bg-slate-50 text-xs font-medium">
                            <button 
                                type="button" 
                                wire:click="$set('statusFilter', 'all')" 
                                class="px-3 py-1.5 rounded-md transition cursor-pointer {{ $statusFilter === 'all' ? 'bg-white text-slate-900 shadow-2xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}"
                            >
                                All Logs
                            </button>
                            <button 
                                type="button" 
                                wire:click="$set('statusFilter', 'inside')" 
                                class="px-3 py-1.5 rounded-md transition cursor-pointer flex items-center gap-1.5 {{ $statusFilter === 'inside' ? 'bg-white text-slate-900 shadow-2xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}"
                            >
                                <span>Inside</span>
                                @if ($currentlyInside > 0)
                                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-emerald-100 text-emerald-800 font-bold">{{ $currentlyInside }}</span>
                                @endif
                            </button>
                            <button 
                                type="button" 
                                wire:click="$set('statusFilter', 'completed')" 
                                class="px-3 py-1.5 rounded-md transition cursor-pointer {{ $statusFilter === 'completed' ? 'bg-white text-slate-900 shadow-2xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}"
                            >
                                Completed
                            </button>
                        </div>

                        <!-- Date Filter -->
                        <div class="inline-flex rounded-lg border border-slate-200 p-0.5 bg-slate-50 text-xs font-medium">
                            <button 
                                type="button" 
                                wire:click="$set('dateFilter', 'today')" 
                                class="px-3 py-1.5 rounded-md transition cursor-pointer {{ $dateFilter === 'today' ? 'bg-white text-slate-900 shadow-2xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}"
                            >
                                Today
                            </button>
                            <button 
                                type="button" 
                                wire:click="$set('dateFilter', 'all_time')" 
                                class="px-3 py-1.5 rounded-md transition cursor-pointer {{ $dateFilter === 'all_time' ? 'bg-white text-slate-900 shadow-2xs font-semibold' : 'text-slate-600 hover:text-slate-900' }}"
                            >
                                All Time
                            </button>
                        </div>
                    </div>

                    <!-- Search Input -->
                    <div class="w-full md:w-72">
                        <div class="relative">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-slate-400">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>
                            <input 
                                type="text" 
                                wire:model.live.debounce.250ms="searchQuery" 
                                placeholder="Search by Card ID..." 
                                class="w-full pl-9 pr-3 py-1.5 bg-white border border-slate-300 rounded-md text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-indigo-600 focus:ring-1 focus:ring-indigo-600 transition shadow-2xs"
                            />
                        </div>
                    </div>
                </div>

                <!-- Table Content -->
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm text-slate-700">
                        <thead class="bg-slate-50 text-[11px] uppercase tracking-wider text-slate-500 border-b border-slate-200">
                            <tr>
                                <th scope="col" class="py-3 px-6 font-semibold">Card ID</th>
                                <th scope="col" class="py-3 px-6 font-semibold">Checked In</th>
                                <th scope="col" class="py-3 px-6 font-semibold">Checked Out</th>
                                <th scope="col" class="py-3 px-6 font-semibold">Duration</th>
                                <th scope="col" class="py-3 px-6 font-semibold">Status</th>
                                <th scope="col" class="py-3 px-6 font-semibold text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($visits as $visit)
                                <tr class="hover:bg-slate-50/70 transition">
                                    <td class="py-3.5 px-6 font-mono font-medium text-slate-900 flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full {{ is_null($visit->checked_out) ? 'bg-emerald-500' : 'bg-slate-300' }}"></span>
                                        <span>{{ $visit->cardnumber }}</span>
                                    </td>
                                    <td class="py-3.5 px-6 text-slate-600 text-xs font-mono">
                                        {{ $visit->checked_in->format('M d, Y · H:i:s') }}
                                    </td>
                                    <td class="py-3.5 px-6 text-xs font-mono">
                                        @if ($visit->checked_out)
                                            <span class="text-slate-600">{{ $visit->checked_out->format('M d, Y · H:i:s') }}</span>
                                        @else
                                            <span class="text-slate-400 italic">—</span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-6 text-xs font-mono text-slate-600">
                                        {{ $visit->duration }}
                                    </td>
                                    <td class="py-3.5 px-6">
                                        @if (is_null($visit->checked_out))
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                Inside
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium bg-slate-100 text-slate-600 border border-slate-200">
                                                Completed
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-3.5 px-6 text-right">
                                        @if (is_null($visit->checked_out))
                                            <button 
                                                type="button" 
                                                wire:click="forceCheckout({{ $visit->id }})" 
                                                class="text-xs font-medium text-slate-600 hover:text-rose-600 px-2 py-1 rounded hover:bg-rose-50 transition cursor-pointer"
                                            >
                                                Check Out
                                            </button>
                                        @else
                                            <span class="text-xs text-slate-400">—</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-12 text-center text-slate-500">
                                        <div class="flex flex-col items-center justify-center gap-2">
                                            <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                            <div class="text-sm font-medium text-slate-800">No records found</div>
                                            <div class="text-xs text-slate-400">Scan a card or adjust filters.</div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Progressive 'See More' Footer -->
                <div class="px-6 py-4 border-t border-slate-200 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3">
                    <div class="text-xs text-slate-500">
                        Showing <span class="font-medium text-slate-700">{{ min($perPage, $totalMatching) }}</span> of <span class="font-medium text-slate-700">{{ $totalMatching }}</span> total records
                    </div>

                    @if ($hasMore)
                        <button 
                            type="button" 
                            wire:click="loadMore"
                            class="px-4 py-2 text-xs font-semibold text-slate-700 bg-white hover:bg-slate-50 active:bg-slate-100 border border-slate-300 rounded-lg shadow-2xs transition cursor-pointer flex items-center gap-2"
                        >
                            <span wire:loading.remove wire:target="loadMore">See More Records (+10)</span>
                            <span wire:loading wire:target="loadMore" class="flex items-center gap-1.5">
                                <span class="animate-spin inline-block w-3.5 h-3.5 border-2 border-indigo-600 border-t-transparent rounded-full"></span>
                                <span>Loading...</span>
                            </span>
                            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                    @else
                        <div class="text-xs text-slate-400 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>All records loaded</span>
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- VIEW MODE 2: PROPER ANALYTICS DASHBOARD -->
        @if ($currentView === 'analytics')
            <div class="space-y-6">
                <!-- Section 1: Hourly Distribution Chart (Clean Bar Visualization) -->
                <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-6">
                        <div>
                            <h3 class="text-base font-semibold text-slate-900 tracking-tight">Today's Traffic Distribution by Hour</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Distribution of check-in activity throughout the day (8:00 AM &ndash; 8:00 PM)</p>
                        </div>
                        <div class="flex items-center gap-2 text-xs text-slate-500">
                            <span class="w-2.5 h-2.5 rounded-sm bg-indigo-600"></span>
                            <span>Hourly Check-ins</span>
                        </div>
                    </div>

                    <!-- Bar Graph -->
                    <div class="pt-4 pb-2">
                        <div class="grid grid-cols-13 gap-2 items-end h-48 border-b border-slate-200 pb-2">
                            @foreach ($hourlyDistribution as $bar)
                                @php
                                    $heightPercent = $maxHourlyCount > 0 ? round(($bar['count'] / $maxHourlyCount) * 100) : 0;
                                @endphp
                                <div class="flex flex-col items-center h-full justify-end group relative">
                                    <!-- Tooltip on Hover -->
                                    <div class="absolute -top-8 hidden group-hover:flex px-2 py-1 bg-slate-900 text-white text-[10px] rounded shadow whitespace-nowrap z-10">
                                        {{ $bar['hour'] }}: {{ $bar['count'] }} {{ Str::plural('visit', $bar['count']) }}
                                    </div>
                                    <!-- Bar -->
                                    <div 
                                        style="height: {{ max(4, $heightPercent) }}%;" 
                                        class="w-full rounded-t-sm transition-all duration-300 {{ $bar['count'] > 0 ? 'bg-indigo-600 group-hover:bg-indigo-500' : 'bg-slate-100' }}"
                                    ></div>
                                    <!-- Label -->
                                    <span class="text-[10px] font-mono text-slate-400 mt-2 rotate-45 sm:rotate-0 origin-left sm:origin-center">{{ $bar['hour'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Section 2: Duration Cohorts & Top Frequent Visitors -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Duration Cohorts -->
                    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs">
                        <div class="mb-5">
                            <h3 class="text-base font-semibold text-slate-900 tracking-tight">Visit Duration Breakdown</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Length of library sessions based on completed departures today</p>
                        </div>

                        <div class="space-y-4">
                            @php
                                $totalBucketVisits = max(1, array_sum($durationBuckets));
                            @endphp

                            <!-- Quick (<30m) -->
                            <div>
                                <div class="flex justify-between text-xs font-medium text-slate-700 mb-1">
                                    <span>Quick Visit (&lt; 30 mins)</span>
                                    <span class="font-mono text-slate-500">{{ $durationBuckets['under_30m'] }} ({{ round(($durationBuckets['under_30m'] / $totalBucketVisits) * 100) }}%)</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ ($durationBuckets['under_30m'] / $totalBucketVisits) * 100 }}%"></div>
                                </div>
                            </div>

                            <!-- Medium (30m - 1h) -->
                            <div>
                                <div class="flex justify-between text-xs font-medium text-slate-700 mb-1">
                                    <span>Standard Session (30m &ndash; 1 hr)</span>
                                    <span class="font-mono text-slate-500">{{ $durationBuckets['30m_to_1h'] }} ({{ round(($durationBuckets['30m_to_1h'] / $totalBucketVisits) * 100) }}%)</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div class="bg-indigo-500 h-2 rounded-full" style="width: {{ ($durationBuckets['30m_to_1h'] / $totalBucketVisits) * 100 }}%"></div>
                                </div>
                            </div>

                            <!-- Long (1h - 2h) -->
                            <div>
                                <div class="flex justify-between text-xs font-medium text-slate-700 mb-1">
                                    <span>Extended Study (1 &ndash; 2 hrs)</span>
                                    <span class="font-mono text-slate-500">{{ $durationBuckets['1h_to_2h'] }} ({{ round(($durationBuckets['1h_to_2h'] / $totalBucketVisits) * 100) }}%)</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div class="bg-amber-500 h-2 rounded-full" style="width: {{ ($durationBuckets['1h_to_2h'] / $totalBucketVisits) * 100 }}%"></div>
                                </div>
                            </div>

                            <!-- Deep Study (>2h) -->
                            <div>
                                <div class="flex justify-between text-xs font-medium text-slate-700 mb-1">
                                    <span>Deep Focus (&gt; 2 hrs)</span>
                                    <span class="font-mono text-slate-500">{{ $durationBuckets['over_2h'] }} ({{ round(($durationBuckets['over_2h'] / $totalBucketVisits) * 100) }}%)</span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden">
                                    <div class="bg-rose-500 h-2 rounded-full" style="width: {{ ($durationBuckets['over_2h'] / $totalBucketVisits) * 100 }}%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Frequent Visitors -->
                    <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-2xs">
                        <div class="mb-5">
                            <h3 class="text-base font-semibold text-slate-900 tracking-tight">Top Frequent Cards</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Most active cards logged across all database records</p>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-xs">
                                <thead>
                                    <tr class="text-slate-400 border-b border-slate-100 pb-2 uppercase tracking-wider font-semibold">
                                        <th class="pb-2">Card ID</th>
                                        <th class="pb-2 text-center">Visits</th>
                                        <th class="pb-2 text-right">Last Visit</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @forelse ($topVisitors as $top)
                                        <tr class="hover:bg-slate-50/50">
                                            <td class="py-2.5 font-mono font-medium text-slate-800">
                                                {{ $top->cardnumber }}
                                            </td>
                                            <td class="py-2.5 text-center">
                                                <span class="px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 font-bold font-mono">
                                                    {{ $top->visit_count }}
                                                </span>
                                            </td>
                                            <td class="py-2.5 text-right font-mono text-slate-500">
                                                {{ Carbon\Carbon::parse($top->last_seen)->diffForHumans() }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="py-4 text-center text-slate-400">No data available yet</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-slate-200 py-4 px-6 text-xs text-slate-500 mt-auto">
        <div class="max-w-7xl mx-auto flex flex-col sm:flex-row items-center justify-between gap-2">
            <div>&copy; {{ date('Y') }} OrbitEntry Visitor Management Suite</div>
            <div class="flex items-center gap-4 text-slate-400">
                <span>Progressive Loading</span>
                <span>&bull;</span>
                <span>SQLite DB</span>
                <span>&bull;</span>
                <span>Real-Time Analytics</span>
            </div>
        </div>
    </footer>
</div>
