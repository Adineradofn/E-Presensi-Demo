@extends('admin.app_admin')

@section('content')
    {{-- Background --}}
    <div aria-hidden="true"
        class="fixed inset-0 -z-10 pointer-events-none bg-gradient-to-br from-emerald-50 via-white to-sky-50"></div>
    <div aria-hidden="true" class="fixed inset-0 -z-10 opacity-[0.03] pointer-events-none select-none"
        style="background-image: radial-gradient(circle at 1px 1px, #000 1px, transparent 1px); background-size: 22px 22px;">
    </div>

    <div class="relative min-h-[78vh] w-full overflow-hidden" x-data="dashboardRealtime()" x-init="init()">
        <div class="relative p-4 sm:p-6 max-w-7xl mx-auto">
            {{-- Header --}}
            <div class="mb-6 sm:mb-8">
                <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
                    <div class="min-w-0 max-w-[88vw] sm:max-w-none">
                        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 truncate">
                            Selamat Datang,
                            <span class="text-emerald-700 inline-block max-w-[70vw] sm:max-w-none align-middle truncate">
                                {{ Auth::user()->first_name ?: 'Admin' }}
                            </span>
                            <span aria-hidden="true">👋</span>
                        </h1>
                        <p class="text-gray-600 mt-1 flex flex-wrap items-center gap-x-1">
                            <span x-text="now.day"></span>,
                            <span x-text="now.date"></span>
                            <span class="hidden sm:inline">—</span>
                            <span class="tabular-nums" x-text="now.time"></span>
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2 sm:gap-3 justify-between sm:justify-end">
                        <span
                            class="inline-flex items-center gap-2 text-xs font-medium px-2.5 py-1.5 rounded-full bg-white/70 border border-emerald-100 text-emerald-700 shadow-sm">
                            <span class="relative flex h-2.5 w-2.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-60"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                            </span>
                            Realtime aktif
                        </span>
                        <span class="text-xs text-gray-500">
                            Sinkronisasi: <span class="tabular-nums" x-text="lastUpdatedLabel"></span>
                        </span>
                    </div>
                </div>
            </div>

            <h2 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6 text-gray-900">Dashboard</h2>

            {{-- Grid kartu --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-4 sm:gap-6">

                {{-- 1) Kehadiran Hari Ini --}}
                <div
                    class="group relative overflow-hidden rounded-2xl shadow-sm ring-1 ring-black/5 bg-gradient-to-br from-emerald-500 to-emerald-600 text-white p-4 sm:p-6 flex items-center justify-between gap-4 min-w-0">
                    <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-white/20 blur-2xl transition-all duration-500 group-hover:scale-110"></div>
                    <div class="min-w-0 relative">
                        <h3 class="text-base sm:text-lg font-semibold leading-tight">Kehadiran Hari Ini</h3>
                        <template x-if="loading">
                            <div class="mt-3 sm:mt-4 h-9 sm:h-10 w-24 rounded-md bg-white/30 animate-pulse"></div>
                        </template>
                        <p x-show="!loading" class="text-3xl sm:text-4xl font-bold mt-3 sm:mt-4 tabular-nums"
                           x-text="stats.hadir" aria-live="polite" aria-atomic="true"></p>
                    </div>
                    <div class="shrink-0 text-white/90 relative">
                        <img src="{{ asset('images/user_icon.svg') }}" class="h-8 w-8" alt="Hadir">
                    </div>
                </div>

                {{-- 2) Tidak Hadir (Izin/Cuti/Alpa/Sakit) --}}
                <div
                    class="group relative overflow-hidden rounded-2xl shadow-sm ring-1 ring-black/5 bg-gradient-to-br from-orange-500 to-amber-500 text-white p-4 sm:p-6 flex items-center justify-between gap-4 min-w-0">
                    <div class="absolute -left-12 -bottom-12 h-44 w-44 rounded-full bg-white/20 blur-2xl transition-all duration-500 group-hover:scale-110"></div>
                    <div class="min-w-0 relative">
                        <h3 class="text-base sm:text-lg font-semibold leading-tight">
                            Tidak Hadir <span class="opacity-90 text-xs">(Izin/Cuti/Alpa/Sakit)</span>
                        </h3>
                        <template x-if="loading">
                            <div class="mt-3 sm:mt-4 h-9 sm:h-10 w-20 rounded-md bg-white/30 animate-pulse"></div>
                        </template>
                        <p x-show="!loading" class="text-3xl sm:text-4xl font-bold mt-3 sm:mt-4 tabular-nums"
                           x-text="stats.absen" aria-live="polite" aria-atomic="true"></p>
                    </div>
                    <div class="shrink-0 text-white/90 relative">
                        <img src="{{ asset('images/time_icon.svg') }}" class="h-8 w-8" alt="Tidak Hadir">
                    </div>
                </div>

                {{-- 3) Invalid --}}
                <div
                    class="group relative overflow-hidden rounded-2xl shadow-sm ring-1 ring-black/5 bg-gradient-to-br from-rose-500 to-rose-600 text-white p-4 sm:p-6 flex items-center justify-between gap-4 min-w-0">
                    <div class="absolute -right-12 -bottom-12 h-44 w-44 rounded-full bg-white/20 blur-2xl transition-all duration-500 group-hover:scale-110"></div>
                    <div class="min-w-0 relative">
                        <h3 class="text-base sm:text-lg font-semibold leading-tight">Invalid</h3>
                        <template x-if="loading">
                            <div class="mt-3 sm:mt-4 h-9 sm:h-10 w-24 rounded-md bg-white/30 animate-pulse"></div>
                        </template>
                        <p x-show="!loading" class="text-3xl sm:text-4xl font-bold mt-3 sm:mt-4 tabular-nums"
                           x-text="stats.invalid" aria-live="polite" aria-atomic="true"></p>
                    </div>
                    <div class="shrink-0 text-white/90 relative">
                        <img src="{{ asset('images/user_not_icon.svg') }}" class="h-8 w-8" alt="Invalid">
                    </div>
                </div>

                {{-- 4) Izin Pending --}}
                <div
                    class="group relative overflow-hidden rounded-2xl shadow-sm ring-1 ring-black/5 bg-gradient-to-br from-yellow-500 to-amber-500 text-white p-4 sm:p-6 flex items-center justify-between gap-4 min-w-0">
                    <div class="absolute -left-10 -top-10 h-40 w-40 rounded-full bg-white/20 blur-2xl transition-all duration-500 group-hover:scale-110"></div>
                    <div class="min-w-0 relative">
                        <h3 class="text-base sm:text-lg font-semibold leading-tight">Izin Pending</h3>
                        <template x-if="loading">
                            <div class="mt-3 sm:mt-4 h-9 sm:h-10 w-24 rounded-md bg-white/30 animate-pulse"></div>
                        </template>
                        <p x-show="!loading" class="text-3xl sm:text-4xl font-bold mt-3 sm:mt-4 tabular-nums"
                           x-text="stats.izin_pending" aria-live="polite" aria-atomic="true"></p>
                    </div>
                    <div class="shrink-0 text-white/90 relative">
                        <img src="{{ asset('images/document_pending_icon.svg') }}" class="h-8 w-8" alt="Izin Pending">
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Alpine component --}}
    <script>
        function dashboardRealtime() {
            return {
                // ==== State statistik ====
                stats: { hadir: 0, absen: 0, invalid: 0, izin_pending: 0 },

                // ==== Meta polling ====
                lastDate: null,
                lastUpdatedLabel: '—',
                loading: true,
                timer: null,
                intervalMs: 10000,

                // ==== Jam realtime ====
                now: { day: '-', date: '-', time: '-' },
                nowTimer: null,

                init() {
                    this.fetchOnce();
                    this.startPolling();
                    this.startClock();
                    document.addEventListener('visibilitychange', () => {
                        if (!document.hidden) this.fetchOnce();
                    });
                },

                startPolling() {
                    if (this.timer) clearInterval(this.timer);
                    this.timer = setInterval(() => this.fetchOnce(), this.intervalMs);
                },

                async fetchOnce() {
                    try {
                        const url = "{{ route('admin.dashboard.stats') }}";
                        const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                        if (!res.ok) throw new Error('Network error');
                        const data = await res.json();

                        this.stats.hadir        = data.hadir ?? 0;
                        this.stats.absen        = data.absen ?? 0;
                        this.stats.invalid      = data.invalid ?? 0;
                        this.stats.izin_pending = data.izin_pending ?? 0;
                        this.lastDate           = data.date ?? null;

                        const now = new Date();
                        this.lastUpdatedLabel = new Intl.DateTimeFormat('id-ID', {
                            hour: '2-digit', minute: '2-digit', second: '2-digit'
                        }).format(now);
                    } catch (e) {
                        // optional: console.error(e);
                    } finally {
                        this.loading = false;
                    }
                },

                startClock() {
                    this.tickNow();
                    if (this.nowTimer) clearInterval(this.nowTimer);
                    this.nowTimer = setInterval(() => this.tickNow(), 1000);
                },

                tickNow() {
                    const now = new Date();
                    const day = new Intl.DateTimeFormat('id-ID', { weekday: 'long' }).format(now);
                    const date = new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }).format(now);
                    const time = new Intl.DateTimeFormat('id-ID', { hour: '2-digit', minute: '2-digit', second: '2-digit' }).format(now);

                    this.now.day = day.charAt(0).toUpperCase() + day.slice(1);
                    this.now.date = date;
                    this.now.time = time;
                }
            }
        }
    </script>
@endsection
