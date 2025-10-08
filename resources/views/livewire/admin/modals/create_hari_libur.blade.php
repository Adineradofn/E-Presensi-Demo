<div id="modalCreateLibur"
     x-data="{
        open:false,
        openModal(){ this.open = true; this.$nextTick(() => { document.querySelector('#formCreateLibur input, #formCreateLibur textarea, #formCreateLibur button, #formCreateLibur select')?.focus(); }); },
        close(){ this.open = false; $wire.resetCreateForm?.(); }
     }"
     x-cloak x-show="open" x-transition.opacity
     class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/70"
     role="dialog" aria-modal="true" aria-labelledby="modalCreateLiburTitle"
     x-on:modal-create-open.window="openModal()"
     x-on:modal-create-close.window="close()"
>
  <div class="absolute inset-0" @click="close()" aria-hidden="true"></div>

  <div class="relative z-10 w-[96%] max-w-xl" @keydown.window.escape="close()">
    <div class="bg-white rounded-2xl shadow-2xl ring-1 ring-black/5 overflow-hidden" x-transition.scale.origin.center>
      <div class="sticky top-0 z-20 flex items-center justify-between px-5 py-3 bg-gradient-to-r from-emerald-600 to-emerald-500 text-white">
        <div class="flex items-center gap-3">
          <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-white/20">
            <img src="{{ asset('images/add_icon.svg') }}" class="h-5 w-5" alt="add">
          </span>
          <h3 id="modalCreateLiburTitle" class="text-base sm:text-lg font-semibold">Tambah Hari Libur</h3>
        </div>
        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/60"
                aria-label="Tutup modal" @click="close()">
          <img src="{{ asset('images/cancel_icon.svg') }}" class="h-8 w-8" alt="cancel">
        </button>
      </div>

      <div class="max-h-[70vh] overflow-y-auto px-5 py-5">
        <form id="formCreateLibur" wire:submit.prevent="store" class="grid grid-cols-1 gap-4" novalidate>
          @csrf
          <input type="hidden" name="_open" value="create">

          {{-- Nama hari --}}
          <div>
            <label class="text-sm font-medium text-gray-700">Nama Hari <span class="text-red-600">*</span></label>
            <input type="text" wire:model.defer="create.nama_hari"
              @class([
                'mt-1 w-full rounded-xl border bg-white/80 px-3 py-2 focus:outline-none',
                'focus:ring-emerald-500 focus:border-emerald-500 border-gray-300' => !$errors->has('create.nama_hari'),
                'border-red-400 focus:ring-red-500 focus:border-red-500' => $errors->has('create.nama_hari'),
              ])
              placeholder="Masukan nama hari libur">
            @error('create.nama_hari') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Tanggal mulai & selesai --}}
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <label class="text-sm font-medium text-gray-700">Tanggal Mulai <span class="text-red-600">*</span></label>
              <input type="date" wire:model.defer="create.tanggal_mulai" min="{{ $tomorrow }}"
                @class([
                  'mt-1 w-full rounded-xl border bg-white/80 px-3 py-2 focus:outline-none',
                  'focus:ring-emerald-500 focus:border-emerald-500 border-gray-300' => !$errors->has('create.tanggal_mulai'),
                  'border-red-400 focus:ring-red-500 focus:border-red-500' => $errors->has('create.tanggal_mulai'),
                ])>
              @error('create.tanggal_mulai') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
              <label class="text-sm font-medium text-gray-700">Tanggal Selesai <span class="text-red-600">*</span></label>
              <input type="date" wire:model.defer="create.tanggal_selesai"
                :min="$wire.create.tanggal_mulai || '{{ $tomorrow }}'"
                @class([
                  'mt-1 w-full rounded-xl border bg-white/80 px-3 py-2 focus:outline-none',
                  'focus:ring-emerald-500 focus:border-emerald-500 border-gray-300' => !$errors->has('create.tanggal_selesai'),
                  'border-red-400 focus:ring-red-500 focus:border-red-500' => $errors->has('create.tanggal_selesai'),
                ])>
              @error('create.tanggal_selesai') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
          </div>

          {{-- Keterangan --}}
          <div>
            <label class="text-sm font-medium text-gray-700">Keterangan (opsional)</label>
            <textarea rows="3" wire:model.defer="create.keterangan"
              class="mt-1 w-full rounded-xl border bg-white/80 px-3 py-2 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 border-gray-300"
              placeholder="Catatan tambahan…"></textarea>
            @error('create.keterangan') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
          </div>

          <div class="flex items-center justify-end gap-2">
            <button type="button" class="px-4 py-2 rounded-xl border border-gray-300 bg-white hover:bg-gray-50" @click="$dispatch('modal-create-close')">Batal</button>
            <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm" wire:loading.attr="disabled">
              <span wire:loading.remove>Simpan</span>
              <span wire:loading>Memproses…</span>
            </button>
          </div>
        </form>
      </div>

    </div>
  </div>
</div>
