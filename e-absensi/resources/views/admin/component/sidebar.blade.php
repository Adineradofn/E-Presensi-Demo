<aside class="w-64 h-screen bg-white border-r">
  <div class="h-16 flex items-center gap-3 px-6 border-b bg-emerald-600 text-white">
    <img src="{{ asset('images/logo_perusahaan.png') }}" class="h-12 w-12 rounded-full" alt="Logo"> {{-- dipertahankan --}}
    <span class="font-bold text-lg">PT. ASA</span>
  </div>

  <nav class="p-4 space-y-2">
    <p class="uppercase text-sm text-gray-500 mb-2">Menu</p>

    @php
      $baseLink = 'block px-3 py-2 rounded text-base flex items-center gap-3';
      $hover = 'hover:bg-emerald-100 hover:text-emerald-700';
      $active = 'bg-emerald-50 text-emerald-700 border border-emerald-200';
      $normal = 'text-gray-700';
    @endphp

    {{-- Dashboard --}}
    <a href="{{ route('admin.dashboard') }}"
       class="{{ $baseLink }} {{ request()->routeIs('admin.dashboard') ? $active : $normal }} {{ $hover }}">
      <img src="{{ asset('images/icon_dashboard.png') }}" class="h-5 w-5" alt="Dashboard">
      <span>Dashboard</span>
    </a>

    {{-- Data Karyawan --}}
    <a href="{{ route('admin.data.karyawan') }}"
       class="{{ $baseLink }} {{ request()->routeIs('admin.data.karyawan') ? $active : $normal }} {{ $hover }}">
      <img src="{{ asset('images/icon_karyawan.png') }}" class="h-5 w-5" alt="Data Karyawan">
      <span>Data Karyawan</span>
    </a>

    {{-- Kehadiran --}}
    <a href="{{ route('admin.kehadiran') }}"
       class="{{ $baseLink }} {{ request()->routeIs('admin.kehadiran') ? $active : $normal }} {{ $hover }}">
      <img src="{{ asset('images/icon_kehadiran.png') }}" class="h-5 w-5" alt="Kehadiran">
      <span>Kehadiran</span>
    </a>

    {{-- Pengajuan Izin --}}
    <a href="{{ route('admin.pengajuan.izin') }}"
       class="{{ $baseLink }} {{ request()->routeIs('admin.pengajuan.izin') ? $active : $normal }} {{ $hover }}">
      <img src="{{ asset('images/icon_izin.png') }}" class="h-5 w-5" alt="Pengajuan Izin">
      <span>Pengajuan Izin</span>
    </a>
  </nav>
</aside>
