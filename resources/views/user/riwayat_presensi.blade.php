{{-- resources/views/user/riwayat_presensi.blade.php --}}
@extends('user.app_user')

@section('content')
    @php
        // Ambil bulan aktif dari controller; fallback ke query param / sekarang
        $month = $current_month ?? request('month', now()->format('Y-m'));

        // Map warna status (badge + ring untuk kontras yang lebih baik)
        $statusStyles = [
            'hadir' => 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200',
            'terlambat' => 'bg-orange-100 text-orange-700 ring-1 ring-orange-200',
            'izin' => 'bg-sky-100 text-sky-700 ring-1 ring-sky-200',
            'sakit' => 'bg-violet-100 text-violet-700 ring-1 ring-violet-200',
            'tidak hadir' => 'bg-red-100 text-red-700 ring-1 ring-red-200',
            '-' => 'bg-gray-100 text-gray-700 ring-1 ring-gray-200',
        ];
    @endphp

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
        {{-- Header --}}
        <div class="mb-6">
            <div class="flex items-center gap-3">
                <div
                    class="h-10 w-10 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 text-white
                  flex items-center justify-center shadow-md">
                    {{-- calendar icon --}}
                    <img src="{{ asset('images/riwayat_presensi_white_icon.svg') }}" class="h-8 w-8" alt="">
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900">Riwayat Presensi</h1>
                </div>
            </div>
        </div>

        {{-- Filter bulan --}}
        <form method="GET" class="mb-5 flex flex-col sm:flex-row items-stretch gap-2 sm:gap-3">
            <label for="month" class="sr-only">Pilih bulan</label>
            <div class="relative w-full sm:w-64">
                <input id="month" type="month" name="month" value="{{ $month }}"
                    class="w-full rounded-xl border border-gray-300 pl-10 pr-4 py-2.5 text-sm
               focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500
               bg-white/80 backdrop-blur transition">
            </div>

            <div class="flex gap-2">
                <button type="submit"
                    class="inline-flex items-center justify-center rounded-xl border border-emerald-600 bg-emerald-600
               text-white px-4 py-2.5 text-sm font-medium shadow-sm hover:bg-emerald-700
               focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500
               transition">
                    Tampilkan
                </button>

            </div>
        </form>

        @php
            // Normalisasi data (format string aman untuk tampilan)
            $normalized = $items->map(function ($row) {
                $tgl = $row->tanggal ? $row->tanggal->format('Y-m-d') : '-';
                $jm = $row->jam_masuk ? $row->jam_masuk->format('H:i') : '-';
                $jp = $row->jam_pulang ? $row->jam_pulang->format('H:i') : '-';
                $st = $row->status ?? '-';
                return compact('tgl', 'jm', 'jp', 'st');
            });
        @endphp

        {{-- Empty state --}}
        @if ($normalized->isEmpty())
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center shadow-sm">
                <div class="mx-auto mb-3 h-12 w-12 rounded-2xl bg-gray-100 flex items-center justify-center">
                    <img src="{{ asset('images/clock_icon.svg') }}" class="h-8 w-8" alt="">
                </div>
                <p class="text-gray-900 font-medium">Belum ada riwayat pada bulan ini.</p>
                <p class="text-gray-500 text-sm">Silakan pilih bulan lain atau periksa kembali data presensi.</p>
            </div>
        @else
            {{-- LIST KARTU (Mobile) --}}
            <div class="grid grid-cols-1 gap-3 sm:hidden">
                @foreach ($normalized as $row)
                    @php
                        $badge = $statusStyles[strtolower($row['st'])] ?? $statusStyles['-'];
                    @endphp
                    <article class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm hover:shadow-md transition">
                        <div class="flex items-center justify-between gap-2">
                            <div class="flex items-center gap-2">
                                <span
                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-[10px] font-semibold
                           uppercase tracking-wide {{ $badge }}">
                                    {{ ucfirst($row['st']) }}
                                </span>
                            </div>
                            <time datetime="{{ $row['tgl'] }}" class="text-xs text-gray-500">
                                {{ $row['tgl'] }}
                            </time>
                        </div>

                        <dl class="mt-3 grid grid-cols-2 gap-3">
                            <div class="rounded-xl bg-gray-50 p-3">
                                <dt class="text-[11px] text-gray-500">Jam Masuk</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $row['jm'] }}</dd>
                            </div>
                            <div class="rounded-xl bg-gray-50 p-3">
                                <dt class="text-[11px] text-gray-500">Jam Pulang</dt>
                                <dd class="text-sm font-medium text-gray-900">{{ $row['jp'] }}</dd>
                            </div>
                        </dl>
                    </article>
                @endforeach
            </div>

            {{-- TABEL (Tablet & Desktop) --}}
            <div class="hidden sm:block overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead class="bg-gray-50 text-gray-700 sticky top-0 z-10">
                            <tr class="text-sm">
                                <th scope="col" class="px-5 py-3 font-semibold">Tanggal</th>
                                <th scope="col" class="px-5 py-3 font-semibold">Jam Masuk</th>
                                <th scope="col" class="px-5 py-3 font-semibold">Jam Pulang</th>
                                <th scope="col" class="px-5 py-3 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @foreach ($normalized as $row)
                                @php
                                    $badge = $statusStyles[strtolower($row['st'])] ?? $statusStyles['-'];
                                @endphp
                                <tr class="odd:bg-white even:bg-gray-50 hover:bg-emerald-50/40 transition">
                                    <td class="px-5 py-3 text-gray-800">
                                        <time datetime="{{ $row['tgl'] }}">{{ $row['tgl'] }}</time>
                                    </td>
                                    <td class="px-5 py-3 text-gray-800">{{ $row['jm'] }}</td>
                                    <td class="px-5 py-3 text-gray-800">{{ $row['jp'] }}</td>
                                    <td class="px-5 py-3">
                                        <span
                                            class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold {{ $badge }}">
                                            <span class="h-1.5 w-1.5 rounded-full bg-current/70"></span>
                                            {{ ucfirst($row['st']) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagination (kalau $items paginator) --}}
            @if (method_exists($items, 'links'))
                <div class="mt-4 flex justify-center">
                    {{ $items->onEachSide(1)->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection
