{{-- 
    Sidebar admin yang responsif (desktop: statis kiri; mobile: slide-over).
    Peningkatan:
    - Tambah item "Rekap Presensi" di desktop & mobile (konsisten).
    - Perbaiki ikon rekap: icon_rekap.svg.
    - Aksesibilitas: aria-label, role="navigation".
    - Border kanan di desktop untuk pemisah visual.
--}}

@persist('sidebar')
    <div x-data="{ open: false }" {{-- Kunci scroll body saat sidebar mobile terbuka --}}
        x-effect="document.body.classList.toggle('overflow-hidden', open && window.innerWidth < 768)" class="relative">
        {{-- Tombol toggle (mobile). Fixed, tampil saat sidebar tertutup --}}
        <button x-show="!open" x-cloak @click="open = true" :aria-expanded="open.toString()" aria-label="Open sidebar"
            class="md:hidden fixed z-[60]
                   top-[calc(env(safe-area-inset-top,0)+1rem)]
                   left-[calc(env(safe-area-inset-left,0)+1rem)]
                   bg-emerald-600 text-white p-2 rounded-full shadow-lg ring-1 ring-black/10"
            x-transition>
            <img src="{{ asset('images/hamburger_icon.svg') }}" class="h-8 w-8" alt="Open menu">
        </button>

        {{-- Sidebar DESKTOP (md+): selalu terlihat, ditempel kiri layar --}}
        <aside class="hidden md:block w-64 h-screen bg-white fixed left-0 top-0 z-30 border-r border-gray-200"
            role="navigation" aria-label="Admin sidebar">
            {{-- Header sidebar (logo + nama perusahaan) --}}
            <div class="h-16 flex items-center gap-3 px-6 border-b bg-emerald-600 text-white">
                <img src="{{ asset('images/logo_perusahaan.svg') }}" class="h-12 w-12 rounded-full" alt="Logo">
                <span class="font-bold text-lg">PT. ASA</span>
            </div>

            {{-- Navigasi utama --}}
            <nav class="p-4 space-y-2">
                <p class="uppercase text-sm text-gray-500 mb-2">Menu</p>

                @php
                    // Util kelas link nav
                    $baseLink = 'block px-3 py-2 rounded text-base flex items-center gap-3';
                    $hover = 'hover:bg-emerald-100 hover:text-emerald-700';
                    $active = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                    $normal = 'text-gray-700';
                @endphp

                {{-- wire:current akan menambahkan kelas $active bila route cocok --}}
                <a href="{{ route('admin.dashboard') }}" wire:navigate wire:current="{{ $active }}"
                    class="{{ $baseLink }} {{ $hover }} {{ $normal }}">
                    <img src="{{ asset('images/icon_dashboard.svg') }}" class="h-8 w-8" alt="Dashboard">
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('admin.data.karyawan') }}" wire:navigate wire:current="{{ $active }}"
                    class="{{ $baseLink }} {{ $hover }} {{ $normal }}">
                    <img src="{{ asset('images/icon_karyawan.svg') }}" class="h-8 w-8" alt="Data Karyawan">
                    <span>Data Karyawan</span>
                </a>

                <a href="{{ route('admin.data.presensi') }}" wire:navigate wire:current="{{ $active }}"
                    class="{{ $baseLink }} {{ $hover }} {{ $normal }}">
                    <img src="{{ asset('images/icon_kehadiran.svg') }}" class="h-8 w-8" alt="Data Presensi">
                    <span>Data Presensi</span>
                </a>

                {{-- NEW: Rekap Presensi (desktop) --}}
                <a href="{{ route('admin.data.rekap-presensi') }}" wire:navigate wire:current="{{ $active }}"
                    class="{{ $baseLink }} {{ $hover }} {{ $normal }}">
                    <img src="{{ asset('images/document_icon.svg') }}" class="h-8 w-8" alt="Rekap Presensi">
                    <span>Rekap Presensi</span>
                </a>

                <a href="{{ route('admin.pengajuan-izin') }}" wire:navigate wire:current="{{ $active }}"
                    class="{{ $baseLink }} {{ $hover }} {{ $normal }}">
                    <img src="{{ asset('images/icon_izin.svg') }}" class="h-8 w-8" alt="Data Pengajuan Izin">
                    <span>Data Pengajuan Izin</span>
                </a>
                <a href="{{ route('admin.hari-libur') }}" wire:navigate wire:current="{{ $active }}"
                    class="{{ $baseLink }} {{ $hover }} {{ $normal }}">
                    <img src="{{ asset('images/calendarx_icon.svg') }}" class="h-8 w-8" alt="Data Hari libur">
                    <span>Data Hari Libur</span>
                </a>
            </nav>
        </aside>

        {{-- Backdrop untuk mobile: klik untuk menutup --}}
        <div x-show="open" x-transition.opacity class="fixed inset-0 z-40 md:hidden bg-black/15" @click="open = false"
            aria-hidden="true"></div>

        {{-- Sidebar MOBILE (slide-over dari kiri) --}}
        <aside x-show="open" x-transition:enter="transition transform duration-200"
            x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
            x-transition:leave="transition transform duration-200" x-transition:leave-start="translate-x-0"
            x-transition:leave-end="-translate-x-full" class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-xl md:hidden"
            @keydown.escape.window="open = false" x-cloak role="dialog" aria-modal="true"
            aria-label="Mobile admin sidebar">
            {{-- Header sidebar mobile (logo + nama + tombol close) --}}
            <div class="h-16 flex items-center gap-3 px-6 border-b bg-emerald-600 text-white">
                <img src="{{ asset('images/logo_perusahaan.svg') }}" class="h-12 w-12 rounded-full" alt="Logo">
                <span class="font-bold text-lg">PT. ASA</span>

                <button @click="open = false" aria-label="Close sidebar"
                    class="ml-auto p-2 rounded-md focus:outline-none focus:ring-2 focus:ring-white">
                    <img src="{{ asset('images/cancel_icon.svg') }}" class="h-8 w-8" alt="Close">
                </button>
            </div>

            {{-- Navigasi mobile: setiap klik link juga menutup sidebar --}}
            <nav class="p-4 space-y-2">
                <p class="uppercase text-sm text-gray-500 mb-2">Menu</p>

                @php
                    $baseLink = 'block px-3 py-2 rounded text-base flex items-center gap-3';
                    $hover = 'hover:bg-emerald-100 hover:text-emerald-700';
                    $active = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
                    $normal = 'text-gray-700';
                @endphp

                <a href="{{ route('admin.dashboard') }}" wire:navigate wire:current="{{ $active }}"
                    @click="open = false" class="{{ $baseLink }} {{ $hover }} {{ $normal }}">
                    <img src="{{ asset('images/icon_dashboard.svg') }}" class="h-8 w-8" alt="Dashboard">
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('admin.data.karyawan') }}" wire:navigate wire:current="{{ $active }}"
                    @click="open = false" class="{{ $baseLink }} {{ $hover }} {{ $normal }}">
                    <img src="{{ asset('images/icon_karyawan.svg') }}" class="h-8 w-8" alt="Data Karyawan">
                    <span>Data Karyawan</span>
                </a>

                <a href="{{ route('admin.data.presensi') }}" wire:navigate wire:current="{{ $active }}"
                    @click="open = false" class="{{ $baseLink }} {{ $hover }} {{ $normal }}">
                    <img src="{{ asset('images/icon_kehadiran.svg') }}" class="h-8 w-8" alt="Data Presensi">
                    <span>Data Presensi</span>
                </a>

                {{-- NEW: Rekap Presensi (mobile) --}}
                <a href="{{ route('admin.data.rekap-presensi') }}" wire:navigate wire:current="{{ $active }}"
                    @click="open = false" class="{{ $baseLink }} {{ $hover }} {{ $normal }}">
                    <img src="{{ asset('images/document_icon.svg') }}" class="h-8 w-8" alt="Rekap Presensi">
                    <span>Rekap Presensi</span>
                </a>

                <a href="{{ route('admin.pengajuan-izin') }}" wire:navigate wire:current="{{ $active }}"
                    @click="open = false" class="{{ $baseLink }} {{ $hover }} {{ $normal }}">
                    <img src="{{ asset('images/icon_izin.svg') }}" class="h-8 w-8" alt="Data Pengajuan Izin">
                    <span>Data Pengajuan Izin</span>
                </a>
                <a href="{{ route('admin.hari-libur') }}" wire:navigate wire:current="{{ $active }}"
                    class="{{ $baseLink }} {{ $hover }} {{ $normal }}">
                    <img src="{{ asset('images/calendarx_icon.svg') }}" class="h-8 w-8" alt="Data Hari libur">
                    <span>Data Hari Libur</span>
                </a>
            </nav>
        </aside>
    </div>
@endpersist
