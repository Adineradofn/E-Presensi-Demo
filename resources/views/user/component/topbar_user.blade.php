{{-- resources/views/layouts/partials/topbar.blade.php --}}
@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $user = Auth::user();

    // === HAK AKSES TOMBOL "PINDAH MODE" ===
    // Muncul hanya untuk admin & co-admin
    $canSwitchMode = $user && in_array($user->role ?? '', ['admin', 'co-admin'], true);

    // ===== Foto privat via controller (stream) =====
    $idKaryawan = $user->id_karyawan ?? ($user->id ?? null);
    $punyaFoto  = !empty($user->foto ?? null);

    // Pilih nama route yang tersedia agar tidak RouteNotFoundException
    $photoUrl = null;
    if ($idKaryawan && $punyaFoto) {
        if (Route::has('data.karyawan.foto')) {
            // ✅ key param HARUS 'karyawan'
            $photoUrl = route('data.karyawan.foto', ['karyawan' => $idKaryawan]); // user area
        } elseif (Route::has('admin.data.karyawan.foto')) {
            $photoUrl = route('admin.data.karyawan.foto', ['karyawan' => $idKaryawan]); // admin area
        }
    }

    // ✅ Pakai nama depan dari accessor; fallback: ambil kata pertama dari 'nama' / 'name'
    $displayName = 'User';
    if ($user) {
        $displayName = $user->first_name
            ?: Str::of($user->nama ?? ($user->name ?? 'User'))->squish()->explode(' ')->first();
    }

    // Nama & cache-buster foto
    $pfVer = session('pf_ver'); // contoh: now()->timestamp
@endphp

@once
    <style>
        [x-cloak] { display: none !important; }
    </style>
@endonce

<header class="sticky top-0 z-40 w-full h-16 bg-white/90 backdrop-blur border-b border-gray-200 shadow-sm">
    <div class="h-full px-4 sm:px-6 flex items-center justify-between">
        {{-- spacer kiri agar judul tetap center --}}
        <div class="w-10 sm:w-12"></div>

        {{-- Judul: 2 baris di mobile, 1 baris di ≥sm --}}
        <h1 class="absolute left-1/2 -translate-x-1/2 font-bold tracking-wide text-gray-800">
            <span class="sm:hidden block text-sm leading-tight text-center">
                <span class="block">E-PRESENSI</span>
                <span class="block">KARYAWAN</span>
            </span>
            <span class="hidden sm:inline text-base sm:text-lg">E-PRESENSI KARYAWAN</span>
        </h1>

        <div class="relative"
             x-data='{
               open:false,
               name: @json($displayName),
               photoUrl: @json($photoUrl),
               ver: @json($pfVer),

               get photoSrc(){ return this.photoUrl ? (this.photoUrl + (this.ver ? ("?v=" + this.ver) : "")) : null; },

               openEditProfile(){ window.dispatchEvent(new CustomEvent("self-profile:edit-open")); },
               openChangePassword(){ window.dispatchEvent(new CustomEvent("self-profile:password-open")); },

               init(){
                 window.addEventListener("profile-photo-updated", (e) => {
                   this.ver = (e.detail && e.detail.version) ? e.detail.version : Date.now();
                 });
                 window.addEventListener("self-profile:name-updated", (e) => {
                   if (e.detail && e.detail.name) {
                     const raw = String(e.detail.name).trim().replace(/\s+/gu, " ");
                     this.name = raw ? raw.split(" ")[0] : this.name; // ✅ tampilkan nama depan saja
                   }
                 });
               }
             }'>

            {{-- Tombol profil: di mobile hanya foto; nama & caret tampil di ≥sm --}}
            <button type="button"
                    @click="open = !open"
                    @keydown.escape.window="open = false"
                    class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white p-1.5 sm:px-3 sm:py-1.5 shadow-sm hover:bg-gray-50 focus:outline-none"
                    aria-haspopup="menu"
                    :aria-expanded="open.toString()"
                    aria-label="Menu profil">

                {{-- Avatar --}}
                <img :src="photoSrc" @if ($photoUrl) src="{{ $photoUrl }}" @endif
                     alt="User" class="h-8 w-8 rounded-full object-cover ring-1 ring-gray-200"
                     x-show="photoSrc" x-transition onerror="this.style.display='none';">
                {{-- Placeholder --}}
                <div x-show="!photoSrc"
                     class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center ring-1 ring-gray-200">
                    <img src="{{ asset('images/no_profile_icon.svg') }}" class="h-5 w-5" alt="No Profile Icon">
                </div>

                {{-- Nama: tampil di desktop, hilang di mobile --}}
                <span class="font-medium text-gray-800 max-w-[10rem] truncate hidden sm:inline"
                      x-text="name">{{ $displayName }}</span>

                {{-- Caret: sembunyikan di mobile --}}
                <img src="{{ asset('images/dropdown_icon.svg') }}" class="h-5 w-5 hidden sm:block" alt="dropdown icon">
            </button>

            {{-- Dropdown --}}
            <div x-cloak x-show="open"
                 @click.outside="open = false"
                 x-transition.opacity
                 x-transition.scale.origin.top.right
                 class="absolute right-0 mt-2 w-56 bg-white border border-gray-200 rounded-xl shadow-xl py-2 z-50"
                 role="menu">

                {{-- Header dropdown: nama tampil (termasuk mobile) --}}
                <div class="px-4 pb-2 pt-1 border-b border-gray-100">
                    <p class="text-sm font-semibold text-gray-900 truncate" x-text="name">{{ $displayName }}</p>
                </div>

                {{-- Tampil hanya untuk admin & co-admin --}}
                @if ($canSwitchMode)
                    <a href="{{ url('/') }}"
                       class="flex items-center gap-2 px-4 py-2 text-sm text-gray-800 hover:bg-gray-50"
                       role="menuitem">
                        <img src="{{ asset('images/change_icon.svg') }}" class="h-5 w-5" alt="change icon">
                        Pindah Mode
                    </a>
                    <div class="my-1 h-px bg-gray-100"></div>
                @endif

                <button type="button"
                        class="w-full flex items-center gap-2 px-4 py-2 text-left text-sm text-gray-800 hover:bg-gray-50"
                        @click.prevent="openEditProfile(); open=false" role="menuitem">
                    <img src="{{ asset('images/edit_dropdown_icon.svg') }}" class="h-5 w-5" alt="edit dropdown icon">
                    Ubah Profil
                </button>

                <button type="button"
                        class="w-full flex items-center gap-2 px-4 py-2 text-left text-sm text-gray-800 hover:bg-gray-50"
                        @click.prevent="openChangePassword(); open=false" role="menuitem">
                    <img src="{{ asset('images/password_dropdown_icon.svg') }}" class="h-5 w-5" alt="password dropdown icon">
                    Ganti Password
                </button>

                <div class="my-1 h-px bg-gray-100"></div>

                <form method="POST" action="{{ route('logout') }}" role="menuitem">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center gap-2 px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50">
                        <img src="{{ asset('images/logout_icon.svg') }}" class="h-5 w-5" alt="logout icon">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
