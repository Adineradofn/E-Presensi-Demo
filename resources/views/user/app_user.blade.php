<!DOCTYPE html>
<html lang="id">

<head>
    @livewireStyles
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'E-Presensi' }}</title>

    {{-- CSRF untuk AJAX --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- HANYA CSS di <head> --}}
    @vite(['resources/css/app.css'])

    <link rel="icon" href="{{ asset('images/logo_perusahaan.svg') }}" type="image/png">

    <style>
        [x-cloak] {
            display: none !important
        }
    </style>
</head>

<body class="min-h-screen bg-gray-50">

    {{-- Sidebar (mobile+desktop) --}}
    @include('user.component.sidebar_user')

    {{-- Kolom kanan: topbar + konten; padding-left otomatis saat >= md --}}
    <div class="min-h-screen md:pl-64 flex flex-col">
        {{-- Topbar (persist supaya tidak re-render saat navigate) --}}
        @persist('topbar_user')
            @include('user.component.topbar_user')
        @endpersist

        {{-- Konten halaman --}}
        <main class="p-4 sm:p-6">
            @yield('content')
            {{ $slot ?? '' }}
        </main>
    </div>

    {{-- MOUNT komponen Livewire yang berisi SEMUA modal SelfProfile (WAJIB ADA) --}}
    <livewire:shared.self-profile />

    {{-- Livewire Scripts WAJIB sebelum script lain --}}
    @livewireScripts

    {{-- JS kustom setelah Livewire (jika ada) --}}
    @vite(['resources/js/app.js'])

    @stack('scripts')
    @include('sweetalert::alert')
</body>

</html>
