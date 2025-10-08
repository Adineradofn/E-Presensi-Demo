@extends('user.app_user')

@section('content')
    {{-- Heading & sapaan --}}
    <div class="mb-6">
        <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
            Selamat Datang, {{ Auth::user()->nama ?? (Auth::user()->name ?? 'Pengguna') }}👋
        </h2>
        <p class="text-gray-500 mt-1">Senang melihat Anda kembali di sistem e-Presensi</p>
    </div>

    {{-- GRID KARTU --}}
    <div class="grid gap-6 sm:grid-cols-2 xl:grid-cols-4">
        {{-- Presensi --}}
        <a href="{{ route('user.presensi') }}"
            class="group relative overflow-hidden rounded-2xl border border-emerald-300 bg-gradient-to-b from-white to-emerald-50/40 p-6 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-emerald-200">
            <div
                class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity bg-[radial-gradient(ellipse_at_top,rgba(16,185,129,.12),transparent_60%)]">
            </div>
            <div class="flex flex-col items-center text-center gap-4">
                <img src="{{ asset('images/calendar_emerald_icon.svg') }}" class="h-16 w-16" alt="">
                <div>
                    <div class="text-2xl font-bold text-gray-900">Presensi</div>
                    <p class="text-gray-500 text-sm mt-1">Check-in / Check-out harian</p>
                </div>
            </div>
        </a>

        {{-- Izin --}}
        <a href="{{ route('user.pengajuan_izin') }}"
            class="group relative overflow-hidden rounded-2xl border border-sky-300 bg-gradient-to-b from-white to-sky-50/40 p-6 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-sky-200">
            <div
                class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity bg-[radial-gradient(ellipse_at_top,rgba(56,189,248,.14),transparent_60%)]">
            </div>
            <div class="flex flex-col items-center text-center gap-4">
                <img src="{{ asset('images/calendar_blue_icon.svg') }}" class="h-16 w-16" alt="">
                <div>
                    <div class="text-2xl font-bold text-gray-900">Izin</div>
                    <p class="text-gray-500 text-sm mt-1">Ajukan izin / sakit / perjalanan kerja</p>
                </div>
            </div>
        </a>

        {{-- Riwayat Absen --}}
        <a href="{{ route('user.riwayat_presensi') }}"
            class="group relative overflow-hidden rounded-2xl border border-amber-300 bg-gradient-to-b from-white to-amber-50/40 p-6 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-amber-200">
            <div
                class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity bg-[radial-gradient(ellipse_at_top,rgba(245,158,11,.14),transparent_60%)]">
            </div>
            <div class="flex flex-col items-center text-center gap-4">
                <img src="{{ asset('images/calendar_orange_icon.svg') }}" class="h-16 w-16" alt="">
                <div>
                    <div class="text-2xl font-bold text-gray-900">Riwayat Presensi</div>
                    <p class="text-gray-500 text-sm mt-1">Lihat catatan kehadiran</p>
                </div>
            </div>
        </a>

        {{-- Riwayat Izin --}}
        <a href="{{ route('user.riwayat_izin') }}"
            class="group relative overflow-hidden rounded-2xl border border-rose-300 bg-gradient-to-b from-white to-rose-50/40 p-6 shadow-sm transition-all hover:-translate-y-0.5 hover:shadow-md focus:outline-none focus:ring-4 focus:ring-rose-200">
            <div
                class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity bg-[radial-gradient(ellipse_at_top,rgba(244,63,94,.12),transparent_60%)]">
            </div>
            <div class="flex flex-col items-center text-center gap-4">
                <img src="{{ asset('images/calendar_red_icon.svg') }}" class="h-16 w-16" alt="">
                <div>
                    <div class="text-2xl font-bold text-gray-900">Riwayat Izin</div>
                    <p class="text-gray-500 text-sm mt-1">Pantau status & histori</p>
                </div>
            </div>
        </a>
    </div>
@endsection
