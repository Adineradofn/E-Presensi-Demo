{{-- resources/views/livewire/admin/data-karyawan.blade.php --}}
<div
    x-data="{
        async confirmDelete(id, name) {
            const res = await Swal.fire({
                title: `Hapus ${name || 'data ini'}?`,
                text: 'Data yang dihapus tidak dapat dikembalikan',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                focusCancel: true,
            });

            if (res.isConfirmed) {
                await $wire.destroy(id);
            }
        }
    }"
    @keydown.escape.window="
        window.dispatchEvent(new CustomEvent('modal-create-close'));
        window.dispatchEvent(new CustomEvent('modal-edit-close'));
        window.dispatchEvent(new CustomEvent('modal-password-close'));
    "
>
    <div class="p-4">
        <h2 class="text-xl sm:text-2xl font-bold mb-4">Data Karyawan</h2>

        {{-- Toolbar --}}
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between mb-4">
            <button type="button"
                class="w-full sm:w-auto inline-flex items-center justify-center bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg shadow-sm"
                @click="$dispatch('modal-create-open'); $wire.openCreateModal()">
                Tambah Karyawan
            </button>

            <div class="flex flex-col sm:flex-row gap-2 sm:items-center sm:justify-end sm:w-auto">
                <div class="relative sm:w-80">
                    <input type="text" wire:model.live.debounce.600ms="q"
                        class="w-full rounded-lg border border-gray-300 px-4 py-2 pr-12 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                        placeholder="Cari Karyawan" wire:keydown.escape="$set('q','')"
                        aria-label="Pencarian karyawan" />

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
            @forelse ($karyawans as $k)
                @php
                    $palette = [
                        'from-emerald-500 to-green-500 ring-emerald-100',
                        'from-sky-500 to-indigo-500 ring-sky-100',
                        'from-rose-500 to-pink-500 ring-rose-100',
                        'from-amber-500 to-orange-500 ring-amber-100',
                        'from-violet-500 to-purple-500 ring-violet-100',
                        'from-teal-500 to-emerald-500 ring-teal-100',
                    ];
                    $pi = ($k->id ?? 0) % count($palette);
                    $grad = $palette[$pi];
                @endphp

                <div class="group relative overflow-hidden rounded-2xl border border-gray-200 bg-white p-4 shadow-sm ring-1 ring-black/5"
                    wire:key="card-{{ $k->id }}">
                    <div
                        class="pointer-events-none absolute inset-x-0 -top-10 h-24 bg-gradient-to-r from-emerald-500/10 via-emerald-500/5 to-transparent blur-2xl">
                    </div>

                    <div class="flex items-start gap-3">
                        @if (!empty($k->photo_url))
                            <img src="{{ $k->photo_url }}" alt="{{ $k->nama }}"
                                class="h-12 w-12 shrink-0 rounded-full object-cover ring-2 ring-black/5" />
                        @else
                            <div
                                class="relative h-12 w-12 shrink-0 rounded-full bg-gradient-to-br {{ $grad }} text-white ring-2 shadow-sm flex items-center justify-center">
                                <img src="{{ asset('images/user_icon.svg') }}" class="h-8 w-8" alt="user">
                                <span class="sr-only">{{ $k->nama }}</span>
                            </div>
                        @endif

                        <div class="min-w-0">
                            <div class="text-base font-semibold text-gray-900 leading-6 truncate">{{ $k->nama }}</div>
                            <div class="mt-0.5 flex items-center gap-2">
                                <span class="text-xs text-gray-500 truncate max-w-[220px]">{{ $k->email }}</span>
                            </div>

                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <span class="inline-flex items-center rounded-full border border-emerald-200 bg-emerald-50 px-2 py-0.5 text-[11px] font-medium text-emerald-700">{{ $k->divisi }}</span>
                                <span class="inline-flex items-center rounded-full border border-gray-200 bg-gray-50 px-2 py-0.5 text-[11px] font-medium text-gray-700">{{ $k->jabatan }}</span>
                                <span class="inline-flex items-center rounded-full border border-indigo-200 bg-indigo-50 px-2 py-0.5 text-[11px] font-medium text-indigo-700">{{ $k->role }}</span>
                            </div>
                        </div>
                    </div>

                    <dl class="mt-3 grid grid-cols-2 gap-x-4 gap-y-2 text-sm">
                        <div class="col-span-2">
                            <dt class="text-gray-500">NIK</dt>
                            <dd class="font-medium text-gray-900 tracking-tight">{{ $k->nik }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Divisi</dt>
                            <dd class="text-gray-900">{{ $k->divisi }}</dd>
                        </div>
                        <div>
                            <dt class="text-gray-500">Jabatan</dt>
                            <dd class="text-gray-900">{{ $k->jabatan }}</dd>
                        </div>
                    </dl>

                    {{-- Aksi (Mobile) --}}
                    <div class="mt-4">
                        <div class="grid grid-cols-3 gap-2">
                            {{-- Ubah --}}
                            <button type="button"
                                class="col-span-1 inline-flex h-9 items-center justify-center gap-1.5 rounded-lg border border-emerald-600 bg-white px-2 py-1.5 text-[12px] font-medium text-emerald-700 hover:bg-emerald-50 active:scale-[.98] focus:outline-none focus:ring-2 focus:ring-emerald-500/50 touch-manipulation"
                                @click="$dispatch('modal-edit-open'); $wire.openEditModal({{ $k->id }})"
                                aria-label="Ubah Data {{ $k->nama }}">
                                <img src="{{ asset('images/edit_dropdown_icon.svg') }}" class="h-4 w-4" alt="edit">
                                <span class="whitespace-nowrap">Ubah</span>
                            </button>

                            {{-- Password --}}
                            <button type="button"
                                class="col-span-1 inline-flex h-9 items-center justify-center gap-1.5 rounded-lg border border-amber-500 bg-amber-50 px-2 py-1.5 text-[12px] font-medium text-amber-800 hover:bg-amber-100 active:scale-[.98] focus:outline-none focus:ring-2 focus:ring-amber-500/50 touch-manipulation"
                                @click="$dispatch('modal-password-open'); $wire.openPasswordModal({{ $k->id }})"
                                aria-label="Ubah Password {{ $k->nama }}">
                                <img src="{{ asset('images/password_dropdown_icon.svg') }}" class="h-4 w-4" alt="pwd">
                                <span class="whitespace-nowrap">Password</span>
                            </button>

                            {{-- Hapus --}}
                            <button type="button"
                                class="col-span-1 inline-flex h-9 items-center justify-center gap-1.5 rounded-lg border border-red-500 bg-white px-2 py-1.5 text-[12px] font-medium text-red-700 hover:bg-red-50 active:scale-[.98] focus:outline-none focus:ring-2 focus:ring-red-500/50 touch-manipulation"
                                @click.prevent="confirmDelete({{ $k->id }}, @js($k->nama))"
                                aria-label="Hapus {{ $k->nama }}">
                                <img src="{{ asset('images/trash_icon.svg') }}" class="h-4 w-4" alt="hapus">
                                <span class="whitespace-nowrap">Hapus</span>
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
                <thead class="bg-gray-50 text-left text-gray-700">
                    <tr>
                        <th class="px-4 py-3 text-sm font-semibold">NIK</th>
                        <th class="px-4 py-3 text-sm font-semibold">Nama</th>
                        <th class="px-4 py-3 text-sm font-semibold">Email</th>
                        <th class="px-4 py-3 text-sm font-semibold">Divisi</th>
                        <th class="px-4 py-3 text-sm font-semibold">Jabatan</th>
                        <th class="px-4 py-3 text-sm font-semibold">Role</th>
                        <th class="px-4 py-3 text-sm font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($karyawans as $k)
                        <tr class="hover:bg-gray-50" wire:key="row-{{ $k->id }}">
                            <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">{{ $k->nik }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">{{ $k->nama }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700">
                                <span class="block max-w-[220px] truncate" title="{{ $k->email }}">{{ $k->email }}</span>
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">{{ $k->divisi }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">{{ $k->jabatan }}</td>
                            <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">{{ $k->role }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2 justify-end">
                                    <button type="button"
                                        class="px-3 py-1.5 rounded-lg border border-emerald-600 text-emerald-700 hover:bg-emerald-50 text-sm"
                                        @click="$dispatch('modal-edit-open'); $wire.openEditModal({{ $k->id }})">
                                        Ubah Data
                                    </button>

                                    <button type="button"
                                        class="px-3 py-1.5 rounded-lg border border-amber-600 text-amber-700 hover:bg-amber-50 text-sm"
                                        @click="$dispatch('modal-password-open'); $wire.openPasswordModal({{ $k->id }})">
                                        Ubah Password
                                    </button>

                                    <button type="button"
                                        class="px-3 py-1.5 rounded-lg border border-red-600 text-red-700 hover:bg-red-50 text-sm"
                                        @click.prevent="confirmDelete({{ $k->id }}, @js($k->nama))">
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-6 text-center text-gray-500">Belum ada data</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- ====== FOOTER: Pagination + PerPage ====== --}}
        <div class="mt-4">
            <div class="hidden md:flex items-center justify-end gap-4">
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
                    {{ $karyawans->links() }}
                </div>
            </div>

            <div class="md:hidden space-y-3">
                <div class="flex justify-center">
                    {{ $karyawans->onEachSide(0)->links('pagination::simple-tailwind') }}
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1" for="perPageBottomSm">Baris per halaman</label>
                    <div class="relative">
                        <select id="perPageBottomSm"
                            class="appearance-none w-full h-11 rounded-xl border border-gray-300 bg-white px-3 pr-10 text-sm text-gray-900 shadow-sm transition
                                   hover:border-gray-400 focus:outline-none focus:ring-2 focus:ring-emerald-500/40 focus:border-emerald-500"
                            wire:model.live="perPage" title="Baris per halaman" aria-label="Baris per halaman">
                            <option value="5">5 / halaman</option>
                            <option value="10">10 / halaman</option>
                            <option value="25">25 / halaman</option>
                            <option value="50">50 / halaman</option>
                        </select>
                        <span class="pointer-events-none absolute right-1.5 top-1/2 -translate-y-1/2 inline-flex h-8 w-8 items-center justify-center rounded-md text-gray-500">
                            <img src="{{ asset('images/dropdown_icon.svg') }}" class="h-5 w-5" alt="dropdown">
                        </span>

                        {{-- sekarang primary key nya bernama id sesuai dengan penamaan standar database --}}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODALS --}}
    @include('livewire.admin.modals.create_karyawan')
    @include('livewire.admin.modals.edit_karyawan')
    @include('livewire.admin.modals.edit_password_karyawan')
</div>
