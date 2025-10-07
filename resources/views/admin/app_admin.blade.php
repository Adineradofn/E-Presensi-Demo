<!DOCTYPE html>
<html lang="id">

<head>
    {{-- Stylesheet Livewire (otomatis memasukkan CSS yang diperlukan oleh Livewire) --}}
    @livewireStyles

    <!-- Metadata dasar dokumen -->
    <meta charset="UTF-8">
    <!-- Pastikan layout responsif pada perangkat mobile -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Judul halaman; fallback ke 'E-Presensi' jika $title tidak diset -->
    <title>{{ $title ?? 'E-Presensi' }}</title>

    <!-- Token CSRF untuk proteksi form & request AJAX -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Load asset via Vite: Tailwind/stylesheet dan bundel JavaScript aplikasi --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="icon" href="{{ asset('images/logo_perusahaan.svg') }}" type="image/png">

    {{-- x-cloak (Alpine) menyembunyikan elemen sampai Alpine siap, agar tidak “kedip”/FOUT --}}
    <style>
        [x-cloak] {
            display: none !important
        }
    </style>
</head>

<body class="min-h-screen bg-gray-50 flex">
    {{-- Sidebar admin (komponen Blade terpisah) --}}
    @include('admin.component.sidebar')

    <!-- Wrapper konten utama (kolom kanan) -->
    <div class="flex-1 min-w-0 flex flex-col md:pl-64">
        {{-- Topbar (header) --}}
        @include('admin.component.topbar')

        {{-- Komponen Livewire untuk modal/profil diri (dipasang global agar siap dipanggil dari mana saja) --}}
        @livewire('shared.self-profile')

        <!-- Area konten halaman -->
        <main class="p-6">
            {{-- Slot (untuk komponen yang menggunakan <x-layout>) --}}
            {{ $slot ?? '' }}
            {{-- Section konten (untuk view yang @extends layout ini) --}}
            @yield('content')
        </main>
    </div>

    {{-- Script Livewire (harus di akhir sebelum penutup body agar DOM sudah siap) --}}
    @livewireScripts

    <!-- Helper: auto-scroll ke error pertama di dalam modal (create/edit)
         - Mencari elemen error (data-error / .text-red-600 / [aria-invalid="true"])
         - Scroll ke posisi error agar user langsung melihat pesan yang relevan -->
    <script>
        (function() {
            function scrollModal(modalId, areaName) {
                requestAnimationFrame(() => {
                    const modal = document.getElementById(modalId);
                    if (!modal) return;
                    const area = modal.querySelector(`[data-scroll-area="${areaName}"]`);
                    if (!area) return;

                    const firstError =
                        area.querySelector('[data-error]') ||
                        area.querySelector('.text-red-600') ||
                        area.querySelector('[aria-invalid="true"]');

                    if (firstError) {
                        const y = firstError.getBoundingClientRect().top -
                            area.getBoundingClientRect().top +
                            area.scrollTop - 16; // offset 16px untuk memberi ruang atas
                        try {
                            area.scrollTo({
                                top: Math.max(0, y),
                                behavior: 'smooth'
                            });
                        } catch (_) {
                            area.scrollTop = Math.max(0, y);
                        }
                    } else {
                        // Jika tidak ada error spesifik, scroll ke atas area
                        try {
                            area.scrollTo({
                                top: 0,
                                behavior: 'smooth'
                            });
                        } catch (_) {
                            area.scrollTop = 0;
                        }
                    }
                });
            }

            // Dengarkan event kustom dari Livewire/JS untuk masing-masing form
            window.addEventListener('create-form-has-errors', () => scrollModal('modalCreate', 'create'));
            window.addEventListener('edit-form-has-errors', () => scrollModal('modalEdit', 'edit'));
        })();
    </script>

    {{-- Tempat menumpuk script tambahan dari view anak (@push('scripts')) --}}
    @stack('scripts')

    {{-- Render flash alert (SweetAlert2) bila ada session flash dari backend.
        Catatan: partial ini hanya untuk menampilkan, bukan sumber CDN utama. --}}
    @include('sweetalert::alert')
</body>

</html>
