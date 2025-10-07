<div x-data>
    @php
        use Illuminate\Support\Facades\Storage;
        $mode = $mode ?? 'hari';
    @endphp

    <div class="p-4">
        <h2 class="text-xl sm:text-2xl font-bold mb-4">Data Presensi</h2>

        @if (session('success'))
            <div class="mb-3 p-3 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">
                {{ session('success') }}
            </div>
        @endif
        @if (session('error'))
            <div class="mb-3 p-3 rounded bg-red-50 text-red-700 border border-red-200">
                {{ session('error') }}
            </div>
        @endif

        {{-- Filter --}}
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="inline-flex w-fit max-w-full rounded-lg border border-gray-200 overflow-hidden whitespace-nowrap" role="tablist" aria-label="Filter rentang">
                <button type="button" role="tab" aria-selected="{{ $mode === 'hari' ? 'true' : 'false' }}"
                    wire:click="setMode('hari')"
                    class="px-4 py-2 text-sm font-medium {{ $mode === 'hari' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                    Hari
                </button>
                <button type="button" role="tab" aria-selected="{{ $mode === 'bulan' ? 'true' : 'false' }}"
                    wire:click="setMode('bulan')"
                    class="px-4 py-2 text-sm font-medium border-l border-gray-200 {{ $mode === 'bulan' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                    Bulan
                </button>
                <button type="button" role="tab" aria-selected="{{ $mode === 'tahun' ? 'true' : 'false' }}"
                    wire:click="setMode('tahun')"
                    class="px-4 py-2 text-sm font-medium border-l border-gray-200 {{ $mode === 'tahun' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                    Tahun
                </button>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                @if ($mode === 'hari')
                    <input type="date" wire:model.live="date"
                        class="w-full sm:w-auto rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                @elseif ($mode === 'bulan')
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

                <div class="flex flex-col sm:flex-row gap-2 sm:items-center sm:justify-end sm:w-auto">
                    <div class="relative sm:w-80">
                        <input type="text" wire:model.live.debounce.600ms="q"
                            class="w-full rounded-lg border border-gray-300 px-4 py-2 pr-12 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                            placeholder="Cari Presensi" wire:keydown.escape="$set('q','')" aria-label="Pencarian presensi" />
                        <button type="button"
                            class="absolute right-1.5 top-1/2 -translate-y-1/2 inline-flex items-center justify-center h-8 w-8 rounded-md hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-emerald-500"
                            title="Bersihkan" wire:click="$set('q','')">
                            <img src="{{ asset('images/search_icon.svg') }}" class="h-5 w-5" alt="search">
                        </button>
                        <div wire:loading.delay.short wire:target="q"
                            class="absolute right-10 top-1/2 -translate-y-1/2 text-xs text-gray-500">
                            Memuat…
                        </div>
                    </div>
                </div>

                {{-- Export data presensi (biarkan jika Anda pakai) --}}
                @if (Route::has('admin.data.presensi.export'))
                    <a href="{{ route(
                        'admin.data.presensi.export',
                        array_filter([
                            'mode' => $mode,
                            'date' => $mode === 'hari' ? $this->date ?? $current_date : null,
                            'month'=> $mode === 'bulan'? $this->month ?? $current_month : null,
                            'year' => $mode === 'tahun'? $this->year ?? $current_year : null,
                            'q'    => $this->q ?: null,
                        ]),
                    ) }}"
                        class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-lg border border-emerald-600 text-emerald-700 px-3 py-2 hover:bg-emerald-50"
                        title="Export CSV sesuai filter saat ini">
                        <img src="{{ asset('images/export_icon.svg') }}" class="h-5 w-5" alt="export">
                        Export
                    </a>
                @endif
            </div>
        </div>

        {{-- Info jumlah --}}
        <div class="mt-3 text-sm text-gray-600">
            @php
                $from = $items->firstItem() ?? 0;
                $to = $items->lastItem() ?? 0;
                $total = $items->total();
            @endphp
            Menampilkan <span class="font-medium">{{ $from }}</span>–<span class="font-medium">{{ $to }}</span>
            dari <span class="font-medium">{{ $total }}</span> data
        </div>

        {{-- Tabel Desktop --}}
        <div class="rounded-xl border border-gray-200 shadow-sm overflow-hidden hidden sm:block">
            <div class="overflow-x-auto">
                <table class="min-w-full whitespace-nowrap">
                    <thead class="bg-gray-50 text-left text-gray-700">
                        <tr>
                            <th class="px-4 py-3 text-sm font-semibold">NIK</th>
                            <th class="px-4 py-3 text-sm font-semibold">Nama</th>
                            <th class="px-4 py-3 text-sm font-semibold">Divisi</th>
                            <th class="px-4 py-3 text-sm font-semibold">Jabatan</th>
                            <th class="px-4 py-3 text-sm font-semibold">Jam Masuk</th>
                            <th class="px-4 py-3 text-sm font-semibold">Jam Pulang</th>
                            <th class="px-4 py-3 text-sm font-semibold">Tanggal</th>
                            <th class="px-4 py-3 text-sm font-semibold">Status Presensi</th>
                            <th class="px-4 py-3 text-sm font-semibold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($items as $row)
                            @php
                                $nama = $row->karyawan->nama ?? '-';
                                $divisi = $row->karyawan->divisi ?? '-';
                                $jabatan = $row->karyawan->jabatan ?? '-';
                                $nik = $row->karyawan->nik ?? '-';
                                $jm = $row->jam_masuk ? $row->jam_masuk->format('H:i') : '-';
                                $jp = $row->jam_pulang ? $row->jam_pulang->format('H:i') : '-';
                                $tgl = $row->tanggal ? $row->tanggal->format('Y-m-d') : '-';
                                $status = $row->status_presensi;

                                $badgeClass = match ($status) {
                                    'hadir' => 'bg-emerald-100 text-emerald-700',
                                    'izin'  => 'bg-sky-100 text-sky-700',
                                    'sakit' => 'bg-violet-100 text-violet-700',
                                    'alpa'  => 'bg-amber-100 text-amber-700',
                                    'invalid' => 'bg-red-100 text-red-700',
                                    'cuti'  => 'bg-gray-100 text-gray-700',
                                    default => 'bg-gray-100 text-gray-700',
                                };

                                $hasMasuk = filled($row->foto_masuk);
                                $hasPulang= filled($row->foto_pulang);
                                $hasFoto  = $hasMasuk || $hasPulang;

                                $urlMasuk = $hasMasuk
                                    ? route('admin.data.presensi.foto.show', ['presensi' => $row->id, 'jenis' => 'masuk'])
                                    : '';
                                $urlPulang = $hasPulang
                                    ? route('admin.data.presensi.foto.show', ['presensi' => $row->id, 'jenis' => 'pulang'])
                                    : '';
                            @endphp
                            <tr class="hover:bg-gray-50" wire:key="row-d-{{ $row->id }}">
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $nik }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $nama }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $divisi }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $jabatan }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $jm }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $jp }}</td>
                                <td class="px-4 py-3 text-sm text-gray-700">{{ $tgl }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="px-2 py-1 rounded-full text-xs font-medium {{ $badgeClass }}">
                                        {{ ucfirst($status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    <div class="flex items-center gap-3 w-full flex-nowrap">
                                        <button type="button"
                                            class="group inline-flex items-center justify-center rounded-full p-2 transition hover:bg-gray-100 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2 disabled:opacity-60"
                                            title="{{ $hasFoto ? 'Lihat Foto Absen' : 'Foto tidak tersedia' }}"
                                            aria-label="{{ $hasFoto ? 'Lihat Foto Absen' : 'Foto tidak tersedia' }}"
                                            data-url-masuk="{{ $urlMasuk }}" data-url-pulang="{{ $urlPulang }}"
                                            data-has-masuk="{{ $hasMasuk ? '1' : '0' }}"
                                            data-has-pulang="{{ $hasPulang ? '1' : '0' }}"
                                            onclick="openPhotoModal(this)" {{ !$hasFoto ? 'disabled' : '' }}>
                                            <img src="{{ asset($hasFoto ? 'images/camera_icon.svg' : 'images/hide_camera_icon.svg') }}"
                                                alt="{{ $hasFoto ? 'Foto tersedia' : 'Tidak ada foto' }}" class="size-5" />
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-6 text-center text-sm text-gray-500">
                                    Belum ada data presensi pada rentang ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile Card (hapus tombol edit) --}}
        <div class="sm:hidden space-y-3 mt-2">
            @forelse($items as $row)
                @php
                    $nama = $row->karyawan->nama ?? '-';
                    $divisi = $row->karyawan->divisi ?? '-';
                    $jabatan = $row->karyawan->jabatan ?? '-';
                    $nik = $row->karyawan->nik ?? '-';
                    $jm = $row->jam_masuk ? $row->jam_masuk->format('H:i') : '-';
                    $jp = $row->jam_pulang ? $row->jam_pulang->format('H:i') : '-';
                    $tgl = $row->tanggal ? $row->tanggal->format('Y-m-d') : '-';
                    $status = $row->status_presensi;

                    $badgeClass = match ($status) {
                        'hadir' => 'bg-emerald-100 text-emerald-700',
                        'izin'  => 'bg-sky-100 text-sky-700',
                        'sakit' => 'bg-violet-100 text-violet-700',
                        'alpa'  => 'bg-amber-100 text-amber-700',
                        'invalid' => 'bg-red-100 text-red-700',
                        'cuti'  => 'bg-gray-100 text-gray-700',
                        default => 'bg-gray-100 text-gray-700',
                    };

                    $hasMasuk = filled($row->foto_masuk);
                    $hasPulang= filled($row->foto_pulang);
                    $hasFoto  = $hasMasuk || $hasPulang;

                    $urlMasuk = $hasMasuk
                        ? route('admin.data.presensi.foto.show', ['presensi' => $row->id, 'jenis' => 'masuk'])
                        : '';
                    $urlPulang = $hasPulang
                        ? route('admin.data.presensi.foto.show', ['presensi' => $row->id, 'jenis' => 'pulang'])
                        : '';
                @endphp

                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4" wire:key="row-m-{{ $row->id }}">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <div class="text-base font-semibold text-gray-900 truncate">{{ $nama }}</div>
                            <div class="text-[13px] text-gray-600 truncate">{{ $jabatan }} • {{ $divisi }}</div>
                            <div class="text-[12px] text-gray-500">NIK: <span class="font-medium text-gray-700">{{ $nik }}</span></div>
                        </div>
                        <div class="shrink-0">
                            <span class="inline-block px-2 py-1 rounded-full text-[11px] font-medium {{ $badgeClass }}">
                                {{ ucfirst($status) }}
                            </span>
                        </div>
                    </div>

                    <div class="mt-3 grid grid-cols-3 gap-2 text-[12px]">
                        <div class="rounded-lg bg-gray-50 p-2"><div class="text-gray-500">Masuk</div><div class="font-medium text-gray-900">{{ $jm }}</div></div>
                        <div class="rounded-lg bg-gray-50 p-2"><div class="text-gray-500">Pulang</div><div class="font-medium text-gray-900">{{ $jp }}</div></div>
                        <div class="rounded-lg bg-gray-50 p-2"><div class="text-gray-500">Tanggal</div><div class="font-medium text-gray-900">{{ $tgl }}</div></div>
                    </div>

                    <div class="mt-4 flex items-center gap-2">
                        <button type="button"
                            class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg border hover:bg-gray-50 disabled:opacity-50"
                            title="{{ $hasFoto ? 'Lihat Foto Absen' : 'Foto tidak tersedia' }}"
                            aria-label="{{ $hasFoto ? 'Lihat Foto Absen' : 'Foto tidak tersedia' }}"
                            data-url-masuk="{{ $urlMasuk }}" data-url-pulang="{{ $urlPulang }}"
                            data-has-masuk="{{ $hasMasuk ? '1' : '0' }}"
                            data-has-pulang="{{ $hasPulang ? '1' : '0' }}" onclick="openPhotoModal(this)"
                            {{ !$hasFoto ? 'disabled' : '' }}>
                            <img src="{{ asset($hasFoto ? 'images/camera_icon.svg' : 'images/hide_camera_icon.svg') }}" alt="" class="size-5" />
                            <span class="text-xs text-gray-700">Foto</span>
                        </button>
                    </div>
                </div>
            @empty
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 text-center text-sm text-gray-500">
                    Belum ada data presensi pada rentang ini.
                </div>
            @endforelse
        </div>

        {{-- Pagination --}}
        <div class="mt-4">
            <div class="hidden sm:flex items-center justify-between">
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

            <div class="sm:hidden space-y-3">
                <div class="flex justify-center">{{ $items->onEachSide(0)->links() }}</div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1" for="perPageBottomSm">Baris per halaman</label>
                    <div class="relative">
                        <select id="perPageBottomSm"
                            class="appearance-none w-full h-11 rounded-xl border border-gray-300 bg-white px-3 pr-10 text-sm text-gray-900 shadow-sm transition hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500"
                            wire:model.live="perPage" title="Baris per halaman" aria-label="Baris per halaman">
                            @foreach ($this->perPageOptions as $opt)
                                <option value="{{ $opt }}">{{ $opt }} / halaman</option>
                            @endforeach
                        </select>
                        <span class="pointer-events-none absolute right-1.5 top-1/2 -translate-y-1/2 inline-flex h-8 w-8 items-center justify-center rounded-md text-gray-500">
                              <img src="{{ asset('images/dropdown_icon.svg') }}" class="h-5 w-5" alt="">
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Modal foto tetap --}}
    @include('livewire.admin.modals.foto_presensi')
</div>
