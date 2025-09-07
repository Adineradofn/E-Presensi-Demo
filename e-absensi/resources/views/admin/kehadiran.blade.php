@extends('admin.app-admin')

@section('content')
@php
  $mode = request('mode', 'harian'); // harian|bulanan|tahunan
@endphp

<div class="p-4">
  <h2 class="text-xl sm:text-2xl font-bold mb-4">Data Kehadiran</h2>

  <!-- Filter Mode + Input Rentang Waktu -->
  <div class="mb-4 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <!-- Toggle Mode -->
    <div class="flex w-full sm:w-auto rounded-lg border border-gray-200 overflow-hidden">
      <a href="{{ request()->fullUrlWithQuery(['mode' => 'harian']) }}"
         class="px-4 py-2 text-sm font-medium {{ $mode==='harian' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
        Hari
      </a>
      <a href="{{ request()->fullUrlWithQuery(['mode' => 'bulanan']) }}"
         class="px-4 py-2 text-sm font-medium border-l border-gray-200 {{ $mode==='bulanan' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
        Bulan
      </a>
      <a href="{{ request()->fullUrlWithQuery(['mode' => 'tahunan']) }}"
         class="px-4 py-2 text-sm font-medium border-l border-gray-200 {{ $mode==='tahunan' ? 'bg-emerald-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50' }}">
        Tahun
      </a>
    </div>

    <!-- Form Rentang Waktu (GET) -->
    <form method="GET" class="flex items-center gap-2">
      <input type="hidden" name="mode" value="{{ $mode }}">
      @if($mode==='harian')
        <input type="date" name="date" value="{{ request('date') }}"
               class="rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
      @elseif($mode==='bulanan')
        <input type="month" name="month" value="{{ request('month') }}"
               class="rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
      @else
        <select name="year"
                class="rounded-lg border border-gray-300 px-3 py-2 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
          @for($y = now()->year; $y >= now()->year - 5; $y--)
            <option value="{{ $y }}" {{ (string)$y === request('year') ? 'selected' : '' }}>{{ $y }}</option>
          @endfor
        </select>
      @endif
      <button type="submit"
              class="px-4 py-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm">
        Terapkan
      </button>
    </form>
  </div>

  <!-- Tabel -->
  <div class="overflow-x-auto bg-white rounded-xl border border-gray-200 shadow-sm">
    <table class="min-w-full whitespace-nowrap">
      <thead class="bg-gray-50 text-left text-gray-700">
        <tr>
          <th class="px-4 py-3 text-sm font-semibold">ID</th>
          <th class="px-4 py-3 text-sm font-semibold">Nama</th>
          <th class="px-4 py-3 text-sm font-semibold">Divisi</th>
          <th class="px-4 py-3 text-sm font-semibold">Jabatan</th>
          <th class="px-4 py-3 text-sm font-semibold">Jam Masuk</th>
          <th class="px-4 py-3 text-sm font-semibold">Jam Pulang</th>
          <th class="px-4 py-3 text-sm font-semibold">Tanggal</th> <!-- kolom baru -->
          <th class="px-4 py-3 text-sm font-semibold">Status Kehadiran</th>
          <th class="px-4 py-3 text-sm font-semibold text-center">Aksi</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-gray-100">
        <!-- Dummy Row 1 -->
        <tr class="hover:bg-gray-50">
          <td class="px-4 py-3 text-sm text-gray-700">1</td>
          <td class="px-4 py-3 text-sm font-medium text-gray-900">Fulan</td>
          <td class="px-4 py-3 text-sm text-gray-700">IT</td>
          <td class="px-4 py-3 text-sm text-gray-700">Staff</td>
          <td class="px-4 py-3 text-sm text-gray-700">08:00</td>
          <td class="px-4 py-3 text-sm text-gray-700">17:00</td>
          <td class="px-4 py-3 text-sm text-gray-700">2025-08-24</td>
          <td class="px-4 py-3 text-sm">
            <span class="px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
              Hadir
            </span>
          </td>
          <td class="px-4 py-3">
            <div class="flex items-center justify-center">
              <a href="#"
                 class="px-3 py-1.5 rounded-lg border border-emerald-600 text-emerald-700 hover:bg-emerald-50 text-sm">
                Edit
              </a>
            </div>
          </td>
        </tr>

        <!-- Dummy Row 2 -->
        <tr class="hover:bg-gray-50">
          <td class="px-4 py-3 text-sm text-gray-700">2</td>
          <td class="px-4 py-3 text-sm font-medium text-gray-900">Ahmad</td>
          <td class="px-4 py-3 text-sm text-gray-700">HRD</td>
          <td class="px-4 py-3 text-sm text-gray-700">Manager</td>
          <td class="px-4 py-3 text-sm text-gray-700">10:10</td>
          <td class="px-4 py-3 text-sm text-gray-700">17:00</td>
          <td class="px-4 py-3 text-sm text-gray-700">2025-08-24</td>
          <td class="px-4 py-3 text-sm">
            <span class="px-2 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-700">
              Terlambat
            </span>
          </td>
          <td class="px-4 py-3">
            <div class="flex items-center justify-center">
              <a href="#"
                 class="px-3 py-1.5 rounded-lg border border-emerald-600 text-emerald-700 hover:bg-emerald-50 text-sm">
                Edit
              </a>
            </div>
          </td>
        </tr>

        <!-- Dummy Row 3 -->
        <tr class="hover:bg-gray-50">
          <td class="px-4 py-3 text-sm text-gray-700">3</td>
          <td class="px-4 py-3 text-sm font-medium text-gray-900">Siti</td>
          <td class="px-4 py-3 text-sm text-gray-700">Finance</td>
          <td class="px-4 py-3 text-sm text-gray-700">Staff</td>
          <td class="px-4 py-3 text-sm text-gray-700">-</td>
          <td class="px-4 py-3 text-sm text-gray-700">-</td>
          <td class="px-4 py-3 text-sm text-gray-700">2025-08-24</td>
          <td class="px-4 py-3 text-sm">
            <span class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">
              Tidak Hadir
            </span>
          </td>
          <td class="px-4 py-3">
            <div class="flex items-center justify-center">
              <a href="#"
                 class="px-3 py-1.5 rounded-lg border border-emerald-600 text-emerald-700 hover:bg-emerald-50 text-sm">
                Edit
              </a>
            </div>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>
@endsection
