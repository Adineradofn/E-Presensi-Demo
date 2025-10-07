{{-- resources/views/admin/mode/mode.blade.php
     Halaman pemilihan mode/peran dengan latar FULL SCREEN (gradient + pola titik).
     Catatan:
     - Latar dibuat "fixed inset-0" agar selalu menutupi viewport, bahkan saat konten di-scroll.
     - Z-index negatif (-z-10) memastikan latar berada di belakang seluruh konten.
--}}

@extends('admin.mode.app')

@section('content')
    <div class="relative min-h-screen w-full overflow-hidden">
        {{-- ====================== LATAR BELAKANG FULL SCREEN ======================
             • Layer 1: gradasi lembut (fixed + inset-0 → full viewport)
             • Layer 2: pola titik halus (opacity kecil, pointer-events-none agar tak mengganggu interaksi)
             • aria-hidden="true" agar diabaikan alat bantu (bukan konten bermakna)
        ------------------------------------------------------------------------}}
        <div
            class="fixed inset-0 -z-10 bg-gradient-to-br from-emerald-50 via-white to-sky-50"
            aria-hidden="true">
        </div>

        <div
            class="fixed inset-0 -z-10 opacity-[0.035] pointer-events-none select-none"
            style="background-image: radial-gradient(circle at 1px 1px, #000 1px, transparent 1px); background-size: 24px 24px;"
            aria-hidden="true">
        </div>

        {{-- ============================ KONTEN HALAMAN ============================
             • Wrapper konten dibuat "relative" agar berada di atas latar (-z-10).
             • max-w-4xl + center (mx-auto) untuk lebar ideal dan keterbacaan baik.
        ------------------------------------------------------------------------}}
        <div class="relative mx-auto max-w-4xl px-6 py-16">
            {{-- ============================== HEADING ==============================
                 • Badge kecil: memberi konteks halaman.
                 • Sapaan: ambil nama user, fallback "Admin".
                 • Deskripsi singkat: apa yang harus dilakukan pengguna.
            --------------------------------------------------------------------}}
            <div class="text-center mb-10">
                <span
                    class="inline-flex items-center gap-2 rounded-full border bg-white/60 backdrop-blur px-3 py-1 text-xs font-medium text-emerald-700 border-emerald-100 shadow-sm">
                    <img src="{{ asset('images/change_icon.svg') }}" class="h-8 w-8" alt="change icon">
                    Mode Pemilihan Halaman
                </span>

                <h2 class="mt-4 text-2xl sm:text-3xl font-bold tracking-tight text-gray-900">
                    Selamat datang <span class="text-emerald-700">{{ Auth::user()->nama ?? 'Admin' }}</span>
                </h2>

                <p class="mt-2 text-gray-600 max-w-2xl mx-auto">
                    Silakan pilih peran untuk melanjutkan ke dashboard yang sesuai.
                </p>
            </div>

            {{-- =========================== PILIHAN PERAN ==========================
                 • Grid responsif: 1 kolom (mobile) → 2 kolom (≥sm).
                 • Masing-masing kartu adalah tautan (a) ke route tujuan.
            --------------------------------------------------------------------}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 sm:gap-8">
                {{-- ---------------------------- KARYAWAN ----------------------------
                     • Aksen hijau (emerald).
                     • Efek blur + hover untuk rasa interaksi.
                     • Deskripsi ringkas fitur area karyawan.
                ------------------------------------------------------------------}}
                <a href="{{ route('user.home') }}"
                   class="group relative overflow-hidden rounded-2xl bg-white/70 backdrop-blur border border-emerald-100 shadow-sm transition duration-300 focus:outline-none focus-visible:ring-4 focus-visible:ring-emerald-300/60 hover:shadow-md">
                    {{-- Aksen latar (lingkaran blur) untuk depth; membesar saat hover --}}
                    <div class="absolute -right-10 -top-10 h-40 w-40 rounded-full bg-emerald-100/70 blur-2xl transition-all duration-500 group-hover:scale-110"></div>

                    {{-- Isi kartu: ikon + badge akses + judul + deskripsi + CTA kecil --}}
                    <div class="relative p-6 sm:p-8 flex h-full flex-col">
                        <div class="flex items-center justify-between">
                            <div class="inline-flex items-center justify-center rounded-xl border border-emerald-200 bg-emerald-50 p-3">
                                {{-- Ikon pengguna --}}
                                <img src="{{ asset('images/user_emerald_icon.svg') }}" class="h-5 w-5" alt="Karyawan">
                            </div>
                            <span class="text-xs font-medium text-emerald-700/80 bg-emerald-50 border border-emerald-100 rounded-full px-2 py-1">
                                Akses Karyawan
                            </span>
                        </div>

                        <div class="mt-6">
                            <h3 class="text-xl font-semibold text-gray-900">Karyawan</h3>
                            <p class="mt-1 text-sm leading-6 text-gray-600">
                                Masuk ke beranda karyawan untuk presensi, riwayat, dan pengajuan izin.
                            </p>
                        </div>

                        {{-- CTA sekunder: teks + ikon panah --}}
                        <div class="mt-8 flex items-center gap-2 text-sm font-semibold text-emerald-700">
                            Masuk
                            <img src="{{ asset('images/arrow_emerald_icon.svg') }}" class="h-5 w-5" alt="Panah">
                        </div>
                    </div>
                </a>

                {{-- ----------------------------- ADMIN ------------------------------
                     • Aksen biru (sky).
                     • Menjelaskan area admin (kelola data & pantau statistik).
                ------------------------------------------------------------------}}
                <a href="{{ route('admin.dashboard') }}"
                   class="group relative overflow-hidden rounded-2xl bg-white/70 backdrop-blur border border-sky-100 shadow-sm transition duration-300 focus:outline-none focus-visible:ring-4 focus-visible:ring-sky-300/60 hover:shadow-md">
                    {{-- Aksen latar (lingkaran blur) untuk depth; membesar saat hover --}}
                    <div class="absolute -left-10 -bottom-10 h-40 w-40 rounded-full bg-sky-100/70 blur-2xl transition-all duration-500 group-hover:scale-110"></div>

                    {{-- Isi kartu --}}
                    <div class="relative p-6 sm:p-8 flex h-full flex-col">
                        <div class="flex items-center justify-between">
                            <div class="inline-flex items-center justify-center rounded-xl border border-sky-200 bg-sky-50 p-3">
                                {{-- Ikon dashboard/admin --}}
                                <img src="{{ asset('images/admin_blue_icon.svg') }}" class="h-5 w-5" alt="Admin">
                            </div>
                            <span class="text-xs font-medium text-sky-700/80 bg-sky-50 border border-sky-100 rounded-full px-2 py-1">
                                Akses Admin
                            </span>
                        </div>

                        <div class="mt-6">
                            <h3 class="text-xl font-semibold text-gray-900">Admin</h3>
                            <p class="mt-1 text-sm leading-6 text-gray-600">
                                Kelola data karyawan, presensi, izin, dan pantau statistik operasional.
                            </p>
                        </div>

                        {{-- CTA sekunder --}}
                        <div class="mt-8 flex items-center gap-2 text-sm font-semibold text-sky-700">
                            Buka Dashboard
                            <img src="{{ asset('images/arrow_blue_icon.svg') }}" class="h-5 w-5" alt="Panah">
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </div>
@endsection
