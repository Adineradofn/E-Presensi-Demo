<div id="statusPresensi"
     x-data
     x-cloak
     x-show="$store.presensiModal && $store.presensiModal.open"
     x-transition.opacity
     x-on:close-presensi-modal.window="$store.presensiModal.close()"
     class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/70"
     role="dialog" aria-modal="true" aria-labelledby="statusPresensiTitle"
     wire:ignore.self>
    {{-- Overlay --}}
    <div class="absolute inset-0" @click="$store.presensiModal.close()" aria-hidden="true"></div>

    {{-- Panel --}}
    <div class="relative z-10 w-[96%] max-w-md" @keydown.window.escape="$store.presensiModal.close()">
        <div class="bg-white rounded-2xl shadow-2xl ring-1 ring-black/5 overflow-hidden" x-ref="panel"
            x-transition.scale.origin.center>

            {{-- Header --}}
            <div class="sticky top-0 z-20 flex items-center justify-between px-5 py-3 bg-gradient-to-r from-emerald-600 to-emerald-500 text-white">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-white/20">
                        <img src="{{ asset('images/edit_white_icon.svg') }}" class="h-6 w-6" alt="edit karyawan">
                    </span>
                    <h3 id="statusPresensiTitle" class="text-base sm:text-lg font-semibold">Ubah Status Presensi</h3>
                </div>
                <button type="button"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/60"
                        aria-label="Tutup modal" @click="$store.presensiModal.close()">
                    <img src="{{ asset('images/cancel_icon.svg') }}" class="h-6 w-6" alt="Tutup">
                </button>
            </div>

            {{-- Body --}}
            <div class="max-h-[70vh] overflow-y-auto px-5 py-5">
                <form id="formStatusPresensi" wire:submit.prevent="saveStatus" class="space-y-4" novalidate>
                    {{-- Status --}}
                    <div>
                        <label for="selectStatus" class="text-sm font-medium text-gray-700">
                            Status <span class="text-red-600">*</span>
                        </label>
                        <div class="relative mt-1">
                            <select id="selectStatus" x-model="$store.presensiModal.selectedStatus"
                                @change="$wire.set('selectedStatus', $store.presensiModal.selectedStatus)"
                                class="w-full rounded-xl border bg-white/80 px-3 py-2 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 border-gray-300 text-sm"
                                aria-invalid="false">
                                <option value="hadir">Hadir</option>
                                <option value="tidak hadir">Tidak hadir</option>
                                <option value="izin">Izin</option>
                                <option value="sakit">Sakit</option>
                            </select>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Pilih status presensi yang sesuai.</p>
                        @error('selectedStatus')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Ringkasan --}}
                    <div class="rounded-xl border border-gray-100 bg-gray-50 px-3 py-2 text-xs text-gray-600 space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1">
                                <img src="{{ asset('images/clock_icon.svg') }}" class="h-5 w-5" alt="clock icon">
                                Status saat ini:
                            </span>
                            <strong class="text-gray-800" x-text="$store.presensiModal.initialStatusLabel"></strong>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1">
                                <img src="{{ asset('images/user_grey_icon.svg') }}" class="h-5 w-5" alt="user icon">
                                Nama:
                            </span>
                            <span class="inline-flex items-center rounded-lg bg-white px-2 py-0.5 ring-1 ring-gray-200 text-gray-700"
                                  x-text="$store.presensiModal.nama || '—'"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1">
                                <img src="{{ asset('images/calendar_grey_icon.svg') }}" class="h-5 w-5" alt="calendar icon">
                                Tanggal:
                            </span>
                            <span class="inline-flex items-center rounded-lg bg-white px-2 py-0.5 ring-1 ring-gray-200 text-gray-700"
                                  x-text="$store.presensiModal.tanggal || '—'"></span>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="sticky bottom-0 z-20 mt-2 bg-white px-5 py-3 border-t border-gray-100 flex items-center justify-end gap-2">
                        <button type="button"
                                class="px-4 py-2 rounded-xl border border-gray-300 bg-white hover:bg-gray-50"
                                @click="$store.presensiModal.close()">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-4 py-2 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm inline-flex items-center gap-2">
                            <img src="{{ asset('images/save_icon.svg') }}" class="h-5 w-5" alt="simpan">
                            Simpan
                        </button>
                    </div>

                    <input type="hidden" wire:model="selectedPresensiId">
                </form>
            </div>

        </div>
    </div>
</div>

{{-- ✅ Inisialisasi store Alpine yang PASTI jalan --}}
<script>
(function(){
  function definePresensiStore(){
    if (!window.Alpine) return;
    if (Alpine.store('presensiModal')) return; // hindari duplikasi

    Alpine.store('presensiModal', {
      open: false,
      initialStatus: 'hadir',
      selectedStatus: 'hadir',
      nama: '',
      tanggal: '',
      modalId: 'statusPresensi',
      get initialStatusLabel() {
          const m = {
              'hadir': 'Hadir',
              'tidak hadir': 'Tidak hadir',
              'izin': 'Izin',
              'sakit': 'Sakit'
          };
          return m[this.initialStatus] ?? this.initialStatus;
      },
      openWith(payload = {}) {
          if (payload.targetId && payload.targetId !== this.modalId) return;
          this.initialStatus = payload.currentStatus || 'hadir';
          this.selectedStatus = this.initialStatus;
          this.nama = payload.nama || '';
          this.tanggal = payload.tanggal || '';
          this.open = true;
          queueMicrotask(() => {
              const el = document.querySelector('#formStatusPresensi select, #formStatusPresensi button');
              el && el.focus && el.focus();
          });
      },
      close() { this.open = false; },
    });
  }

  if (window.Alpine) {
    definePresensiStore();
  } else {
    document.addEventListener('alpine:init', definePresensiStore, { once: true });
  }
})();
</script>
