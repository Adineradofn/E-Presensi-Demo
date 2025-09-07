@extends('admin.app-admin')

@section('content')
  <div class="p-4">
    <!-- Welcome Section -->
    <div class="mb-6">
      <h1 class="text-2xl sm:text-3xl font-bold text-gray-800">
        Selamat Datang, Fulan 👋
      </h1>
      <p class="text-gray-600 mt-1">Senang melihat Anda kembali di sistem e-Absensi.</p>
    </div>

    <!-- Dashboard Title -->
    <h2 class="text-xl sm:text-2xl font-bold mb-4 sm:mb-6">Dashboard</h2>

    <!-- Dashboard Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 sm:gap-6">
      
      <!-- Kehadiran Hari Ini -->
      <div class="bg-emerald-500 text-white rounded-2xl shadow-sm ring-1 ring-black/5 p-4 sm:p-6 flex items-center justify-between gap-4 min-w-0">
        <div class="min-w-0">
          <h3 class="text-base sm:text-lg font-semibold leading-tight">Kehadiran Hari Ini</h3>
          <p class="text-3xl sm:text-4xl font-bold mt-3 sm:mt-4 tabular-nums">120{{-- {{ $hadirHariIni }} --}}</p>
        </div>
        <div class="shrink-0 text-white/80">
          <!-- Icon User -->
          <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 sm:h-12 sm:w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A9.003 9.003 0 0112 15c2.21 0 4.21.804 5.879 2.137M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
        </div>
      </div>

      <!-- Terlambat -->
      <div class="bg-red-500 text-white rounded-2xl shadow-sm ring-1 ring-black/5 p-4 sm:p-6 flex items-center justify-between gap-4 min-w-0">
        <div class="min-w-0">
          <h3 class="text-base sm:text-lg font-semibold leading-tight">Terlambat</h3>
          <p class="text-3xl sm:text-4xl font-bold mt-3 sm:mt-4 tabular-nums">8{{-- {{ $terlambat }} --}}</p>
        </div>
        <div class="shrink-0 text-white/80">
          <!-- Icon Clock -->
          <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 sm:h-12 sm:w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
        </div>
      </div>

      <!-- Pengajuan Izin Pending -->
      <div class="bg-yellow-500 text-white rounded-2xl shadow-sm ring-1 ring-black/5 p-4 sm:p-6 flex items-center justify-between gap-4 min-w-0">
        <div class="min-w-0">
          <h3 class="text-base sm:text-lg font-semibold leading-tight">Izin Pending</h3>
          <p class="text-3xl sm:text-4xl font-bold mt-3 sm:mt-4 tabular-nums">5{{-- {{ $izinPending }} --}}</p>
        </div>
        <div class="shrink-0 text-white/80">
          <!-- Icon Document -->
          <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 sm:h-12 sm:w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m2 8H7a2 2 0 01-2-2V6a2 2 0 012-2h5l2 2h5a2 2 0 012 2v10a2 2 0 01-2 2z"/>
          </svg>
        </div>
      </div>

    </div>
  </div>
@endsection
