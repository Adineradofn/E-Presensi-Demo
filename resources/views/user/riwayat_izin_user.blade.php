{{-- resources/views/user/riwayat_izin_user.blade.php --}}
@extends('user.app_user')

@section('content')
    @php
        // Bulan aktif dari controller atau query ?month=YYYY-MM; fallback sekarang
        $month = $current_month ?? request('month', now()->format('Y-m'));

        // Style badge
        $jenisStyles = [
            'sakit' => 'bg-violet-100 text-violet-700 ring-1 ring-violet-200',
            'izin' => 'bg-sky-100 text-sky-700 ring-1 ring-sky-200',
            '-' => 'bg-gray-100 text-gray-700 ring-1 ring-gray-200',
        ];
        $statusStyles = [
            'pending' => 'bg-gray-100 text-gray-700 ring-1 ring-gray-200',
            'diterima' => 'bg-emerald-100 text-emerald-700 ring-1 ring-emerald-200',
            'ditolak' => 'bg-red-100 text-red-700 ring-1 ring-red-200',
            '-' => 'bg-gray-100 text-gray-700 ring-1 ring-gray-200',
        ];
    @endphp

    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 py-6">
        {{-- Header --}}
        <div class="mb-6">
            <div class="flex items-center gap-3">
                <div
                    class="h-10 w-10 rounded-2xl bg-gradient-to-br from-sky-500 to-emerald-500 text-white
                  flex items-center justify-center shadow-md">
                    {{-- clipboard-check icon --}}
                    <img src="{{ asset('images/riwayat_izin_white_icon.svg') }}" class="h-8 w-8" alt="">
                </div>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900">Riwayat Izin</h1>
                </div>
            </div>
        </div>

        {{-- Flash message --}}
        @if (session('success'))
            <div role="status" aria-live="polite"
                class="mb-4 p-3 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200">
                {{ session('success') }}
            </div>
        @endif
        @if ($errors->any())
            <div role="alert" aria-live="assertive"
                class="mb-4 p-3 rounded-xl bg-red-50 text-red-700 border border-red-200">
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $e)
                        <li>{{ $e }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

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
               focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-500 transition">
                    Tampilkan
                </button>

            </div>
        </form>

        @php
            // Ambil koleksi dari paginator/collection lalu normalisasi untuk tampilan
            $source = method_exists($items, 'getCollection') ? $items->getCollection() : collect($items);
            $normalized = $source->map(function ($row) {
                $pengajuan = $row->tanggal_pengajuan ? $row->tanggal_pengajuan->format('Y-m-d') : '-';
                $mulai = $row->tanggal_mulai ? $row->tanggal_mulai->format('Y-m-d') : '-';
                $selesai = $row->tanggal_selesai ? $row->tanggal_selesai->format('Y-m-d') : '-';
                $jenis = $row->jenis ?? '-'; // izin | sakit
                $alasan = $row->alasan ?: '-';
                $status = $row->status ?? '-'; // pending | diterima | ditolak
                return compact('pengajuan', 'mulai', 'selesai', 'jenis', 'alasan', 'status');
            });
        @endphp

        {{-- Empty state --}}
        @if ($normalized->isEmpty())
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-10 text-center shadow-sm">
                <div class="mx-auto mb-3 h-12 w-12 rounded-2xl bg-gray-100 flex items-center justify-center">
                    <img src="{{ asset('images/clock_icon.svg') }}" class="h-8 w-8" alt="">
                </div>
                <p class="text-gray-900 font-medium">Belum ada pengajuan izin.</p>
                <p class="text-gray-500 text-sm">Silakan pilih bulan lain atau ajukan izin baru.</p>
            </div>
        @else
            {{-- LIST KARTU (Mobile) — rapi & nyaman dibaca --}}
            <div class="grid grid-cols-1 gap-3 sm:hidden">
                @foreach ($normalized as $row)
                    @php
                        $jenisClass = $jenisStyles[strtolower($row['jenis'])] ?? $jenisStyles['-'];
                        $statusClass = $statusStyles[strtolower($row['status'])] ?? $statusStyles['-'];
                    @endphp

                    <article class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm hover:shadow-md transition">
                        {{-- Header: tanggal & status --}}
                        <header class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <time datetime="{{ $row['pengajuan'] }}"
                                    class="block text-base font-semibold leading-6 text-gray-900">
                                    {{ $row['pengajuan'] }}
                                </time>
                                <p class="text-xs leading-5 text-gray-500">Tanggal pengajuan</p>
                            </div>
                            <span
                                class="shrink-0 inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $statusClass }}">
                                {{ ucfirst($row['status']) }}
                            </span>
                        </header>

                        {{-- Meta ringkas & rata baseline --}}
                        <ul class="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-gray-600">
                            <li class="flex items-center gap-1.5">
                                {{-- jenis --}}
                               <img src="{{ asset('images/info_icon.svg') }}" class="h-5 w-5" alt="">
                                <span class="font-medium text-gray-700">{{ ucfirst($row['jenis']) }}</span>
                            </li>
                            <li class="flex items-center gap-1.5">
                                {{-- periode --}}
                               <img src="{{ asset('images/calendar_dark_grey_icon.svg') }}" class="h-5 w-5" alt="">
                                <span>{{ $row['mulai'] }}&nbsp;–&nbsp;{{ $row['selesai'] }}</span>
                            </li>
                        </ul>

                        {{-- Alasan: blok teks rapi --}}
                        <div class="mt-3 border-t border-gray-100 pt-3">
                            <p class="text-sm leading-6 text-gray-700 line-clamp-4">
                                {{ $row['alasan'] }}
                            </p>
                        </div>
                    </article>
                @endforeach
            </div>

            {{-- TABEL (Tablet & Desktop) --}}
            <div class="hidden sm:block overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left">
                        <thead class="bg-gray-50 text-gray-700 sticky top-0 z-10">
                            <tr class="text-sm">
                                <th scope="col" class="px-5 py-3 font-semibold">Tanggal Pengajuan</th>
                                <th scope="col" class="px-5 py-3 font-semibold">Tanggal Mulai</th>
                                <th scope="col" class="px-5 py-3 font-semibold">Tanggal Selesai</th>
                                <th scope="col" class="px-5 py-3 font-semibold">Status Izin</th>
                                <th scope="col" class="px-5 py-3 font-semibold">Alasan</th>
                                <th scope="col" class="px-5 py-3 font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-sm">
                            @foreach ($normalized as $row)
                                @php
                                    $jenisClass = $jenisStyles[strtolower($row['jenis'])] ?? $jenisStyles['-'];
                                    $statusClass = $statusStyles[strtolower($row['status'])] ?? $statusStyles['-'];
                                @endphp
                                <tr class="odd:bg-white even:bg-gray-50 hover:bg-sky-50/50 transition">
                                    <td class="px-5 py-3 text-gray-800">
                                        <time datetime="{{ $row['pengajuan'] }}">{{ $row['pengajuan'] }}</time>
                                    </td>
                                    <td class="px-5 py-3 text-gray-800">{{ $row['mulai'] }}</td>
                                    <td class="px-5 py-3 text-gray-800">{{ $row['selesai'] }}</td>
                                    <td class="px-5 py-3">
                                        <span
                                            class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold {{ $jenisClass }}">
                                            {{ ucfirst($row['jenis']) }}
                                        </span>
                                    </td>
                                    <td class="px-5 py-3 text-gray-700 max-w-[28rem]">
                                        <span class="line-clamp-2 sm:line-clamp-3">{{ $row['alasan'] }}</span>
                                    </td>
                                    <td class="px-5 py-3">
                                        <span
                                            class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                            {{ ucfirst($row['status']) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagination (jika $items paginator) --}}
            @if (method_exists($items, 'links'))
                <div class="mt-4 flex justify-center">
                    {{ $items->onEachSide(1)->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection
