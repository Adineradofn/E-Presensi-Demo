@php
    // ------------------------------------------------------------
    // Ambil user saat ini & siapkan flag utilitas (isAdmin) + URL admin
    // ------------------------------------------------------------
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $user = Auth::user();

    // isAdmin true jika:
    // - role = 'admin' ATAU
    // - ada properti boolean is_admin = true ATAU
    // - session('role') = 'admin'
    $isAdmin =
        $user && (($user->role ?? null) === 'admin' || ($user->is_admin ?? false) || session('role') === 'admin');

    // ------------------------------------------------------------
    // Foto profil privat via controller:
    // ------------------------------------------------------------
    $idKaryawan = $user->id_karyawan ?? ($user->id ?? null);
    $punyaFoto = !empty($user->foto ?? null);

    $photoUrl = null;
    if ($idKaryawan && $punyaFoto) {
        if (Route::has('admin.data.karyawan.foto')) {
            $photoUrl = route('admin.data.karyawan.foto', ['karyawan' => $idKaryawan]);
        } elseif (Route::has('data.karyawan.foto')) {
            $photoUrl = route('data.karyawan.foto', ['karyawan' => $idKaryawan]);
        }
    }

    // Nama tampilan (lengkap) & versi cache-buster foto
    $displayName = $user->nama ?? ($user->name ?? 'User');

    // Nama depan (gunakan accessor; fallback aman kalau model lain belum punya accessor)
    $displayFirstName = $user?->first_name;
    if (!$displayFirstName) {
        $displayFirstName = (string) Str::of($displayName)->trim()->before(' ');
    }
    if ($displayFirstName === '') {
        $displayFirstName = 'User';
    }

    $pfVer = session('pf_ver'); // contoh: now()->timestamp; dipakai sebagai ?v=...
@endphp

@once
    <style>
        /* x-cloak: bantu sembunyikan elemen sampai Alpine siap (hindari flashing) */
        [x-cloak] { display: none !important }
    </style>
@endonce

