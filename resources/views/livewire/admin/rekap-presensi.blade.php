<div x-data>
    <div class="p-4">
        <h2 class="text-xl sm:text-2xl font-bold mb-4">Rekap Presensi</h2>

        {{-- Filter --}}
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="inline-flex w-fit max-w-full rounded-lg border border-gray-200 overflow-hidden whitespace-nowrap" role="tablist">
                <button type="button" role="tab" aria-selected="{{ $mode === 'bulan' ? 'true' : 'false' }}"
                        wire:click="setMode('bulan')"
                        class="px-4 py-2 text-sm font-medium {{ $mode === 'bulan' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                    Bulan
                </button>
                <button type="button" role="tab" aria-selected="{{ $mode === 'tahun' ? 'true' : 'false' }}"
                        wire:click="setMode('tahun')"
                        class="px-4 py-2 text-sm font-medium border-l border-gray-200 {{ $mode === 'tahun' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                    Tahun
                </button>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                @if ($mode === 'bulan')
                    <input type="month" wire:model.live="month"
                           class="w-full sm:w-auto rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                @else
                    <select wire:model.live="year"
                            class="w-full sm:w-auto rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        @for ($y = now()->year; $y >= now()->year - 5; $y--)
                            <option value="{{ $y }}">{{ $y }}</option>
                        @endfor
                    </select>
                @endif

                <div class="relative sm:w-80">
                    <input type="text" wire:model.live.debounce.600ms="q"
                           class="w-full rounded-lg border border-gray-300 px-3 py-2 pr-10 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                           placeholder="Cari NIK / Nama / Jabatan / Divisi" wire:keydown.escape="$set('q','')" />
                    <button type="button"
                            class="absolute right-1.5 top-1/2 -translate-y-1/2 inline-flex items-center justify-center h-7 w-7 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            title="Bersihkan" aria-label="Bersihkan" wire:click="$set('q','')">
                        <span class="text-base leading-none">&times;</span>
                    </button>
                    <div wire:loading.delay.short wire:target="q"
                         class="absolute right-9 top-1/2 -translate-y-1/2 text-xs text-gray-500">
                        Memuat…
                    </div>
                </div>

                {{-- Tombol Export --}}
                <a href="{{ route('admin.rekap-presensi.export', ['mode' => $mode, 'month' => $month, 'year' => $year, 'q' => $q]) }}"
                   class="inline-flex items-center justify-center rounded-lg bg-emerald-600 text-white px-3 py-2 text-sm font-medium hover:bg-emerald-700 transition"
                   title="Export ke Excel">
                    Export Excel
                </a>
            </div>
        </div>

        {{-- ===================== MOBILE (CARD SIMPLE + HIGHLIGHT TOTAL) ===================== --}}
        <div class="grid gap-3 sm:hidden">
            @forelse ($items as $row)
                @php
                    $hadir   = (int) $row->hadir;
                    $alpa    = (int) $row->alpa;
                    $izin    = (int) $row->izin;
                    $sakit   = (int) $row->sakit;
                    $cuti    = (int) $row->cuti;
                    $invalid = (int) $row->invalid;
                    $totHadir = (int) $row->total_kehadiran;
                    $totAbsen = (int) $row->total_ketidakhadiran;
                @endphp

                <article class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                    {{-- Header ringkas --}}
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-gray-900 leading-tight">{{ $row->nama }}</h3>
                            <p class="text-xs text-gray-500 mt-0.5">
                                NIK: <span class="font-medium text-gray-700">{{ $row->nik }}</span>
                            </p>
                        </div>
                    </div>
                    <p class="mt-2 text-xs text-gray-600">{{ $row->jabatan }} • {{ $row->divisi }}</p>

                    {{-- Angka detail (tanpa progress/x-y hari) --}}
                    <dl class="mt-3 text-sm divide-y divide-gray-100">
                        <div class="flex items-center justify-between py-1.5">
                            <dt class="text-gray-600">Hadir</dt>
                            <dd class="font-semibold text-gray-900">{{ $hadir }}</dd>
                        </div>
                        <div class="flex items-center justify-between py-1.5">
                            <dt class="text-gray-600">Alpa</dt>
                            <dd class="font-semibold text-gray-900">{{ $alpa }}</dd>
                        </div>
                        <div class="flex items-center justify-between py-1.5">
                            <dt class="text-gray-600">Izin</dt>
                            <dd class="font-semibold text-gray-900">{{ $izin }}</dd>
                        </div>
                        <div class="flex items-center justify-between py-1.5">
                            <dt class="text-gray-600">Sakit</dt>
                            <dd class="font-semibold text-gray-900">{{ $sakit }}</dd>
                        </div>
                        <div class="flex items-center justify-between py-1.5">
                            <dt class="text-gray-600">Cuti</dt>
                            <dd class="font-semibold text-gray-900">{{ $cuti }}</dd>
                        </div>
                        <div class="flex items-center justify-between py-1.5">
                            <dt class="text-gray-600">Invalid</dt>
                            <dd class="font-semibold text-gray-900">{{ $invalid }}</dd>
                        </div>
                    </dl>

                    {{-- HIGHLIGHT TOTALS --}}
                    <div class="mt-3 rounded-xl bg-gray-50 p-3 ring-1 ring-inset ring-gray-200">
                        <div class="text-[11px] font-medium text-gray-600 mb-1.5 tracking-wide">TOTAL</div>
                        <div class="flex items-center justify-between py-1.5">
                            <span class="text-gray-700">Total Kehadiran</span>
                            <span class="font-bold text-emerald-700">{{ $totHadir }}</span>
                        </div>
                        <div class="flex items-center justify-between py-1.5">
                            <span class="text-gray-700">Total Ketidakhadiran</span>
                            <span class="font-bold text-rose-700">{{ $totAbsen }}</span>
                        </div>
                    </div>
                </article>
            @empty
                <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-6 text-center">
                    <p class="text-sm text-gray-600">Belum ada data rekap pada periode ini.</p>
                </div>
            @endforelse
        </div>

        {{-- ===================== DESKTOP (TABLE) ===================== --}}
        <div class="overflow-x-auto bg-white rounded-xl border border-gray-200 shadow-sm mt-2 hidden sm:block">
            <table class="min-w-full whitespace-nowrap">
                <thead class="bg-gray-200 text-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-sm font-semibold text-center">NIK</th>
                        <th class="px-4 py-3 text-sm font-semibold text-center">Nama</th>
                        <th class="px-4 py-3 text-sm font-semibold text-center">Jabatan</th>
                        <th class="px-4 py-3 text-sm font-semibold text-center">Divisi</th>
                        <th class="px-4 py-3 text-sm font-semibold text-center">Hadir</th>
                        <th class="px-4 py-3 text-sm font-semibold text-center">Alpa</th>
                        <th class="px-4 py-3 text-sm font-semibold text-center">Izin</th>
                        <th class="px-4 py-3 text-sm font-semibold text-center">Sakit</th>
                        <th class="px-4 py-3 text-sm font-semibold text-center">Cuti</th>
                        <th class="px-4 py-3 text-sm font-semibold text-center">Invalid</th>
                        <th class="px-4 py-3 text-sm font-semibold text-center">Total Kehadiran</th>
                        <th class="px-4 py-3 text-sm font-semibold text-center">Total Ketidakhadiran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($items as $row)
                        <tr class="hover:bg-gray-50/50">
                            <td class="px-4 py-3 text-sm text-gray-700 text-center">{{ $row->nik }}</td>
                            <td class="px-4 py-3 text-sm font-medium text-gray-900 text-center">{{ $row->nama }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 text-center">{{ $row->jabatan }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 text-center">{{ $row->divisi }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 text-center">{{ (int)$row->hadir }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 text-center">{{ (int)$row->alpa }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 text-center">{{ (int)$row->izin }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 text-center">{{ (int)$row->sakit }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 text-center">{{ (int)$row->cuti }}</td>
                            <td class="px-4 py-3 text-sm text-gray-900 text-center">{{ (int)$row->invalid }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-center">{{ (int)$row->total_kehadiran }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-center">{{ (int)$row->total_ketidakhadiran }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="px-4 py-6 text-center text-sm text-gray-500">
                                Belum ada data rekap pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination & perPage --}}
        <div class="mt-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="text-sm text-gray-600">
                Halaman <span class="font-medium">{{ $items->currentPage() }}</span> dari
                <span class="font-medium">{{ $items->lastPage() }}</span>
            </div>
            <div class="flex items-center gap-4">
                <div class="relative">
                    <label class="sr-only" for="perPageBottom">Baris per halaman</label>
                    <select id="perPageBottom"
                            class="appearance-none h-10 rounded-xl border border-gray-300 bg-white px-3 pr-8 text-sm text-gray-900 shadow-sm transition hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500"
                            wire:model.live="perPage" title="Baris per halaman" aria-label="Baris per halaman">
                        @foreach ($this->perPageOptions as $opt)
                            <option value="{{ $opt }}">{{ $opt }} / halaman</option>
                        @endforeach
                    </select>
                </div>
                <div class="shrink-0">
                    {{ $items->onEachSide(1)->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
