<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'E-Presensi' }}</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Jika ingin tetap pakai CDN Alpine, pin versi dan hindari duplikasi dengan Vite --}}
    {{-- <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script> --}}

    <link rel="icon" href="{{ asset('images/logo_perusahaan.svg') }}" type="image/png">


    @stack('head')
</head>

<body class="min-h-screen bg-gray-50 antialiased font-sans text-gray-900">
    <main class="p-6 max-w-7xl mx-auto">
        @yield('content')
    </main>

    @stack('scripts')
</body>

</html>
