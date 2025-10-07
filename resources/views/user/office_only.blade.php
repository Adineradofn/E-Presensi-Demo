{{-- resources/views/user/office_only.blade.php --}}
@extends('user.app_user')

@section('content')
<div class="min-h-[60vh] flex items-center justify-center p-4">
  <div class="w-full max-w-md">
    <div class="bg-white dark:bg-slate-900 shadow ring-1 ring-slate-200/60 dark:ring-slate-700/60 rounded-2xl p-6 text-center">
      <div class="mx-auto mb-3 h-12 w-12 rounded-full bg-rose-50 dark:bg-rose-900/20 flex items-center justify-center">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 4h.01M12 3a9 9 0 100 18 9 9 0 000-18z"/>
        </svg>
      </div>
      <h1 class="text-lg font-semibold text-slate-900 dark:text-slate-100">Akses Terbatas</h1>
      <p class="mt-2 text-sm text-slate-600 dark:text-slate-300">
        Anda di luar area jaringan kantor, silakan melakukan presensi di jaringan kantor.
      </p>
    </div>
  </div>
</div>
@endsection
