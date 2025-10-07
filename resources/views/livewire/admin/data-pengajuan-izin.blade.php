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
        <table class="min-w-full whitespace-nowrap">
            <thead class="bg-gray-50 text-left text-gray-700">
                <tr>
                    <th class="px-4 py-3 text-sm font-semibold">NIK</th>
                    <th class="px-4 py-3 text-sm font-semibold w-24 text-center">
                        <span class="block leading-tight">Tanggal</span>
                        <span class="block leading-tight">Pengajuan</span>
                    </th>
                    <th class="px-4 py-3 text-sm font-semibold">Nama</th>
                    <th class="px-4 py-3 text-sm font-semibold">Jabatan</th>
                    <th class="px-4 py-3 text-sm font-semibold">Divisi</th>
                    <th class="px-4 py-3 text-sm font-semibold">Jenis</th>
                    <th class="px-4 py-3 text-sm font-semibold w-24 text-center">
                        <span class="block leading-tight">Tanggal</span>
                        <span class="block leading-tight">Mulai</span>
                    </th>
                    <th class="px-4 py-3 text-sm font-semibold w-24 text-center">
                        <span class="block leading-tight">Tanggal</span>
                        <span class="block leading-tight">Selesai</span>
                    </th>
                    <th class="px-4 py-3 text-sm font-semibold">Alasan</th>
                    <th class="px-4 py-3 text-sm font-semibold">Status</th>
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
                            default => 'bg-sky-100 text-sky-700', // izin, izin terlambat
                        };
                        $statusClass = match ($status) {
                            'disetujui' => 'bg-emerald-100 text-emerald-700',
                            'ditolak'   => 'bg-red-100 text-red-700',
                            default     => 'bg-gray-100 text-gray-700',
                        };

                        // ⚙️ Hanya tampilkan tombol edit untuk 'tugas' & 'izin terlambat'
                        $canEdit = in_array($jenis, ['tugas', 'izin terlambat'], true);
                    @endphp

                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $nik }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 text-center">{{ $pengajuan }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $nama }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $jabatan }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $divisi }}</td>

                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $jenisClass }}">{{ ucfirst($jenis) }}</span>
                        </td>

                        <td class="px-4 py-3 text-sm text-gray-700 text-center">{{ $mulai }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700 text-center">{{ $akhir }}</td>
                        <td class="px-4 py-3 text-sm text-gray-700">{{ $alasan }}</td>

                        <td class="px-4 py-3 text-sm">
                            <span class="px-2 py-1 rounded-full text-xs font-medium {{ $statusClass }}">{{ ucfirst($status) }}</span>
                        </td>

                        <td class="px-4 py-3 text-sm">
                            <div class="flex items-center justify-center gap-2">
                                {{-- View Bukti --}}
                                <button type="button"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg border hover:bg-gray-50 disabled:opacity-50"
                                        title="Lihat bukti" onclick="openBukti(this)" data-url="{{ $buktiUrl }}"
                                        {{ $hasFile ? '' : 'disabled' }}>
                                    <img src="{{ asset('images/letter_icon.svg') }}" class="h-5 w-5" alt="bukti surat">
                                    <span class="text-xs text-gray-700">Bukti</span>
                                </button>

                                {{-- Ubah Status: tampilkan HANYA jika boleh diedit --}}
                                @if ($canEdit)
                                    <button type="button"
                                            class="group inline-flex items-center justify-center rounded-full p-2 transition hover:bg-gray-100 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2"
                                            title="Ubah Status"
                                            aria-label="Ubah Status {{ $nama }} {{ $mulai }}-{{ $akhir }}"
                                            @click="
                                                $store.izinModal.openWith({
                                                    targetId: 'edit_status_izin',
                                                    currentStatus: @js($status), // opsi di modal: pending|disetujui|ditolak
                                                    nama: @js($nama === '-' ? '' : $nama),
                                                    mulai: @js($mulai === '-' ? '' : $mulai),
                                                    akhir: @js($akhir === '-' ? '' : $akhir)
                                                });
                                                $wire.set('selectedIzinId', {{ $row->id }});   // pk standar
                                                $wire.set('initialStatus', @js($status));
                                                $wire.set('selectedStatus', @js($status));
                                                $wire.set('selectedNama', @js($nama === '-' ? '' : $nama));
                                                $wire.set('selectedMulai', @js($mulai === '-' ? '' : $mulai));
                                                $wire.set('selectedAkhir', @js($akhir === '-' ? '' : $akhir));
                                            ">
                                        <img src="{{ asset('images/edit_icon.svg') }}" alt=""
                                             class="size-5 opacity-90 transition group-hover:opacity-100 group-hover:scale-110 group-hover:drop-shadow"
                                             loading="lazy" width="20" height="20" />
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

                // ⚙️ Hanya tampilkan tombol edit untuk 'tugas' & 'izin terlambat'
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
                            <span class="inline-block px-2 py-1 rounded-full text-[11px] font-medium {{ $jenisClass }}">{{ ucfirst($jenis) }}</span>
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
                <div class="mt-4 flex items-center gap-2">
                    <button type="button"
                            class="flex-1 inline-flex items-center justify-center gap-2 px-3 py-2 rounded-lg border hover:bg-gray-50 disabled:opacity-50"
                            title="Lihat bukti" onclick="openBukti(this)" data-url="{{ $buktiUrl }}"
                            {{ $hasFile ? '' : 'disabled' }}>
                        <img src="{{ asset('images/letter_icon.svg') }}" class="h-5 w-5" alt="bukti surat">
                        <span class="text-xs text-gray-700">Bukti</span>
                    </button>

                    {{-- Ubah Status: tampilkan HANYA jika boleh diedit --}}
                    @if ($canEdit)
                        <button type="button"
                                class="flex-1 group inline-flex items-center justify-center gap-2 rounded-lg border px-3 py-2 transition hover:bg-gray-100 active:scale-95 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500 focus-visible:ring-offset-2"
                                title="Ubah Status"
                                aria-label="Ubah Status {{ $nama }} {{ $mulai }}-{{ $akhir }}"
                                @click="
                                    $store.izinModal.openWith({
                                        targetId: 'edit_status_izin',
                                        currentStatus: @js($status), // opsi di modal: pending|disetujui|ditolak
                                        nama: @js($nama === '-' ? '' : $nama),
                                        mulai: @js($mulai === '-' ? '' : $mulai),
                                        akhir: @js($akhir === '-' ? '' : $akhir)
                                    });
                                    $wire.set('selectedIzinId', {{ $row->id }});   // pk standar
                                    $wire.set('initialStatus', @js($status));
                                    $wire.set('selectedStatus', @js($status));
                                    $wire.set('selectedNama', @js($nama === '-' ? '' : $nama));
                                    $wire.set('selectedMulai', @js($mulai === '-' ? '' : $mulai));
                                    $wire.set('selectedAkhir', @js($akhir === '-' ? '' : $akhir));
                                ">
                            <img src="{{ asset('images/edit_icon.svg') }}" alt=""
                                 class="size-5 opacity-90 transition group-hover:opacity-100 group-hover:scale-110 group-hover:drop-shadow"
                                 loading="lazy" width="20" height="20" />
                            <span class="text-xs text-gray-700">Ubah Status</span>
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

    {{-- Modal Anda --}}
    @include('livewire.admin.modals.bukti_pengajuan_izin')
    @include('livewire.admin.modals.edit_status_pengajuan_izin')
</div>
