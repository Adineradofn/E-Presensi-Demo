<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $title ?? 'E-Absensi' }}</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="//unpkg.com/alpinejs"></script>
</head>
<body class="min-h-screen bg-gray-50 flex">
  
  {{-- Kolom kanan: topbar + konten --}}
  <div class="flex-1 min-w-0 flex flex-col">
    {{-- Topbar (penuh lebar kolom kanan) --}}
    @include('mode.component.topbar')

    {{-- Konten halaman --}}
    <main class="p-6">
      {{ $slot ?? '' }}
      @yield('content')
    </main>
  </div>

</body>
</html>
