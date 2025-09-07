<header class="relative w-full h-16 bg-white border-b flex items-center justify-between px-6">
    <!-- Kiri: Logo + Nama Perusahaan -->
    <div class="flex items-center gap-3">
        <img src="{{ asset('images/logo_perusahaan.png') }}" class="h-12 w-12 rounded-full" alt="Logo">
        <span class="font-bold text-lg text-gray-800">PT. ASA</span>
    </div>

    <!-- Tengah: Judul -->
    <h1 class="absolute left-1/2 transform -translate-x-1/2 font-bold text-lg text-gray-900">
        ADMIN E-ABSENSI
    </h1>

    <!-- Kanan: Profile Dropdown -->
    <div class="relative" x-data="{ open: false }">
        <button @click="open = !open"
                class="flex items-center gap-2 px-3 py-1 rounded-md hover:bg-gray-100 focus:outline-none">
            <img src="{{ asset('images/user.jpg') }}" alt="User" class="h-8 w-8 rounded-full object-cover">
            <span class="font-medium">Fulan Fulan</span>
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Dropdown menu -->
        <div x-show="open" @click.away="open = false"
             class="absolute right-0 mt-2 w-44 bg-white border rounded-lg shadow-lg py-2 z-50">
            <a href="#" class="block px-4 py-2 text-sm hover:bg-gray-100">Ubah Profile</a>
            <a href="#" class="block px-4 py-2 text-sm hover:bg-gray-100">Ganti Password</a>
            <button type="button" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-100">
                Logout
            </button>
        </div>
    </div>
</header>