<header class="sticky top-0 z-40 w-full h-16 bg-white/90 backdrop-blur border-gray-200 shadow-sm">
    <div class="h-full px-4 sm:px-6 flex items-center justify-between">
        {{-- Spacer kiri untuk menjaga judul tetap tengah meski ada tombol profil di kanan --}}
        <div class="w-10 sm:w-12"></div>

        {{-- Judul halaman diposisikan absolute agar tetap center --}}
        <h1 class="absolute left-1/2 -translate-x-1/2 font-bold text-base sm:text-lg tracking-wide text-gray-800">
            Admin E-Presensi
        </h1>

        {{-- Wrapper tombol profil + dropdown (Alpine state didefinisikan di x-data) --}}
        <div class="relative"
            x-data='{
                open:false, // state buka/tutup dropdown

                // --- state reaktif yang diisi dari server (SSR) ---
                name: @json($displayFirstName),   // CHANGED: nama depan
                photoUrl: @json($photoUrl),       // URL streaming foto privat (bisa null)
                ver: @json($pfVer),               // versi untuk cache-buster (opsional)

                // Getter: src avatar final + query ?v=<ver> jika ada
                get photoSrc(){
                    return this.photoUrl ? (this.photoUrl + (this.ver ? ("?v=" + this.ver) : "")) : null;
                },

                // Helper: ambil nama depan dari string nama lengkap
                extractFirst(n){
                    const s = (n || "").trim();
                    const m = s.match(/^\s*([^\s]+)/u);
                    return (m && m[1]) ? m[1] : "User";
                },

                // Trigger event global untuk dibaca komponen Livewire (buka modal profil/password)
                openEditProfile(){ window.dispatchEvent(new CustomEvent("self-profile:edit-open")); },
                openChangePassword(){ window.dispatchEvent(new CustomEvent("self-profile:password-open")); },

                // Lifecycle Alpine: dengarkan event dari Livewire untuk sinkron UI
                init(){
                    // Jika foto diupdate, Livewire kirim event dengan version (timestamp)
                    window.addEventListener("profile-photo-updated", (e) => {
                        this.ver = (e.detail && e.detail.version) ? e.detail.version : Date.now();
                    });
                    // Jika nama diupdate (mungkin kirim nama lengkap), tampilkan nama depan
                    window.addEventListener("self-profile:name-updated", (e) => {
                        if (e.detail && e.detail.name) this.name = this.extractFirst(e.detail.name);
                    });
                }
            }'>

            {{-- Tombol profil (toggle dropdown) --}}
            <button type="button"
                    @click="open = !open"
                    @keydown.escape.window="open = false"
                    class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white p-1.5 sm:px-3 sm:py-1.5 shadow-sm hover:bg-gray-50 focus:outline-none"
                    aria-haspopup="menu"
                    :aria-expanded="open.toString()"
                    aria-label="Menu profil">

                {{-- Avatar: pakai foto privat jika tersedia, fallback ke placeholder --}}
                <img :src="photoSrc" @if ($photoUrl) src="{{ $photoUrl }}" @endif
                     alt="User" class="h-8 w-8 rounded-full object-cover ring-1 ring-gray-200"
                     x-show="photoSrc" x-transition onerror="this.style.display='none';">

                {{-- Placeholder (ikon vektor) jika tidak ada foto atau gagal load --}}
                <div x-show="!photoSrc"
                     class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center ring-1 ring-gray-200">
                    <img src="{{ asset('images/no_profile_icon.svg') }}" class="h-5 w-5" alt="No Profile Icon">
                </div>

                {{-- Nama user (nama depan; disembunyikan di mobile untuk hemat ruang) --}}
                <span class="font-medium text-gray-800 max-w-[10rem] truncate hidden sm:block"
                      x-text="name">{{ $displayFirstName }}</span>

                {{-- Ikon caret (hanya tampil >= sm) --}}
                <img src="{{ asset('images/dropdown_icon.svg') }}" class="h-5 w-5 hidden sm:block" alt="dropdown icon">
            </button>

            {{-- Dropdown menu profil --}}
            <div x-cloak x-show="open"
                 @click.outside="open = false"
                 x-transition.opacity
                 x-transition.scale.origin.top.right
                 class="absolute right-0 mt-2 w-56 bg-white border border-gray-200 rounded-xl shadow-xl py-2 z-50"
                 role="menu">

                {{-- Header: tampilkan nama depan (truncate jika panjang) --}}
                <div class="px-4 pb-2">
                    <p class="text-sm font-semibold text-gray-900 truncate" x-text="name">{{ $displayFirstName }}</p>
                </div>
                <div class="my-1 h-px bg-gray-100"></div>

                {{-- Pindah Mode --}}
                <a href="{{ url('/') }}"
                   class="flex items-center gap-2 px-4 py-2 text-sm text-gray-800 hover:bg-gray-50"
                   role="menuitem">
                    <img src="{{ asset('images/change_icon.svg') }}" class="h-8 w-8" alt="change icon">
                    Pindah Mode
                </a>

                <div class="my-1 h-px bg-gray-100"></div>

                {{-- Ubah Profil --}}
                <button type="button"
                        class="w-full flex items-center gap-2 px-4 py-2 text-left text-sm text-gray-800 hover:bg-gray-50"
                        @click.prevent="openEditProfile(); open=false"
                        role="menuitem">
                    <img src="{{ asset('images/edit_dropdown_icon.svg') }}" class="h-5 w-5" alt="edit dropdown icon">
                    Ubah Profil
                </button>

                {{-- Ganti Password --}}
                <button type="button"
                        class="w-full flex items-center gap-2 px-4 py-2 text-left text-sm text-gray-800 hover:bg-gray-50"
                        @click.prevent="openChangePassword(); open=false"
                        role="menuitem">
                    <img src="{{ asset('images/password_dropdown_icon.svg') }}" class="h-5 w-5" alt="password dropdown icon">
                    Ganti Password
                </button>

                <div class="my-1 h-px bg-gray-100"></div>

                {{-- Logout (POST + CSRF) --}}
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
