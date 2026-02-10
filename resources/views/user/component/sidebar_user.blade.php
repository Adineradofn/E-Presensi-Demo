@persist('sidebar_user')
<div
  x-data="{ open: false }"
  x-effect="document.body.classList.toggle('overflow-hidden', open && window.innerWidth < 768)"
  class="relative"
>
  {{-- Tombol toggle mobile (fixed, tidak ikut scroll) --}}
  <button
    x-show="!open"
    x-cloak
    @click="open = true"
    :aria-expanded="open.toString()"
    aria-label="Open sidebar"
    class="md:hidden fixed z-[60]
           top-[calc(env(safe-area-inset-top,0)+1rem)]
           left-[calc(env(safe-area-inset-left,0)+1rem)]
           bg-emerald-600 text-white p-2 rounded-full shadow-lg ring-1 ring-black/10"
    x-transition
  >
    <img src="{{ asset('images/hamburger_icon.svg') }}" class="h-6 w-6" alt="hamburger" loading="lazy">
  </button>

  {{-- Desktop sidebar (md+) --}}
  <aside class="hidden md:block w-64 h-screen bg-white fixed left-0 top-0 z-30 border-r border-gray-200">
    <div class="h-16 flex items-center gap-3 px-6 border-b bg-emerald-600 text-white">
      <img src="{{ asset('images/logo_perusahaan.svg') }}" class="h-12 w-12 rounded-full" alt="Logo" loading="lazy">
      <span class="font-bold text-lg">PT. ASA</span>
    </div>

    <nav class="p-4 space-y-2">
      <p class="uppercase text-sm text-gray-500 mb-2">Menu</p>

      @php
        $baseLink = 'block px-3 py-2 rounded text-base flex items-center gap-3';
        $hover    = 'hover:bg-emerald-100 hover:text-emerald-700';
        $active   = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
        $normal   = 'text-gray-700';
      @endphp

      <a href="{{ route('user.home') }}"
         wire:navigate.hover
         wire:current="{{ $active }}"
         class="{{ $baseLink }} {{ $hover }} {{ request()->routeIs('user.home') ? $active : $normal }}">
        <img src="{{ asset('images/home_icon.svg') }}" class="h-5 w-5" alt="Home" loading="lazy">
        <span>Home</span>
      </a>

      <a href="{{ route('user.presensi') }}"
         wire:navigate.hover
         wire:current="{{ $active }}"
         class="{{ $baseLink }} {{ $hover }} {{ request()->routeIs('user.presensi*') ? $active : $normal }}">
        <img src="{{ asset('images/presensi_icon.svg') }}" class="h-5 w-5" alt="Presensi" loading="lazy">
        <span>Presensi</span>
      </a>

      <a href="{{ route('user.pengajuan_izin') }}"
         wire:navigate.hover
         wire:current="{{ $active }}"
         class="{{ $baseLink }} {{ $hover }} {{ request()->routeIs('user.pengajuan_izin*') ? $active : $normal }}">
        <img src="{{ asset('images/pengajuan_izin_icon.svg') }}" class="h-5 w-5" alt="Pengajuan Izin" loading="lazy">
        <span>Pengajuan Izin</span>
      </a>

      <a href="{{ route('user.riwayat_presensi') }}"
         wire:navigate.hover
         wire:current="{{ $active }}"
         class="{{ $baseLink }} {{ $hover }} {{ request()->routeIs('user.riwayat_presensi*') ? $active : $normal }}">
        <img src="{{ asset('images/riwayat_presensi_icon.svg') }}" class="h-5 w-5" alt="Riwayat Presensi" loading="lazy">
        <span>Riwayat Presensi</span>
      </a>

      <a href="{{ route('user.riwayat_izin') }}"
         wire:navigate.hover
         wire:current="{{ $active }}"
         class="{{ $baseLink }} {{ $hover }} {{ request()->routeIs('user.riwayat_izin*') ? $active : $normal }}">
        <img src="{{ asset('images/riwayat_pengajuan_izin_icon.svg') }}" class="h-5 w-5" alt="Riwayat Izin" loading="lazy">
        <span>Riwayat Izin</span>
      </a>
    </nav>
  </aside>

  {{-- Backdrop mobile --}}
  <div
    x-show="open"
    x-transition.opacity
    class="fixed inset-0 z-40 md:hidden bg-black/20 backdrop-blur-[1px]"
    @click="open = false"
    aria-hidden="true"
  ></div>

  {{-- Sidebar mobile (slide-over) --}}
  <aside
    x-show="open"
    x-transition:enter="transition transform duration-200"
    x-transition:enter-start="-translate-x-full"
    x-transition:enter-end="translate-x-0"
    x-transition:leave="transition transform duration-200"
    x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full"
    class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-xl md:hidden border-r border-gray-200"
    @keydown.escape.window="open = false"
    x-cloak
    role="dialog"
    aria-modal="true"
  >
    <div class="h-16 flex items-center gap-3 px-6 border-b bg-emerald-600 text-white">
      <img src="{{ asset('images/logo_perusahaan.svg') }}" class="h-12 w-12 rounded-full" alt="Logo" loading="lazy">
      <span class="font-bold text-lg">PT. ASA</span>

      <button @click="open = false" aria-label="Close sidebar"
              class="ml-auto p-2 rounded-md focus:outline-none focus:ring-2 focus:ring-white">
        <img src="{{ asset('images/cancel_icon.svg') }}" class="h-8 w-8" alt="cancel" loading="lazy">
      </button>
    </div>

    <nav class="p-4 space-y-2">
      <p class="uppercase text-sm text-gray-500 mb-2">Menu</p>

      @php
        $baseLink = 'block px-3 py-2 rounded text-base flex items-center gap-3';
        $hover    = 'hover:bg-emerald-100 hover:text-emerald-700';
        $active   = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
        $normal   = 'text-gray-700';
      @endphp

      <a href="{{ route('user.home') }}"
         wire:navigate.hover
         wire:current="{{ $active }}"
         @click="open = false"
         class="{{ $baseLink }} {{ $hover }} {{ request()->routeIs('user.home') ? $active : $normal }}">
        <img src="{{ asset('images/home_icon.svg') }}" class="h-5 w-5" alt="Home" loading="lazy">
        <span>Home</span>
      </a>

      <a href="{{ route('user.presensi') }}"
         wire:navigate.hover
         wire:current="{{ $active }}"
         @click="open = false"
         class="{{ $baseLink }} {{ $hover }} {{ request()->routeIs('user.presensi*') ? $active : $normal }}">
        <img src="{{ asset('images/presensi_icon.svg') }}" class="h-5 w-5" alt="Presensi" loading="lazy">
        <span>Presensi</span>
      </a>

      <a href="{{ route('user.pengajuan_izin') }}"
         wire:navigate.hover
         wire:current="{{ $active }}"
         @click="open = false"
         class="{{ $baseLink }} {{ $hover }} {{ request()->routeIs('user.pengajuan_izin*') ? $active : $normal }}">
        <img src="{{ asset('images/pengajuan_izin_icon.svg') }}" class="h-5 w-5" alt="Pengajuan Izin" loading="lazy">
        <span>Pengajuan Izin</span>
      </a>

      <a href="{{ route('user.riwayat_presensi') }}"
         wire:navigate.hover
         wire:current="{{ $active }}"
         @click="open = false"
         class="{{ $baseLink }} {{ $hover }} {{ request()->routeIs('user.riwayat_presensi*') ? $active : $normal }}">
        <img src="{{ asset('images/riwayat_presensi_icon.svg') }}" class="h-5 w-5" alt="Riwayat Presensi" loading="lazy">
        <span>Riwayat Presensi</span>
      </a>

      <a href="{{ route('user.riwayat_izin') }}"
         wire:navigate.hover
         wire:current="{{ $active }}"
         @click="open = false"
         class="{{ $baseLink }} {{ $hover }} {{ request()->routeIs('user.riwayat_izin*') ? $active : $normal }}">
        <img src="{{ asset('images/riwayat_pengajuan_izin_icon.svg') }}" class="h-5 w-5" alt="Riwayat Izin" loading="lazy">
        <span>Riwayat Izin</span>
      </a>
    </nav>
  </aside>
</div>
@endpersist
