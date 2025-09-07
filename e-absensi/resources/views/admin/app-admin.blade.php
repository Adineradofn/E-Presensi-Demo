<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $title ?? 'E-Absensi' }}</title>

  {{-- CSRF untuk AJAX/fetch --}}
  <meta name="csrf-token" content="{{ csrf_token() }}">

  {{-- Tailwind & Alpine --}}
  <script src="https://cdn.tailwindcss.com"></script>
  <style>[x-cloak]{ display:none !important; }</style>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="min-h-screen bg-gray-50 flex">
  
  {{-- Sidebar (kiri) --}}
  @include('admin.component.sidebar')

  {{-- Kolom kanan: topbar + konten --}}
  <div class="flex-1 min-w-0 flex flex-col">
    {{-- Topbar --}}
    @include('admin.component.topbar')

    {{-- Konten halaman --}}
    <main class="p-6">
      {{-- Untuk komponen slot-based --}}
      {{ $slot ?? '' }}
      {{-- Untuk view yang extend layout --}}
      @yield('content')
    </main>
  </div>

  {{-- Tempatkan script halaman di bawah agar DOM sudah siap --}}
  @stack('scripts')
</body>
</html>
