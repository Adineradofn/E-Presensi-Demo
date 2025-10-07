<!DOCTYPE html>
<html lang="id">
<head>
    {{-- ====== Meta dasar dokumen ====== --}}
    <meta charset="UTF-8" />
    {{-- Responsif di perangkat mobile --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    {{-- Judul halaman --}}
    <title>E-Presensi PT. ASA</title>

    {{-- Tailwind via Vite (CSS utama aplikasi) --}}
    @vite(['resources/css/app.css'])
    
    <link rel="icon" href="{{ asset('images/logo_perusahaan.svg') }}" type="image/png">
</head>

{{-- 
  - min-h-screen: konten setidaknya setinggi layar penuh
  - bg-gradient-to-br: latar gradasi lembut
  - flex + items-center + justify-center: form berada di tengah
  - p-4: beri ruang di pinggir pada layar kecil
--}}
<body class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-blue-50 flex items-center justify-center p-4">
    {{-- Wrapper lebar maksimum form agar tidak melebar di layar besar --}}
    <div class="w-full max-w-md">
        {{-- Kartu form login dengan efek blur & ring tipis --}}
        <div class="bg-white/90 backdrop-blur rounded-2xl shadow-xl ring-1 ring-gray-200 p-6 sm:p-8">
            {{-- Header/logo + sapaan --}}
            <div class="text-center mb-6">
                <div class="mx-auto h-28 w-28 rounded-full ring-4 ring-emerald-100 grid place-items-center mb-3 bg-emerald-50">
                    {{-- Logo perusahaan (pakai object-cover agar proporsional) --}}
                    <img src="{{ asset('images/logo_perusahaan.svg') }}" alt="Logo Perusahaan" class="h-26 w-26 rounded-full object-cover">
                </div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900">Selamat Datang</h1>
                <p class="text-gray-600 mt-1">Silakan login untuk melakukan absen</p>
            </div>

            {{-- 
              Form login:
              - method POST ke route('login.post')
              - @csrf untuk proteksi CSRF
              - novalidate: biar pesan validasi dari server/Laravel yang tampil (bukan default browser)
            --}}
            <form id="loginForm" method="POST" action="{{ route('login.post') }}" novalidate class="space-y-5">
                @csrf

                @php
                    // Flag error per field untuk mengatur kelas border fokus
                    $nikInvalid = $errors->has('nik');
                    $pwdInvalid = $errors->has('password');
                @endphp

                {{-- Pesan ERROR GLOBAL (contoh: kredensial salah); tampil di atas field NIK --}}
                @if ($errors->has('auth'))
                    <div class="flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-700"
                         role="alert" aria-live="polite">
                        <img src="{{ asset('images/alert_icon.svg') }}" class="h-5 w-5" alt="">
                        <span>{{ $errors->first('auth') }}</span>
                    </div>
                @endif

                {{-- ====== Field NIK ====== --}}
                <div>
                    <label for="nik" class="block text-sm font-medium text-gray-700">NIK</label>
                    <div class="relative mt-1">
                        {{-- Ikon di kiri input (non-interaktif) --}}
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <img src="{{ asset('images/user_grey_icon.svg') }}" class="h-5 w-5" alt="">
                        </span>

                        {{-- 
                          - autocomplete="username": bantu pengisian otomatis
                          - old('nik'): isi ulang nilai ketika validasi gagal
                          - @class: switch kelas menurut status error
                        --}}
                        <input
                            type="text"
                            id="nik"
                            name="nik"
                            required
                            autocomplete="username"
                            value="{{ old('nik') }}"
                            placeholder="Masukkan NIK"
                            @class([
                                'block w-full rounded-xl border px-10 py-2.5 text-gray-900 placeholder-gray-400 focus:outline-none',
                                'border-gray-300 focus:border-emerald-500 focus:ring-emerald-500' => !$nikInvalid,
                                'border-red-300 focus:border-red-500 focus:ring-red-500' => $nikInvalid,
                            ])>
                    </div>
                    {{-- Pesan error NIK (aksesibel) --}}
                    @error('nik')
                        <p class="mt-1 text-sm text-red-600" role="alert" aria-live="polite">{{ $message }}</p>
                    @enderror
                </div>

                {{-- ====== Field Password + tombol toggle (show/hide) ====== --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <div class="relative mt-1" data-pass-toggle>
                        {{-- 
                          - autocomplete="current-password": bantu pengisian otomatis
                          - Kelas border dinamis sesuai status error
                        --}}
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="Masukkan password"
                            @class([
                                'block w-full rounded-xl border pr-10 px-3 py-2.5 text-gray-900 placeholder-gray-400 focus:outline-none',
                                'border-gray-300 focus:border-emerald-500 focus:ring-emerald-500' => !$pwdInvalid,
                                'border-red-300 focus:border-red-500 focus:ring-red-500' => $pwdInvalid,
                            ])>

                        {{-- 
                          Tombol toggle:
                          - Ganti type input antara password/text
                          - ARIA: label & pressed disesuaikan agar aksesibel
                          - data-icon-show/hide untuk sumber ikon
                        --}}
                        <button
                            type="button"
                            id="passwordToggle"
                            class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-500 hover:text-gray-700"
                            aria-label="Tampilkan password"
                            aria-pressed="false"
                            data-icon-show="{{ asset('images/eye_icon.svg') }}"
                            data-icon-hide="{{ asset('images/eye_hide_icon.svg') }}">
                            <img id="passwordIcon" class="h-5 w-5"
                                 src="{{ asset('images/eye_icon.svg') }}"
                                 alt="Tampilkan password">
                        </button>
                    </div>
                    {{-- Pesan error Password --}}
                    @error('password')
                        <p class="mt-1 text-sm text-red-600" role="alert" aria-live="polite">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tombol kirim form --}}
                <button
                    type="submit"
                    class="w-full inline-flex items-center justify-center gap-2 rounded-xl bg-emerald-600 text-white px-4 py-3 font-semibold shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 transition">
                    Login
                </button>
            </form>

            {{-- Footer kecil --}}
            <p class="mt-6 text-center text-xs text-gray-500">
                © {{ date('Y') }} PT. Anugerah Sawit Andalan. All rights reserved.
            </p>
        </div>
    </div>

    {{-- 
      Toggle password IMG:
      - AUTO-BIND untuk semua wrapper dengan atribut [data-pass-toggle]
      - Tidak butuh library tambahan
      - Menjaga fokus tetap di input setelah toggle untuk UX yang baik
    --}}
    <script>
        (function () {
            function bindPasswordToggle(input, button, img) {
                if (!input || !button || !img) return;

                const showSrc = button.dataset.iconShow;
                const hideSrc = button.dataset.iconHide;

                // Sinkronkan ikon & atribut ARIA dengan state saat ini
                function applyState() {
                    const isHidden = input.type === 'password';
                    img.src = isHidden ? showSrc : hideSrc;
                    const label = isHidden ? 'Tampilkan password' : 'Sembunyikan password';
                    img.alt = label;
                    button.setAttribute('aria-label', label);
                    button.setAttribute('aria-pressed', (!isHidden).toString());
                }

                // Inisialisasi ikon sesuai tipe input saat ini
                applyState();

                // Klik: toggle tipe input + perbarui ikon/ARIA
                button.addEventListener('click', () => {
                    input.type = (input.type === 'password') ? 'text' : 'password';
                    applyState();
                    // UX: tetap fokus di input setelah toggle
                    input.focus({ preventScroll: true });
                });
            }

            // AUTO-BIND: cari semua container yang menandai diri dengan data-pass-toggle
            document.querySelectorAll('[data-pass-toggle]').forEach(wrap => {
                const input  = wrap.querySelector('input[type="password"], input[data-pass-input]');
                const button = wrap.querySelector('button[data-icon-show][data-icon-hide]');
                const img    = button?.querySelector('img');
                if (input && button && img) bindPasswordToggle(input, button, img);
            });
        })();
    </script>
</body>
</html>
