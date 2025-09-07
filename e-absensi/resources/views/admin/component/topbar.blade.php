<header class="relative w-full h-16 bg-white border-b flex items-center justify-end px-6">
    {{-- Judul Tengah --}}
    <h1 class="absolute left-1/2 -translate-x-1/2 font-bold text-lg">
        ADMIN E-ABSENSI
    </h1>

    {{-- Kanan: Profile Dropdown --}}
    <div class="relative" x-data="{ open: false }">
        <button @click="open = !open"
                class="flex items-center gap-2 px-3 py-1 rounded-md hover:bg-gray-100 focus:outline-none">
            <img src="{{ asset('images/user.jpg') }}" alt="User" class="h-8 w-8 rounded-full object-cover">
            <span class="font-medium">{{ session('nama') ?? 'Pengguna' }}</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        {{-- Dropdown menu --}}
        <div x-cloak x-show="open" @click.away="open = false"
             class="absolute right-0 mt-2 w-44 bg-white border rounded-lg shadow-lg py-2 z-50">
            <a href="#" class="block px-4 py-2 text-sm hover:bg-gray-100">Ubah Profil</a>
            <a href="#" class="block px-4 py-2 text-sm hover:bg-gray-100">Ganti Password</a>

            {{-- Logout via POST --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                        class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100"
                        @click="open = false">
                    Logout
                </button>
            </form>
        </div>
    </div>
</header>
