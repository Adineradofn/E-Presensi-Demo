@extends('mode.app')

@section('content')
<div class="flex flex-col items-center justify-center min-h-[70vh] px-6">
    <!-- Sambutan -->
    <h2 class="text-xl sm:text-2xl font-bold mb-12 text-gray-800">
        Selamat datang Admin Fulan, silahkan pilih halaman
    </h2>

    <!-- Tombol pilihan -->
    <div class="flex gap-12">
        <!-- Tombol Karyawan -->
        <a href="#"
           class="w-48 h-32 flex items-center justify-center 
                  text-xl font-semibold text-gray-700 
                  border border-gray-300 rounded-2xl shadow-md
                  hover:bg-emerald-500 hover:text-white hover:border-emerald-600 
                  transition duration-300 ease-in-out">
            Karyawan
        </a>

        <!-- Tombol Admin -->
        <a href="#"
           class="w-48 h-32 flex items-center justify-center 
                  text-xl font-semibold text-gray-700 
                  border border-gray-300 rounded-2xl shadow-md
                  hover:bg-emerald-500 hover:text-white hover:border-emerald-600 
                  transition duration-300 ease-in-out">
            Admin
        </a>
    </div>
</div>
@endsection
