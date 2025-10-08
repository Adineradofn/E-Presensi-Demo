<div id="modalEditLibur"
     x-data="editLiburModal()"
     x-cloak x-show="open" x-transition.opacity
     class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/70"
     role="dialog" aria-modal="true" aria-labelledby="modalEditLiburTitle"
     x-on:modal-edit-open.window="open = true"
     x-on:modal-edit-close.window="close()"
     x-on:open-edit.window="openWith($event.detail)"
>
  <div class="absolute inset-0" @click="close()" aria-hidden="true"></div>

  <div class="relative z-10 w-[96%] max-w-xl" @keydown.window.escape="close()">
    <div class="bg-white rounded-2xl shadow-2xl ring-1 ring-black/5 overflow-hidden" x-transition.scale.origin.center>
      <div class="sticky top-0 z-20 flex items-center justify-between px-5 py-3 bg-gradient-to-r from-emerald-600 to-emerald-500 text-white">
        <div class="flex items-center gap-3">
          <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-white/20">
            <img src="{{ asset('images/edit_white_icon.svg') }}" class="h-5 w-5" alt="edit">
          </span>
          <h3 id="modalEditLiburTitle" class="text-base sm:text-lg font-semibold">
            Ubah Hari Libur <span class="font-normal opacity-90" x-text="form.nama_hari ? `— ${form.nama_hari}` : ''"></span>
          </h3>
        </div>
        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/60"
                aria-label="Tutup modal" @click="close()">
          <img src="{{ asset('images/cancel_icon.svg') }}" class="h-8 w-8" alt="cancel">
        </button>
      </div>

      <div class="max-h-[70vh] overflow-y-auto px-5 py-5">
        <form id="formEditLibur" wire:submit.prevent="update" class="grid grid-cols-1 gap-4" novalidate>
          @csrf

          <input type="hidden" wire:model="edit.id" />

          {{-- Nama hari --}}
          <div>
            <label class="text-sm font-medium text-gray-700">Nama Hari <span class="text-red-600">*</span></label>
            <input type="text" wire:model.defer="edit.nama_hari"
              @class([
                'mt-1 w-full rounded-xl border bg-white/80 px-3 py-2 focus:outline-none',
                'focus:ring-emerald-500 focus:border-emerald-500 border-gray-300' => !$errors->has('edit.nama_hari'),
                'border-red-400 focus:ring-red-500 focus:border-red-500' => $errors->has('edit.nama_hari'),
              ])
              placeholder="Mis. Tahun Baru, Nyepi, dll">
            @error('edit.nama_hari') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Tanggal mulai & selesai --}}
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="text-sm font-medium text-gray-700">Tanggal Mulai <span class="text-red-600">*</span></label>
              <input type="date" wire:model.defer="edit.tanggal_mulai" min="{{ $tomorrow }}"
                @class([
                  'mt-1 w-full rounded-xl border bg-white/80 px-3 py-2 focus:outline-none',
                  'focus:ring-emerald-500 focus:border-emerald-500 border-gray-300' => !$errors->has('edit.tanggal_mulai'),
                  'border-red-400 focus:ring-red-500 focus:border-red-500' => $errors->has('edit.tanggal_mulai'),
                ])>
              @error('edit.tanggal_mulai') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
              <label class="text-sm font-medium text-gray-700">Tanggal Selesai <span class="text-red-600">*</span></label>
              <input type="date" wire:model.defer="edit.tanggal_selesai"
                :min="$wire.edit.tanggal_mulai || '{{ $tomorrow }}'"
                @class([
                  'mt-1 w-full rounded-xl border bg-white/80 px-3 py-2 focus:outline-none',
                  'focus:ring-emerald-500 focus:border-emerald-500 border-gray-300' => !$errors->has('edit.tanggal_selesai'),
                  'border-red-400 focus:ring-red-500 focus:border-red-500' => $errors->has('edit.tanggal_selesai'),
                ])>
              @error('edit.tanggal_selesai') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
          </div>

          {{-- Keterangan --}}
          <div>
            <label class="text-sm font-medium text-gray-700">Keterangan (opsional)</label>
            <textarea rows="3" wire:model.defer="edit.keterangan"
              class="mt-1 w-full rounded-xl border bg-white/80 px-3 py-2 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 border-gray-300"
              placeholder="Catatan tambahan…"></textarea>
            @error('edit.keterangan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
          </div>

          <div class="flex items-center justify-end gap-2">
            <button type="button" class="px-4 py-2 rounded-xl border border-gray-300 bg-white hover:bg-gray-50" @click="$dispatch('modal-edit-close')">Batal</button>
            <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm" wire:loading.attr="disabled">
              <span wire:loading.remove>Update</span>
              <span wire:loading>Memproses…</span>
            </button>
          </div>
        </form>
      </div>

    </div>
  </div>
</div>

<script>
function editLiburModal() {
  return {
    open:false,
    form:{ id:'', nama_hari:'', tanggal_mulai:'', tanggal_selesai:'', keterangan:'' },
    openWith(p) {
      this.form = { ...this.form, ...(p || {}) };
      this.open = true;
      this.$nextTick(() => {
        const first = document.querySelector('#formEditLibur input, #formEditLibur textarea, #formEditLibur button');
        first?.focus?.();
      });
    },
    close() {
      this.open = false;
      $wire.resetEditForm?.();
    }
  }
}
</script>
