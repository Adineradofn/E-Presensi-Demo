<div
  id="modalSelfEdit"
  x-data="selfEditModal()"
  x-cloak
  x-show="open"
  x-transition.opacity
  class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/70"
  role="dialog"
  aria-modal="true"
  aria-labelledby="modalSelfEditTitle"
  x-on:self-profile:edit-open.window="openFromAuth()"
  x-on:self-profile:edit-close.window="close()"
  x-init="
    window.addEventListener('self-profile:edit-open', () => openFromAuth());
    window.addEventListener('self-profile:edit-close', () => close());
  "

  x-on:livewire-upload-start="uploading = true; uploadProgress = 0"
  x-on:livewire-upload-finish="uploading = false; uploadProgress = 0"
  x-on:livewire-upload-error="uploading = false"
  x-on:livewire-upload-progress="uploadProgress = $event.detail.progress"
>
  <div class="absolute inset-0" @click="close()" aria-hidden="true"></div>

  <div class="relative z-10 w-[96%] max-w-2xl" @keydown.window.escape="close()">
    <div class="bg-white rounded-2xl shadow-2xl ring-1 ring-black/5 overflow-hidden" x-ref="panel" x-transition.scale.origin.center>

      <div class="sticky top-0 z-20 flex items-center justify-between px-5 py-3 bg-gradient-to-r from-emerald-600 to-emerald-500 text-white">
        <div class="flex items-center gap-3">
          <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-white/20">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
              <path d="M12 6v12M6 12h12" stroke-width="2" stroke-linecap="round" />
            </svg>
          </span>
          <h3 id="modalSelfEditTitle" class="text-base sm:text-lg font-semibold">
            Ubah Profil <span class="font-normal opacity-90"></span>
          </h3>
        </div>
        <button type="button" class="inline-flex h-9 w-9 items-center justify-center rounded-lg hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/60" aria-label="Tutup modal" @click="close()">
          <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
            <path d="M6 6l12 12M6 18L18 6" stroke-width="2" stroke-linecap="round" />
          </svg>
        </button>
      </div>

      <div class="max-h-[70vh] overflow-y-auto px-5 py-5">
        <form id="formSelfEdit"
              wire:submit.prevent="updateSelf"
              class="grid grid-cols-1 sm:grid-cols-2 gap-4"
              novalidate
              enctype="multipart/form-data">

          <input type="hidden" wire:model="edit.id" />

          <div class="sm:col-span-2 -mt-1 -mb-1 text-sm text-gray-600">
            <span>Kolom bertanda <span class="text-red-600">*</span> wajib diisi.</span>
          </div>

          {{-- Nama --}}
          <div class="sm:col-span-1">
            <label class="text-sm font-medium text-gray-700">Nama <span class="text-red-600">*</span></label>
            <input type="text" name="nama" wire:model.defer="edit.nama" placeholder="Nama lengkap"
                   @class([
                     'mt-1 w-full rounded-xl border bg-white/80 px-3 py-2 focus:outline-none',
                     'focus:ring-emerald-500 focus:border-emerald-500 border-gray-300' => !$errors->has('edit.nama'),
                     'border-red-400 focus:ring-red-500 focus:border-red-500' => $errors->has('edit.nama'),
                   ])>
            @error('edit.nama') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Email --}}
          <div class="sm:col-span-1">
            <label class="text-sm font-medium text-gray-700">Email <span class="text-red-600">*</span></label>
            <div class="relative mt-1">
              <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                <svg class="h-4 w-4 text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                  <path d="M4 6l8 6 8-6" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  <rect x="4" y="6" width="16" height="12" rx="2" ry="2" stroke-width="2" />
                </svg>
              </span>
              <input type="email" name="email" wire:model.defer="edit.email" autocomplete="email" placeholder="nama@perusahaan.com"
                     @class([
                       'w-full rounded-xl border bg-white/80 px-3 py-2 pl-10 focus:outline-none',
                       'focus:ring-emerald-500 focus:border-emerald-500 border-gray-300' => !$errors->has('edit.email'),
                       'border-red-400 focus:ring-red-500 focus:border-red-500' => $errors->has('edit.email'),
                     ])>
            </div>
            @error('edit.email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Alamat --}}
          <div class="sm:col-span-2">
            <label class="text-sm font-medium text-gray-700">Alamat <span class="text-red-600">*</span></label>
            <input type="text" name="alamat" wire:model.defer="edit.alamat" placeholder="Jl. Contoh No. 123, Kota"
                   @class([
                     'mt-1 w-full rounded-xl border bg-white/80 px-3 py-2 focus:outline-none',
                     'focus:ring-emerald-500 focus:border-emerald-500 border-gray-300' => !$errors->has('edit.alamat'),
                     'border-red-400 focus:ring-red-500 focus:border-red-500' => $errors->has('edit.alamat'),
                   ])>
            @error('edit.alamat') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
          </div>

          {{-- Foto (opsional) --}}
          <div class="sm:col-span-2">
            <label class="text-sm font-medium text-gray-700">Foto (opsional)</label>
            <div class="mt-1 grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div class="sm:col-span-2">
                <div class="flex items-center gap-2">
                  <input
                    type="file"
                    name="foto"
                    id="selfEditFoto"
                    x-ref="fotoInput"
                    wire:model="edit.foto"
                    accept=".jpg,.jpeg,.png,.webp"
                    @change="onFotoChange"
                    @class([
                      'w-full rounded-xl border border-dashed bg-white/60 px-3 py-2 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-600 file:px-3 file:py-1.5 file:text-white hover:border-emerald-300 focus:outline-none',
                      'border-gray-300 focus:ring-emerald-500 focus:border-emerald-500' => !$errors->has('edit.foto'),
                      'border-red-400 focus:ring-red-500 focus:border-red-500' => $errors->has('edit.foto'),
                    ])>

                  {{-- Tombol Hapus Foto (ikon saja) --}}
                  <button
                    type="button"
                    class="inline-flex items-center justify-center h-10 w-10 rounded-xl border
                           border-rose-200 text-rose-600 hover:bg-rose-50 focus:outline-none focus:ring-2 focus:ring-rose-300"
                    @click="removeCurrentPhoto()"
                    title="Hapus foto"
                    aria-label="Hapus foto"
                  >
                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" aria-hidden="true">
                      <path d="M3 6h18M8 6V4h8v2M6 6l1 14h10l1-14" stroke-width="2" stroke-linecap="round" />
                    </svg>
                    <span class="sr-only">Hapus Foto</span>
                  </button>
                </div>

                {{-- progress upload --}}
                <div class="mt-2" x-show="uploading" x-transition>
                  <div class="h-2 w-full rounded bg-gray-200 overflow-hidden">
                    <div class="h-2 bg-emerald-500" :style="`width:${uploadProgress}%;`"></div>
                  </div>
                  <p class="mt-1 text-xs text-gray-500">Mengunggah foto… <span x-text="uploadProgress + '%'"></span></p>
                </div>

                <p class="mt-1 text-xs text-gray-500">
                  Jika <strong>preview kosong</strong> saat disimpan, maka foto Anda akan <strong>dihapus</strong>.
                </p>
                @error('edit.foto') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
              </div>

              {{-- preview (wire:ignore agar tidak ke-reinit oleh Livewire morph) --}}
              <div class="sm:col-span-1" wire:ignore>
                <div class="aspect-square rounded-xl border border-gray-200 bg-gray-50 overflow-hidden grid place-items-center">
                  <img x-ref="fotoPreview" alt="Preview Foto" class="hidden h-full w-full object-cover" />
                  <span x-ref="fotoEmpty" class="text-xs text-gray-400">Preview</span>
                </div>
              </div>
            </div>
          </div>

          {{-- Footer --}}
          <div class="sm:col-span-2">
            <div class="sticky bottom-0 z-20 mt-2 bg-white px-5 py-3 border-t border-gray-100 flex items-center justify-end gap-2">
              <button type="button" class="px-4 py-2 rounded-xl border border-gray-300 bg-white hover:bg-gray-50" @click="close()">Batal</button>

              <button
                type="submit"
                class="px-4 py-2 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm"
                wire:loading.attr="disabled"
                wire:target="updateSelf,edit.foto"
                :disabled="uploading"
              >
                <span wire:loading.remove wire:target="updateSelf,edit.foto">Update</span>
                <span wire:loading wire:target="updateSelf,edit.foto">Memproses…</span>
              </button>
            </div>
          </div>

        </form>
      </div>

    </div>
  </div>
</div>

<script>
function selfEditModal() {
  return {
    open: false,
    uploading: false,
    uploadProgress: 0,

    setPreview(url) {
      const img = this.$refs.fotoPreview, empty = this.$refs.fotoEmpty;
      if (!img || !empty) return;
      if (url) {
        img.src = url;
        img.onload = () => { try { URL.revokeObjectURL(url); } catch(e) {} };
        img.classList.remove('hidden');
        empty.classList.add('hidden');
      } else {
        img.src = '';
        img.classList.add('hidden');
        empty.classList.remove('hidden');
      }
    },

    resetPreview() {
      const input = this.$refs.fotoInput;
      if (input) input.value = '';
      this.setPreview(null);
    },

    onFotoChange(e) {
      const file = e.target.files?.[0];
      if (!file) {
        this.resetPreview();
        this.$wire.set('removePhoto', true);
        return;
      }
      const url = URL.createObjectURL(file);
      this.setPreview(url);
      this.$wire.set('removePhoto', false); // ada file baru => jangan hapus
    },

    async removeCurrentPhoto() {
      this.resetPreview();
      await this.$wire.set('removePhoto', true);
    },

    async openFromAuth() {
      await this.$wire.fillFromAuth();

      try {
        const url = await this.$wire.getCurrentPhotoUrl();
        if (url) {
          this.setPreview(url);
          await this.$wire.set('removePhoto', false);
        } else {
          this.resetPreview();
          await this.$wire.set('removePhoto', true);
        }
      } catch (_) {
        this.resetPreview();
      }

      this.open = true;
      this.$nextTick(() => {
        this.$refs.panel?.animate?.(
          [{ transform: 'scale(0.98)', opacity: 0 }, { transform: 'scale(1)', opacity: 1 }],
          { duration: 160, easing: 'cubic-bezier(.2,.8,.2,1)' }
        );
        document.querySelector('#formSelfEdit input, #formSelfEdit select')?.focus?.();
      });
    },

    close() {
      this.open = false;
      this.resetPreview();
      this.$wire.resetEditForm?.();
    }
  }
}
</script>
