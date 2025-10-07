<div
  id="modalSelfPassword"
  x-data="{
    open:false,
    showPw:false,
    showPw2:false,
    async openFromAuth(){ await $wire.fillPasswordFromAuth(); this.open=true; this.showPw=false; this.showPw2=false; },
    close(){ this.open=false; this.showPw=false; this.showPw2=false; $wire.resetPasswordForm(); },
  }"
  x-cloak
  x-show="open"
  x-transition.opacity
  class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/70"
  role="dialog"
  aria-modal="true"
  aria-labelledby="modalSelfPasswordTitle"
  x-on:self-profile:password-open.window="openFromAuth()"
  x-on:self-profile:password-close.window="close()"
  x-init="
    window.addEventListener('self-profile:password-open', () => openFromAuth());
    window.addEventListener('self-profile:password-close', () => close());
  "
>
  <div class="absolute inset-0" @click="close()" aria-hidden="true"></div>

  <div class="relative z-10 w-[96%] max-w-md" @keydown.escape.window="close()">
    <div
      class="bg-white rounded-2xl shadow-2xl ring-1 ring-black/5 overflow-hidden"
      x-transition.scale.origin.center
    >
      <!-- Header -->
      <div class="sticky top-0 z-20 flex items-center justify-between px-5 py-3 bg-gradient-to-r from-emerald-600 to-emerald-500 text-white">
        <h3 id="modalSelfPasswordTitle" class="text-base sm:text-lg font-semibold">
          Ubah Password
        </h3>
        <button
          type="button"
          class="inline-flex h-9 w-9 items-center justify-center rounded-lg hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/60"
          aria-label="Tutup modal"
          @click="close()"
        >
          <img src="{{ asset('images/cancel_icon.svg') }}" class="h-6 w-6" alt="Tutup">
        </button>
      </div>

      <!-- Body -->
      <div class="px-5 py-5 max-h-[75vh] overflow-y-auto" x-ref="scrollArea">
        <form class="grid grid-cols-1 gap-4" wire:submit.prevent="updateSelfPassword" id="formPassword" novalidate>
          @csrf

          <div class="-mt-1 -mb-1 text-sm text-gray-600">
            <span>Kolom bertanda <span class="text-red-600">*</span> wajib diisi.</span>
          </div>

          <!-- Password baru -->
          <div>
            <label class="text-sm font-medium text-gray-700">
              Password baru <span class="text-red-600">*</span>
            </label>
            <div class="relative mt-1">
              <input
                x-ref="first"
                :type="showPw ? 'text' : 'password'"
                wire:model.defer="password.password"
                autocomplete="new-password"
                placeholder="Min 6, ≥1 huruf besar & ≥1 angka"
                aria-invalid="{{ $errors->has('password.password') ? 'true' : 'false' }}"
                @class([
                  'w-full rounded-xl border bg-white/80 px-3 py-2 pr-10 focus:outline-none',
                  'focus:ring-emerald-500 focus:border-emerald-500 border-gray-300' => !$errors->has('password.password'),
                  'border-red-400 focus:ring-red-500 focus:border-red-500' => $errors->has('password.password'),
                ])
              >
              <button
                type="button"
                class="absolute inset-y-0 right-0 px-3 text-gray-500 hover:text-gray-700"
                :aria-label="showPw ? 'Sembunyikan password' : 'Tampilkan password'"
                :aria-pressed="showPw"
                @click="showPw = !showPw"
                tabindex="-1"
              >
                <!-- Tampilkan -->
                <img x-cloak x-show="!showPw" src="{{ asset('images/eye_icon.svg') }}" class="h-5 w-5" alt="Tampilkan password">
                <!-- Sembunyikan -->
                <img x-cloak x-show="showPw" src="{{ asset('images/eye_hide_icon.svg') }}" class="h-5 w-5" alt="Sembunyikan password">
              </button>
            </div>
            <p class="mt-1 text-xs text-gray-500">Minimal 6 karakter, wajib 1 huruf besar & 1 angka.</p>
            @error('password.password')
              <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
          </div>

          <!-- Konfirmasi password baru -->
          <div>
            <label class="text-sm font-medium text-gray-700">
              Konfirmasi password baru <span class="text-red-600">*</span>
            </label>
            <div class="relative mt-1">
              <input
                :type="showPw2 ? 'text' : 'password'"
                wire:model.defer="password.password_confirmation"
                autocomplete="new-password"
                placeholder="Ulangi password baru"
                aria-invalid="{{ $errors->has('password.password_confirmation') ? 'true' : 'false' }}"
                @class([
                  'w-full rounded-xl border bg-white/80 px-3 py-2 pr-10 focus:outline-none',
                  'focus:ring-emerald-500 focus:border-emerald-500 border-gray-300' => !$errors->has('password.password_confirmation'),
                  'border-red-400 focus:ring-red-500 focus:border-red-500' => $errors->has('password.password_confirmation'),
                ])
              >
              <button
                type="button"
                class="absolute inset-y-0 right-0 px-3 text-gray-500 hover:text-gray-700"
                :aria-label="showPw2 ? 'Sembunyikan konfirmasi' : 'Tampilkan konfirmasi'"
                :aria-pressed="showPw2"
                @click="showPw2 = !showPw2"
                tabindex="-1"
              >
                <!-- Tampilkan -->
                <img x-cloak x-show="!showPw2" src="{{ asset('images/eye_icon.svg') }}" class="h-5 w-5" alt="Tampilkan password">
                <!-- Sembunyikan -->
                <img x-cloak x-show="showPw2" src="{{ asset('images/eye_hide_icon.svg') }}" class="h-5 w-5" alt="Sembunyikan password">
              </button>
            </div>
            @error('password.password_confirmation')
              <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
            @enderror
          </div>

          <!-- Footer -->
          <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
            <button type="button" class="px-4 py-2 rounded-xl border border-gray-300 bg-white hover:bg-gray-50" @click="close()">Batal</button>
            <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm" wire:loading.attr="disabled">Simpan</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
