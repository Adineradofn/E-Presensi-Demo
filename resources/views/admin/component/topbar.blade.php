@php
    use Illuminate\Support\Facades\Auth;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Str;

    $user = Auth::user();

    // Nama lengkap & nama depan (fallback aman)
    $displayName = $user->nama ?? ($user->name ?? 'User');
    $displayFirstName = $user?->first_name;
    if (!$displayFirstName) {
        $displayFirstName = (string) Str::of($displayName)->trim()->before(' ');
    }
    if ($displayFirstName === '') {
        $displayFirstName = 'User';
    }

    // Foto privat via controller (route yang tersedia)
    $idKaryawan = $user->id_karyawan ?? ($user->id ?? null);
    $punyaFoto  = !empty($user->foto ?? null);
    $photoUrl   = null;

    if ($idKaryawan && $punyaFoto) {
        if (Route::has('admin.data.karyawan.foto')) {
            $photoUrl = route('admin.data.karyawan.foto', ['karyawan' => $idKaryawan]);
        } elseif (Route::has('data.karyawan.foto')) {
            $photoUrl = route('data.karyawan.foto', ['karyawan' => $idKaryawan]);
        }
    }

    // Versi untuk cache-buster ?v= (mis. timestamp saat update foto)
    $pfVer = session('pf_ver');
@endphp

@once
    <style>
        /* Hindari flashing sebelum Alpine siap */
        [x-cloak]{ display:none !important; }
    </style>
@endonce

@persist('topbar_admin')
<header class="sticky top-0 z-40 w-full h-16 bg-white/90 backdrop-blur border-b border-gray-200 shadow-sm">
    <div class="h-full px-4 sm:px-6 flex items-center justify-between">
        {{-- Spacer kiri agar judul tetap center --}}
        <div class="w-10 sm:w-12"></div>

        {{-- Judul halaman --}}
        <h1 class="absolute left-1/2 -translate-x-1/2 font-bold text-base sm:text-lg tracking-wide text-gray-800">
            Admin E-Presensi
        </h1>

        {{-- Profil + dropdown --}}
        <div class="relative"
             x-data='{
                open:false,

                // Data dari server
                name: @json($displayFirstName),
                photoUrl: @json($photoUrl),
                ver: @json($pfVer),

                // src final avatar (pakai cache-buster kalau ada)
                get photoSrc(){
                    return this.photoUrl ? (this.photoUrl + (this.ver ? ("?v=" + this.ver) : "")) : null;
                },

                // Helper ambil nama depan saat event update nama (jaga konsistensi)
                extractFirst(n){
                    const s = (n || "").trim();
                    const m = s.match(/^\s*([^\s]+)/u);
                    return (m && m[1]) ? m[1] : "User";
                },

                // Event untuk buka modal (dibaca komponen Livewire lain)
                openEditProfile(){ window.dispatchEvent(new CustomEvent("self-profile:edit-open")); },
                openChangePassword(){ window.dispatchEvent(new CustomEvent("self-profile:password-open")); },

                // Sinkron UI saat foto/nama diupdate dari tempat lain
                init(){
                    window.addEventListener("profile-photo-updated", (e) => {
                        this.ver = (e.detail && e.detail.version) ? e.detail.version : Date.now();
                    });
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

                {{-- Avatar: hanya pakai :src dari Alpine (hindari duplikasi request) --}}
                <img :src="photoSrc"
                     alt="User"
                     class="h-8 w-8 rounded-full object-cover ring-1 ring-gray-200"
                     x-show="photoSrc"
                     x-cloak
                     style="visibility:hidden"        {{-- anti-pop: tampil setelah onload --}}
                     @load="$el.style.visibility='visible'"
                     onerror="this.style.display='none'">

                {{-- Placeholder jika tidak ada foto --}}
                <div x-show="!photoSrc"
                     x-cloak
                     class="h-8 w-8 rounded-full bg-gray-200 flex items-center justify-center ring-1 ring-gray-200">
                    <img src="{{ asset('images/no_profile_icon.svg') }}" class="h-5 w-5" alt="No Profile Icon" loading="lazy">
                </div>

                {{-- Nama depan (sembunyi di mobile) --}}
                <span class="font-medium text-gray-800 max-w-[10rem] truncate hidden sm:block"
                      x-text="name">{{ $displayFirstName }}</span>

                {{-- Ikon caret --}}
                <img src="{{ asset('images/dropdown_icon.svg') }}" class="h-5 w-5 hidden sm:block" alt="dropdown icon" loading="lazy">
            </button>

            {{-- Dropdown menu --}}
            <div x-cloak
                 x-show="open"
                 @click.outside="open = false"
                 x-transition.opacity
                 x-transition.scale.origin.top.right
                 class="absolute right-0 mt-2 w-56 bg-white border border-gray-200 rounded-xl shadow-xl py-2 z-50"
                 role="menu"
                 aria-label="Menu profil">

                <div class="px-4 pb-2">
                    <p class="text-sm font-semibold text-gray-900 truncate" x-text="name">{{ $displayFirstName }}</p>
                </div>
                <div class="my-1 h-px bg-gray-100"></div>

                <a href="{{ url('/') }}"
                   class="flex items-center gap-2 px-4 py-2 text-sm text-gray-800 hover:bg-gray-50"
                   role="menuitem">
                    <img src="{{ asset('images/change_icon.svg') }}" class="h-5 w-5" alt="change icon" loading="lazy">
                    Pindah Mode
                </a>

                <div class="my-1 h-px bg-gray-100"></div>

                <button type="button"
                        class="w-full flex items-center gap-2 px-4 py-2 text-left text-sm text-gray-800 hover:bg-gray-50"
                        @click.prevent="openEditProfile(); open=false"
                        role="menuitem">
                    <img src="{{ asset('images/edit_dropdown_icon.svg') }}" class="h-5 w-5" alt="edit dropdown icon" loading="lazy">
                    Ubah Profil
                </button>

                <button type="button"
                        class="w-full flex items-center gap-2 px-4 py-2 text-left text-sm text-gray-800 hover:bg-gray-50"
                        @click.prevent="openChangePassword(); open=false"
                        role="menuitem">
                    <img src="{{ asset('images/password_dropdown_icon.svg') }}" class="h-5 w-5" alt="password dropdown icon" loading="lazy">
                    Ganti Password
                </button>

                <div class="my-1 h-px bg-gray-100"></div>

                <form method="POST" action="{{ route('logout') }}" role="menuitem">
                    @csrf
                    <button type="submit"
                            class="w-full flex items-center gap-2 px-4 py-2 text-left text-sm text-red-600 hover:bg-red-50">
                        <img src="{{ asset('images/logout_icon.svg') }}" class="h-5 w-5" alt="logout icon" loading="lazy">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>
@endpersist
