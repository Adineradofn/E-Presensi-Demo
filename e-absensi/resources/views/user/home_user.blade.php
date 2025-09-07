@extends('user.app_user')

@section('content')
  {{-- Heading & sapaan --}}
  <div class="mb-6">
    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Selamat Datang, {{ session('nama') ?? 'Pengguna' }}👋</h2>
    <p class="text-gray-500 mt-1">Senang melihat Anda kembali di sistem e-Absensi</p>
  </div>

  {{-- GRID KARTU --}}
  <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
    {{-- Absensi --}}
    <a href="{{ route('user.absensi') }}"
       class="group relative overflow-hidden rounded-2xl border border-emerald-300 bg-gradient-to-b from-white to-emerald-50/40 p-6 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-emerald-200">
      <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity bg-[radial-gradient(ellipse_at_top,rgba(16,185,129,.12),transparent_60%)]"></div>
      <div class="flex flex-col items-center text-center gap-4">
        {{-- Icon: Calendar Check (SVG inline agar tajam & offline) --}}
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
             class="h-16 w-16 stroke-emerald-600 group-hover:scale-105 transition-transform"
             fill="none" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M8 7V3m8 4V3M6 11h12M6.5 21h11A2.5 2.5 0 0 0 20 18.5v-9A2.5 2.5 0 0 0 17.5 7h-11A2.5 2.5 0 0 0 4 9.5v9A2.5 2.5 0 0 0 6.5 21z"/>
          <path stroke-linecap="round" stroke-linejoin="round"
                d="m9.5 15.5 1.75 1.75L15 13.5"/>
        </svg>
        <div>
          <div class="text-2xl font-bold text-gray-900">Absensi</div>
          <p class="text-gray-500 text-sm mt-1">Check-in / Check-out harian</p>
        </div>
      </div>
    </a>

    {{-- Izin --}}
    <a href="{{ route('user.pengajuan_izin') }}"
       class="group relative overflow-hidden rounded-2xl border border-sky-300 bg-gradient-to-b from-white to-sky-50/40 p-6 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-sky-200">
      <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity bg-[radial-gradient(ellipse_at_top,rgba(56,189,248,.14),transparent_60%)]"></div>
      <div class="flex flex-col items-center text-center gap-4">
        {{-- Icon: Calendar Clock --}}
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
             class="h-16 w-16 stroke-sky-600 group-hover:scale-105 transition-transform"
             fill="none" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M8 7V3m8 4V3M6 11h8m-7.5 10h6A2.5 2.5 0 0 0 15 18.5v-9A2.5 2.5 0 0 0 12.5 7h-6A2.5 2.5 0 0 0 4 9.5v9A2.5 2.5 0 0 0 6.5 21z"/>
          <circle cx="17.5" cy="16.5" r="3.5"/>
          <path stroke-linecap="round" stroke-linejoin="round" d="M17.5 14.8V16.5l1.2 1.2"/>
        </svg>
        <div>
          <div class="text-2xl font-bold text-gray-900">Izin</div>
          <p class="text-gray-500 text-sm mt-1">Ajukan izin / sakit / perjalanan kerja</p>
        </div>
      </div>
    </a>

    {{-- Riwayat Absen --}}
    <a href="{{ route('user.riwayat_absen') }}"
       class="group relative overflow-hidden rounded-2xl border border-amber-300 bg-gradient-to-b from-white to-amber-50/40 p-6 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-amber-200">
      <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity bg-[radial-gradient(ellipse_at_top,rgba(245,158,11,.14),transparent_60%)]"></div>
      <div class="flex flex-col items-center text-center gap-4">
        {{-- Icon: List-Time --}}
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
             class="h-16 w-16 stroke-amber-600 group-hover:scale-105 transition-transform"
             fill="none" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h9M4 12h7M4 17h6"/>
          <circle cx="17.5" cy="16.5" r="3.5"/>
          <path stroke-linecap="round" stroke-linejoin="round" d="M17.5 15v1.6l1.2 1.1"/>
        </svg>
        <div>
          <div class="text-2xl font-bold text-gray-900">Riwayat Absen</div>
          <p class="text-gray-500 text-sm mt-1">Lihat catatan kehadiran</p>
        </div>
      </div>
    </a>

    {{-- Riwayat Izin --}}
    <a href="{{ route('user.riwayat_izin') }}"
       class="group relative overflow-hidden rounded-2xl border border-rose-300 bg-gradient-to-b from-white to-rose-50/40 p-6 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-rose-200">
      <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity bg-[radial-gradient(ellipse_at_top,rgba(244,63,94,.12),transparent_60%)]"></div>
      <div class="flex flex-col items-center text-center gap-4">
        {{-- Icon: Document-Time --}}
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
             class="h-16 w-16 stroke-rose-600 group-hover:scale-105 transition-transform"
             fill="none" stroke-width="1.5">
          <path stroke-linecap="round" stroke-linejoin="round"
                d="M8 3h5l3 3v12.5A2.5 2.5 0 0 1 13.5 21H8A2 2 0 0 1 6 19V5a2 2 0 0 1 2-2z"/>
          <circle cx="17.5" cy="16.5" r="3.5"/>
          <path stroke-linecap="round" stroke-linejoin="round" d="M17.5 15v1.6l1.2 1.1"/>
        </svg>
        <div>
          <div class="text-2xl font-bold text-gray-900">Riwayat Izin</div>
          <p class="text-gray-500 text-sm mt-1">Pantau status & histori</p>
        </div>
      </div>
    </a>
  </div>
@endsection
