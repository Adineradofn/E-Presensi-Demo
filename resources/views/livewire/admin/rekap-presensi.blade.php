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
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 pr-12 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                        placeholder="Cari NIK / Nama / Jabatan / Divisi" wire:keydown.escape="$set('q','')" />
                    <button type="button"
                        class="absolute right-1.5 top-1/2 -translate-y-1/2 inline-flex items-center justify-center h-8 w-8 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                        title="Bersihkan" wire:click="$set('q','')">
                        <img src="{{ asset('images/search_icon.svg') }}" class="h-5 w-5" alt="">
                    </button>
                    <div wire:loading.delay.short wire:target="q"
                        class="absolute right-10 top-1/2 -translate-y-1/2 text-xs text-gray-500">
                        Memuat…
                    </div>
                </div>

                {{-- Tombol Export --}}
                <a
                    href="{{ route('admin.rekap-presensi.export', ['mode' => $mode, 'month' => $month, 'year' => $year, 'q' => $q]) }}"
                    class="inline-flex items-center justify-center rounded-lg bg-emerald-600 text-white px-3 py-2 text-sm font-medium hover:bg-emerald-700 transition"
                    title="Export ke Excel">
                    Export Excel
                </a>
            </div>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto bg-white rounded-xl border border-gray-200 shadow-sm mt-2">
            <table class="min-w-full whitespace-nowrap">
                <thead class="bg-gray-50 text-gray-700">
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
                        <tr>
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
        <div class="mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-600">
                Halaman <span class="font-medium">{{ $items->currentPage() }}</span> dari
                <span class="font-medium">{{ $items->lastPage() }}</span>
            </div>
            <div class="flex items-center gap-4">
                <div class="relative">
                    <label class="sr-only" for="perPageBottom">Baris per halaman</label>
                    <select id="perPageBottom"
                        class="appearance-none h-10 rounded-xl border border-gray-300 bg-white px-3 pr-10 text-sm text-gray-900 shadow-sm transition hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500"
                        wire:model.live="perPage" title="Baris per halaman" aria-label="Baris per halaman">
                        @foreach ($this->perPageOptions as $opt)
                            <option value="{{ $opt }}">{{ $opt }} / halaman</option>
                        @endforeach
                    </select>
                    <span class="pointer-events-none absolute right-1.5 top-1/2 -translate-y-1/2 inline-flex h-7 w-7 items-center justify-center rounded-md text-gray-500">
                         <img src="{{ asset('images/dropdown_icon.svg') }}" class="h-5 w-5" alt="">
                    </span>
                </div>
                <div class="shrink-0">{{ $items->onEachSide(1)->links() }}</div>
            </div>
        </div>
    </div>
</div>
