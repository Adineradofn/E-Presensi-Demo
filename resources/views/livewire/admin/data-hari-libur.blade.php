<div
    id="pageHariLibur"
    x-data="{
        async confirmDelete(id, name) {
            const res = await Swal.fire({
                title: `Hapus ${name || 'hari libur ini'}?`,
                text: 'Data yang dihapus tidak dapat dikembalikan',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                focusCancel: true,
            });
            if (res.isConfirmed) { await $wire.destroy(id); }
        }
    }"
    @keydown.escape.window="
        window.dispatchEvent(new CustomEvent('modal-create-close'));
        window.dispatchEvent(new CustomEvent('close-libur-modal'));
    "
>
    <div class="p-4">
        <h2 class="text-xl sm:text-2xl font-bold mb-4">Data Hari Libur</h2>

        {{-- ==================== FILTER BULAN/TAHUN (tanpa hari) ==================== --}}
        <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
            <div class="inline-flex w-fit max-w-full self-start sm:self-auto rounded-lg border border-gray-200 overflow-hidden whitespace-nowrap">
                <a href="{{ request()->fullUrlWithQuery(['mode' => 'bulan']) }}" wire:click.prevent="setMode('bulan')"
                   class="px-4 py-2 text-sm font-medium {{ $mode === 'bulan' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                    Bulan
                </a>
                <a href="{{ request()->fullUrlWithQuery(['mode' => 'tahun']) }}" wire:click.prevent="setMode('tahun')"
                   class="px-4 py-2 text-sm font-medium border-l border-gray-200 {{ $mode === 'tahun' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
                    Tahun
                </a>
            </div>

            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full sm:w-auto">
                @if ($mode === 'bulan')
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
            </div>
        </div>
        {{-- ==================== /FILTER BULAN/TAHUN ==================== --}}

        {{-- Toolbar (tetap) --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
            <button type="button"
                class="w-full sm:w-auto inline-flex items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg shadow-sm"
                @click="$dispatch('modal-create-open'); $wire.openCreateModal()">
                Tambah Hari Libur
            </button>

            <div class="flex flex-col sm:flex-row gap-2 sm:items-center sm:justify-end sm:w-auto">
                <div class="relative sm:w-80">
                    <input type="text" wire:model.live.debounce.600ms="q"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 pr-12 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                        placeholder="Cari (nama/keterangan/tanggal)" wire:keydown.escape="$set('q','')"
                        aria-label="Pencarian hari libur" />
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

        {{-- ====== MOBILE (CARD LIST) ====== --}}
        <div class="md:hidden space-y-3">
            @forelse ($items as $h)
                <div class="group relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-4 shadow-sm ring-1 ring-black/5"
                     wire:key="card-{{ $h->id }}">
                    <div class="flex items-start gap-3">
                        <div class="relative h-12 w-12 shrink-0 rounded-xl bg-gradient-to-br from-emerald-500 to-green-500 text-white ring-2 shadow-sm grid place-items-center">
                            <img src="{{ asset('images/calendar_white_icon.svg') }}" class="h-7 w-7" alt="libur">
                        </div>

                        <div class="min-w-0">
                            <div class="text-base font-semibold text-gray-900 leading-6 truncate">{{ $h->nama_hari }}</div>
                            <div class="mt-0.5 text-xs text-gray-500">
                                {{ optional($h->tanggal_mulai)->format('Y-m-d') }} – {{ optional($h->tanggal_selesai)->format('Y-m-d') }}
                            </div>
                            @if ($h->keterangan)
                                <p class="mt-1 text-sm text-gray-700">{{ $h->keterangan }}</p>
                            @endif
                        </div>
                    </div>

                    {{-- Aksi (Mobile) --}}
                    <div class="mt-4">
                        <div class="grid grid-cols-2 gap-2">
                            <button type="button"
                                class="inline-flex items-center justify-center rounded-lg border border-emerald-600 bg-white px-2 py-1.5 text-[12px] font-medium text-emerald-700 hover:bg-emerald-50"
                                @click="
                                    $store.liburModal.openWith({
                                        targetId:'modalEditLibur',
                                        id: {{ $h->id }},
                                        nama_hari: @js($h->nama_hari),
                                        tanggal_mulai: @js(optional($h->tanggal_mulai)->toDateString() ?? ''),
                                        tanggal_selesai: @js(optional($h->tanggal_selesai)->toDateString() ?? ''),
                                        keterangan: @js($h->keterangan ?? '')
                                    });
                                    $wire.set('edit.id', {{ $h->id }});
                                    $wire.set('edit.nama_hari', @js($h->nama_hari));
                                    $wire.set('edit.tanggal_mulai', @js(optional($h->tanggal_mulai)->toDateString() ?? ''));
                                    $wire.set('edit.tanggal_selesai', @js(optional($h->tanggal_selesai)->toDateString() ?? ''));
                                    $wire.set('edit.keterangan', @js($h->keterangan ?? ''));
                                ">
                                Edit
                            </button>
                            <button type="button"
                                class="inline-flex items-center justify-center rounded-lg border border-red-500 bg-white px-2 py-1.5 text-[12px] font-medium text-red-700 hover:bg-red-50"
                                @click.prevent="confirmDelete({{ $h->id }}, @js($h->nama_hari))">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center text-gray-500">
                    Belum ada data
                </div>
            @endforelse
        </div>

        {{-- ====== DESKTOP (TABLE) ====== --}}
        <div class="hidden md:block overflow-x-auto bg-white rounded-xl border border-gray-200 shadow-sm">
            <table class="min-w-full">
                <thead class="bg-gray-200 text-left text-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-sm font-semibold">Nama Hari</th>
                        <th class="px-4 py-3 text-sm font-semibold">Tanggal Mulai</th>
                        <th class="px-4 py-3 text-sm font-semibold">Tanggal Selesai</th>
                        <th class="px-4 py-3 text-sm font-semibold">Keterangan</th>
                        <th class="px-4 py-3 text-sm font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($items as $h)
                        <tr class="hover:bg-gray-50" wire:key="row-{{ $h->id }}">
                            <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">{{ $h->nama_hari }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">{{ optional($h->tanggal_mulai)->format('Y-m-d') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">{{ optional($h->tanggal_selesai)->format('Y-m-d') }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                <span class="block max-w-[320px] truncate" title="{{ $h->keterangan }}">{{ $h->keterangan }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2 justify-end">
                                    <button type="button"
                                        class="px-3 py-1.5 rounded-lg border border-emerald-600 text-emerald-700 hover:bg-emerald-50 text-sm"
                                        @click="
                                            $store.liburModal.openWith({
                                                targetId:'modalEditLibur',
                                                id: {{ $h->id }},
                                                nama_hari: @js($h->nama_hari),
                                                tanggal_mulai: @js(optional($h->tanggal_mulai)->toDateString() ?? ''),
                                                tanggal_selesai: @js(optional($h->tanggal_selesai)->toDateString() ?? ''),
                                                keterangan: @js($h->keterangan ?? '')
                                            });
                                            $wire.set('edit.id', {{ $h->id }});
                                            $wire.set('edit.nama_hari', @js($h->nama_hari));
                                            $wire.set('edit.tanggal_mulai', @js(optional($h->tanggal_mulai)->toDateString() ?? ''));
                                            $wire.set('edit.tanggal_selesai', @js(optional($h->tanggal_selesai)->toDateString() ?? ''));
                                            $wire.set('edit.keterangan', @js($h->keterangan ?? ''));
                                        ">
                                        Edit
                                    </button>
                                    <button type="button"
                                        class="px-3 py-1.5 rounded-lg border border-red-600 text-red-700 hover:bg-red-50 text-sm"
                                        @click.prevent="confirmDelete({{ $h->id }}, @js($h->nama_hari))">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-6 text-center text-gray-500">Belum ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer: Pagination + PerPage --}}
        <div class="mt-4 flex items-center justify-end gap-4">
            <div class="relative">
                <label class="sr-only" for="perPageBottom">Baris per halaman</label>
                <select id="perPageBottom"
                    class="appearance-none h-10 rounded-xl border border-gray-300 bg-white px-3 pr-10 text-sm text-gray-900 shadow-sm transition
                           hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500"
                    wire:model.live="perPage" title="Baris per halaman" aria-label="Baris per halaman">
                    <option value="5">5 / halaman</option>
                    <option value="10">10 / halaman</option>
                    <option value="25">25 / halaman</option>
                    <option value="50">50 / halaman</option>
                </select>
                <span class="pointer-events-none absolute right-1.5 top-1/2 -translate-y-1/2 inline-flex h-7 w-7 items-center justify-center rounded-md text-gray-500">
                    <img src="{{ asset('images/dropdown_icon.svg') }}" class="h-5 w-5" alt="dropdown">
                </span>
            </div>

            <div class="shrink-0">
                {{ $items->links() }}
            </div>
        </div>
    </div>

    {{-- MODALS (wajib di-include agar store & DOM tersedia) --}}
    @include('livewire.admin.modals.create_hari_libur', ['tomorrow' => $tomorrow])
    @include('livewire.admin.modals.edit_hari_libur', ['tomorrow' => $tomorrow])

    @once
    <style>[x-cloak]{display:none!important}</style>
    @endonce
</div>
