@php use Illuminate\Support\Facades\Storage; @endphp

<div class="p-4" x-data>
    <h2 class="text-xl sm:text-2xl font-bold mb-4">Pengajuan Izin</h2>

    {{-- Filter bar (mode + tanggal + search live) --}}
    <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div class="inline-flex w-fit max-w-full self-start sm:self-auto rounded-lg border border-gray-200 overflow-hidden whitespace-nowrap">
            <a href="{{ request()->fullUrlWithQuery(['mode' => 'hari']) }}" wire:click.prevent="setMode('hari')"
               class="px-4 py-2 text-sm font-medium {{ $mode === 'hari' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                Hari
            </a>
            <a href="{{ request()->fullUrlWithQuery(['mode' => 'bulan']) }}" wire:click.prevent="setMode('bulan')"
               class="px-4 py-2 text-sm font-medium border-l border-gray-200 {{ $mode === 'bulan' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                Bulan
            </a>
            <a href="{{ request()->fullUrlWithQuery(['mode' => 'tahun']) }}" wire:click.prevent="setMode('tahun')"
               class="px-4 py-2 text-sm font-medium border-l border-gray-200 {{ $mode === 'tahun' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                Tahun
            </a>
        </div>

        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
            @if ($mode === 'hari')
                <input type="date" wire:model.live="date"
                       class="rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 w-full sm:w-auto">
            @elseif ($mode === 'bulan')
                <input type="month" wire:model.live="month"
                       class="rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 w-full sm:w-auto">
            @else
                <select wire:model.live="year"
                        class="rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 w-full sm:w-auto">
                    @for ($y = now()->year; $y >= now()->year - 5; $y--)
                        <option value="{{ $y }}">{{ $y }}</option>
                    @endfor
                </select>
            @endif

            <div class="flex flex-col sm:flex-row gap-2 sm:items-center sm:justify-end sm:w-auto">
                <div class="relative sm:w-80">
                    <input type="text" wire:model.live.debounce.600ms="q"
                           class="w-full rounded-lg border border-gray-300 px-4 py-2 pr-12 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                           placeholder="Cari Pengajuan Izin" wire:keydown.escape="$set('q','')" aria-label="Pencarian pengajuan izin" />
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
        </div>
    </div>

    <div class="mt-2 text-sm text-gray-600">
        Total data: <span class="font-medium">{{ $items->total() }}</span>
    </div>

    {{-- ========================= DESKTOP TABLE ========================= --}}
    <div class="overflow-x-auto bg-white rounded-xl border border-gray-200 shadow-sm mt-2 hidden sm:block">
        {{-- table-fixed: tetap tanpa scroll horizontal --}}
        <table class="min-w-full table-fixed">
            {{-- SEMUA HEADER RATA TENGAH --}}
            <thead class="bg-gray-50 text-gray-700">
                <tr>
                    <th class="px-4 py-3 text-sm font-semibold text-center">NIK</th>
                    <th class="px-4 py-3 text-sm font-semibold text-center w-24">
                        <span class="block leading-tight">Tanggal</span>
                        <span class="block leading-tight">Pengajuan</span>
                    </th>
                    <th class="px-4 py-3 text-sm font-semibold text-center w-44">Nama</th>
                    <th class="px-4 py-3 text-sm font-semibold text-center">Jabatan</th>
                    <th class="px-4 py-3 text-sm font-semibold text-center">Divisi</th>
                    <th class="px-4 py-3 text-sm font-semibold text-center">Jenis</th>
                    <th class="px-4 py-3 text-sm font-semibold text-center w-24">
                        <span class="block leading-tight">Tanggal</span>
                        <span class="block leading-tight">Mulai</span>
                    </th>
                    <th class="px-4 py-3 text-sm font-semibold text-center w-24">
                        <span class="block leading-tight">Tanggal</span>
                        <span class="block leading-tight">Selesai</span>
                    </th>
                    <th class="px-4 py-3 text-sm font-semibold text-center w-56">Alasan</th>
                    <th class="px-4 py-3 text-sm font-semibold text-center">Status</th>
                    <th class="px-4 py-3 text-sm font-semibold text-center">Aksi</th>
                </tr>
            </thead>

            <tbody class="divide-y divide-gray-100">
                @forelse ($items as $row)
                    @php
                        $nik      = $row->karyawan->nik ?? '-';
                        $nama     = $row->karyawan->nama ?? '-';
                        $jabatan  = $row->karyawan->jabatan ?? '-';
                        $divisi   = $row->karyawan->divisi ?? '-';

                        $pengajuan = $row->tanggal_pengajuan ? $row->tanggal_pengajuan->format('Y-m-d') : '-';
                        $mulai     = $row->tanggal_mulai ? $row->tanggal_mulai->format('Y-m-d') : '-';
                        $akhir     = $row->tanggal_selesai ? $row->tanggal_selesai->format('Y-m-d') : '-';
                        $alasan    = $row->alasan ?: '-';

                        $jenis   = $row->jenis;   // izin|sakit|cuti|izin terlambat|tugas
                        $status  = $row->status;  // pending|disetujui|ditolak

                        $hasFile = $row->bukti_path && Storage::disk('local')->exists($row->bukti_path);
                        $buktiUrl = $hasFile ? route('admin.izin.bukti.show', $row->id) : '';

                        $jenisClass = match ($jenis) {
                            'sakit' => 'bg-violet-100 text-violet-700',
                            'cuti'  => 'bg-gray-100 text-gray-700',
                            'tugas' => 'bg-emerald-100 text-emerald-700',
                            default => 'bg-sky-100 text-sky-700',
                        };
                        $statusClass = match ($status) {
                            'disetujui' => 'bg-emerald-100 text-emerald-700',
                            'ditolak'   => 'bg-red-100 text-red-700',
                            default     => 'bg-gray-100 text-gray-700',
                        };

                        $canEdit = in_array($jenis, ['tugas', 'izin terlambat'], true);
                    @endphp

                    <tr class="hover:bg-gray-50">
                        {{-- Rata tengah & single-line --}}
                        <td class="px-4 py-3 text-sm text-gray-700 text-center whitespace-nowrap truncate max-w-[6rem]">{{ $nik }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 text-center whitespace-nowrap">{{ $pengajuan }}</td>

                        {{-- NAMA: rata kiri & boleh turun --}}
                        <td class="px-4 py-3 text-sm font-medium text-gray-900 align-top text-left whitespace-normal break-words">
                            {{ $nama }}
                        </td>

                        {{-- Rata tengah & single-line --}}
                        <td class="px-4 py-3 text-sm text-gray-700 text-center whitespace-nowrap truncate max-w-[12rem]" title="{{ $jabatan }}">{{ $jabatan }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 text-center whitespace-nowrap truncate max-w-[10rem]" title="{{ $divisi }}">{{ $divisi }}</td>

                        <td class="px-4 py-3 text-sm text-center whitespace-nowrap">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $jenisClass }} whitespace-nowrap">{{ ucfirst($jenis) }}</span>
                        </td>

                        <td class="px-4 py-3 text-sm text-gray-700 text-center whitespace-nowrap">{{ $mulai }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 text-center whitespace-nowrap">{{ $akhir }}</td>

                        {{-- ALASAN: rata kiri & boleh turun --}}
                        <td class="px-4 py-3 text-sm text-gray-700 align-top text-left whitespace-normal break-words">
                            {{ $alasan }}
                        </td>

                        <td class="px-4 py-3 text-sm text-center whitespace-nowrap">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusClass }}">{{ ucfirst($status) }}</span>
                        </td>

                        {{-- AKSI: kontainer boleh wrap ke bawah jika sempit; item tetap single-line --}}
                        <td class="px-4 py-3 text-sm text-center">
                            <div class="flex flex-wrap items-center justify-center gap-2">
                                {{-- Bukti --}}
                                <button type="button"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border hover:bg-gray-50 disabled:opacity-50 overflow-hidden whitespace-nowrap"
                                        title="Lihat bukti" onclick="openBukti(this)" data-url="{{ $buktiUrl }}"
                                        {{ $hasFile ? '' : 'disabled' }}>
                                    <img src="{{ asset('images/letter_icon.svg') }}" class="h-5 w-5 shrink-0" alt="bukti surat">
                                    <span class="text-xs text-gray-700 truncate">Bukti</span>
                                </button>

                                {{-- Edit Status (muncul hanya jika boleh) --}}
                                @if ($canEdit)
                                    <button type="button"
                                            class="px-3 py-1.5 rounded-lg border border-emerald-600 text-emerald-700 hover:bg-emerald-50 text-sm"
                                            title="Ubah Status"
                                            aria-label="Ubah Status {{ $nama }} {{ $mulai }}-{{ $akhir }}"
                                            @click="
                                                $store.izinModal.openWith({
                                                    targetId: 'edit_status_izin',
                                                    currentStatus: @js($status),
                                                    nama: @js($nama === '-' ? '' : $nama),
                                                    mulai: @js($mulai === '-' ? '' : $mulai),
                                                    akhir: @js($akhir === '-' ? '' : $akhir)
                                                });
                                                $wire.set('selectedIzinId', {{ $row->id }});
                                                $wire.set('initialStatus', @js($status));
                                                $wire.set('selectedStatus', @js($status));
                                                $wire.set('selectedNama', @js($nama === '-' ? '' : $nama));
                                                $wire.set('selectedMulai', @js($mulai === '-' ? '' : $mulai));
                                                $wire.set('selectedAkhir', @js($akhir === '-' ? '' : $akhir));
                                            ">
                                        Edit
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="px-4 py-6 text-center text-sm text-gray-500">
                            Belum ada pengajuan pada rentang ini.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- ========================= MOBILE CARD LIST ========================= --}}
    <div class="sm:hidden mt-2 space-y-3">
        @forelse ($items as $row)
            @php
                $nik      = $row->karyawan->nik ?? '-';
                $nama     = $row->karyawan->nama ?? '-';
                $jabatan  = $row->karyawan->jabatan ?? '-';
                $divisi   = $row->karyawan->divisi ?? '-';

                $pengajuan = $row->tanggal_pengajuan ? $row->tanggal_pengajuan->format('Y-m-d') : '-';
                $mulai     = $row->tanggal_mulai ? $row->tanggal_mulai->format('Y-m-d') : '-';
                $akhir     = $row->tanggal_selesai ? $row->tanggal_selesai->format('Y-m-d') : '-';
                $alasan    = $row->alasan ?: '-';

                $jenis   = $row->jenis;
                $status  = $row->status;

                $hasFile = $row->bukti_path && Storage::disk('local')->exists($row->bukti_path);
                $buktiUrl = $hasFile ? route('admin.izin.bukti.show', $row->id) : '';

                $jenisClass = match ($jenis) {
                    'sakit' => 'bg-violet-100 text-violet-700',
                    'cuti'  => 'bg-gray-100 text-gray-700',
                    'tugas' => 'bg-emerald-100 text-emerald-700',
                    default => 'bg-sky-100 text-sky-700',
                };
                $statusClass = match ($status) {
                    'disetujui' => 'bg-emerald-100 text-emerald-700',
                    'ditolak'   => 'bg-red-100 text-red-700',
                    default     => 'bg-gray-100 text-gray-700',
                };

                $canEdit = in_array($jenis, ['tugas', 'izin terlambat'], true);
            @endphp

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-4">
                {{-- Header --}}
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="text-base font-semibold text-gray-900 truncate">{{ $nama }}</div>
                        <div class="text-[13px] text-gray-600 truncate">{{ $jabatan }} • {{ $divisi }}</div>
                        <div class="text-[12px] text-gray-500">NIK: <span class="font-medium text-gray-700">{{ $nik }}</span></div>
                    </div>
                    <div class="shrink-0 text-right">
                        <div>
                            <span class="inline-block px-2 py-1 rounded-full text-[11px] font-medium {{ $jenisClass }} whitespace-nowrap">{{ ucfirst($jenis) }}</span>
                        </div>
                        <div class="mt-1">
                            <span class="inline-block px-2 py-1 rounded-full text-[11px] font-medium {{ $statusClass }}">{{ ucfirst($status) }}</span>
                        </div>
                    </div>
                </div>

                {{-- Dates --}}
                <div class="mt-3 grid grid-cols-3 gap-2 text-[12px]">
                    <div class="rounded-lg bg-gray-50 p-2">
                        <div class="text-gray-500 leading-tight">Pengajuan</div>
                        <div class="font-medium text-gray-900 leading-tight">{{ $pengajuan }}</div>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-2">
                        <div class="text-gray-500 leading-tight">Mulai</div>
                        <div class="font-medium text-gray-900 leading-tight">{{ $mulai }}</div>
                    </div>
                    <div class="rounded-lg bg-gray-50 p-2">
                        <div class="text-gray-500 leading-tight">Selesai</div>
                        <div class="font-medium text-gray-900 leading-tight">{{ $akhir }}</div>
                    </div>
                </div>

                {{-- Alasan --}}
                <div class="mt-3">
                    <div class="text-[12px] text-gray-500">Alasan</div>
                    <p class="text-sm text-gray-800 break-words">{{ $alasan }}</p>
                </div>

                {{-- Actions --}}
                <div class="mt-4 flex flex-wrap items-center gap-2 justify-center">
                    <button type="button"
                            class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg border hover:bg-gray-50 disabled:opacity-50 overflow-hidden whitespace-nowrap"
                            title="Lihat bukti" onclick="openBukti(this)" data-url="{{ $buktiUrl }}"
                            {{ $hasFile ? '' : 'disabled' }}>
                        <img src="{{ asset('images/letter_icon.svg') }}" class="h-5 w-5 shrink-0" alt="bukti surat">
                        <span class="text-xs text-gray-700 truncate">Bukti</span>
                    </button>

                    @if ($canEdit)
                        <button type="button"
                               class="px-3 py-1.5 rounded-lg border border-emerald-600 text-emerald-700 hover:bg-emerald-50 text-sm"
                                title="Ubah Status"
                                aria-label="Ubah Status {{ $nama }} {{ $mulai }}-{{ $akhir }}"
                                @click="
                                    $store.izinModal.openWith({
                                        targetId: 'edit_status_izin',
                                        currentStatus: @js($status),
                                        nama: @js($nama === '-' ? '' : $nama),
                                        mulai: @js($mulai === '-' ? '' : $mulai),
                                        akhir: @js($akhir === '-' ? '' : $akhir)
                                    });
                                    $wire.set('selectedIzinId', {{ $row->id }});
                                    $wire.set('initialStatus', @js($status));
                                    $wire.set('selectedStatus', @js($status));
                                    $wire.set('selectedNama', @js($nama === '-' ? '' : $nama));
                                    $wire.set('selectedMulai', @js($mulai === '-' ? '' : $mulai));
                                    $wire.set('selectedAkhir', @js($akhir === '-' ? '' : $akhir));
                                ">
                            Edit
                        </button>
                    @endif
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6 text-center text-sm text-gray-500">
                Belum ada pengajuan pada rentang ini.
            </div>
        @endforelse
    </div>

    <div class="mt-3">
        {{ $items->onEachSide(1)->links() }}
    </div>

    {{-- Modal --}}
    @include('livewire.admin.modals.bukti_pengajuan_izin')
    @include('livewire.admin.modals.edit_status_pengajuan_izin')
</div>
