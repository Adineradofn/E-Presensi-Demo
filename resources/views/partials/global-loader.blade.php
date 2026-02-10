@once
<style>
  [x-cloak]{display:none!important}
</style>
@endonce

<!-- GLOBAL LOADER OVERLAY -->
<div
  x-data="{ show:false, tid:null }"
  x-cloak
  x-show="show"
  x-transition.opacity.duration.150ms
  class="fixed inset-0 z-[100000] flex items-center justify-center bg-black/35 backdrop-blur-sm"
  role="status" aria-live="polite"
  x-on:global-loader-show.window="
    clearTimeout(tid); show = true;
    // fallback auto-hide kalau ada error tak terduga
    tid = setTimeout(() => show=false, 15000);
  "
  x-on:global-loader-hide.window="clearTimeout(tid); show = false;"
>
  <!-- kartu kecil estetik -->
  <div class="relative grid place-items-center rounded-2xl bg-white/90 ring-1 ring-black/5 shadow-2xl px-6 py-5">
    <!-- cincin berputar -->
    <div class="relative h-12 w-12">
      <svg class="absolute inset-0 h-12 w-12 animate-spin" viewBox="0 0 24 24" aria-hidden="true">
        <defs>
          <linearGradient id="loaderGradient" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="#10B981"/>
            <stop offset="100%" stop-color="#3B82F6"/>
          </linearGradient>
        </defs>
        <circle cx="12" cy="12" r="10" stroke="#E5E7EB" stroke-width="4" fill="none"/>
        <path d="M22 12a10 10 0 0 0-10-10" stroke="url(#loaderGradient)" stroke-width="4" fill="none" stroke-linecap="round"/>
      </svg>
    </div>

    <div class="mt-3 text-center">
      <p class="text-sm font-medium text-gray-800">Loading</p>
      <p class="text-xs text-gray-500">Please Wait....</p>
    </div>
  </div>
</div>
<!-- /GLOBAL LOADER OVERLAY -->
