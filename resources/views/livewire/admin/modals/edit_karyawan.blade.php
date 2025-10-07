@once
<style>
  [x-cloak]{display:none!important}
</style>
@endonce

<div id="modalEdit"
     x-data="editModal()"
     x-cloak
     x-show="open"
     x-transition.opacity
     class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/70"
     role="dialog"
     aria-modal="true"
     aria-labelledby="modalEditTitle"
     x-on:modal-edit-open.window="open = true"
     x-on:modal-edit-close.window="close()"
     x-on:open-edit.window="openWith($event.detail)"

     {{-- ✅ sinkron Livewire → Alpine untuk loading & progress upload --}}
     x-on:livewire-upload-start="uploading = true; uploadProgress = 0"
     x-on:livewire-upload-finish="uploading = false; uploadProgress = 0"
     x-on:livewire-upload-error="uploading = false"
     x-on:livewire-upload-progress="uploadProgress = $event.detail.progress"
>

  <div class="absolute inset-0" @click="close()" aria-hidden="true"></div>

  <div class="relative z-10 w-[96%] max-w-2xl" @keydown.window.escape="close()">
    <div class="bg-white rounded-2xl shadow-2xl ring-1 ring-black/5 overflow-hidden"
         x-ref="panel"
         x-transition.scale.origin.center>

      <div class="sticky top-0 z-20 flex items-center justify-between px-5 py-3 bg-gradient-to-r from-emerald-600 to-emerald-500 text-white">
        <div class="flex items-center gap-3">
          <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-white/20">
            <img src="{{ asset('images/edit_white_icon.svg') }}" class="h-6 w-6" alt="edit karyawan">
          </span>
          <h3 id="modalEditTitle" class="text-base sm:text-lg font-semibold">
            Ubah Data Karyawan <span class="font-normal opacity-90" x-text="form.nama ? `— ${form.nama}` : ''"></span>
          </h3>
        </div>
        <button type="button"
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/60"
                aria-label="Tutup modal" @click="close()">
          <img src="{{ asset('images/cancel_icon.svg') }}" class="h-8 w-8" alt="cancel">
        </button>
      </div>

      {{-- Kontainer yang discroll saat ada error --}}
      <div class="max-h-[70vh] overflow-y-auto px-5 py-5" data-scroll-area="edit">
        <form id="formEdit" wire:submit.prevent="update" class="grid grid-cols-1 sm:grid-cols-2 gap-4"
              novalidate enctype="multipart/form-data">
          @csrf

          <input type="hidden" wire:model="edit.id" />
          <input type="hidden" name="_open" value="edit">

          <div class="sm:col-span-2 -mt-1 -mb-1 text-sm text-gray-600">
            <span>Kolom bertanda <span class="text-red-600">*</span> wajib diisi.</span>
          </div>

          {{-- NIK --}}
          <div class="sm:col-span-1">
            <label class="text-sm font-medium text-gray-700">NIK <span class="text-red-600">*</span></label>
            <input type="text" name="nik" wire:model.defer="edit.nik"
                   aria-invalid="{{ $errors->has('edit.nik') ? 'true' : 'false' }}"
                   placeholder="Masukkan NIK"
                   @class([
                        'w-full rounded-xl border bg-white/80 px-3 py-2 focus:outline-none',
                        'focus:ring-emerald-500 focus:border-emerald-500 border-gray-300' => !$errors->has('edit.nik'),
                        'border-red-400 focus:ring-red-500 focus:border-red-500' => $errors->has('edit.nik'),
                   ])>
            @error('edit.nik')
              <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
          </div>

          {{-- Nama --}}
          <div class="sm:col-span-1 sm:col-start-2">
            <label class="text-sm font-medium text-gray-700">Nama <span class="text-red-600">*</span></label>
            <input type="text" name="nama" wire:model.defer="edit.nama"
                   aria-invalid="{{ $errors->has('edit.nama') ? 'true' : 'false' }}"
                   placeholder="Nama lengkap"
                   @class([
                        'mt-1 w-full rounded-xl border bg-white/80 px-3 py-2 focus:outline-none',
                        'focus:ring-emerald-500 focus:border-emerald-500 border-gray-300' => !$errors->has('edit.nama'),
                        'border-red-400 focus:ring-red-500 focus:border-red-500' => $errors->has('edit.nama'),
                   ])>
            @error('edit.nama')
              <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
          </div>

          {{-- Alamat --}}
          <div class="sm:col-span-2">
            <label class="text-sm font-medium text-gray-700">Alamat <span class="text-red-600">*</span></label>
            <input type="text" name="alamat" wire:model.defer="edit.alamat"
                   aria-invalid="{{ $errors->has('edit.alamat') ? 'true' : 'false' }}"
                   placeholder="Jl. Contoh No. 123, Kota"
                   @class([
                        'mt-1 w-full rounded-xl border bg-white/80 px-3 py-2 focus:outline-none',
                        'focus:ring-emerald-500 focus:border-emerald-500 border-gray-300' => !$errors->has('edit.alamat'),
                        'border-red-400 focus:ring-red-500 focus:border-red-500' => $errors->has('edit.alamat'),
                   ])>
            @error('edit.alamat')
              <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
          </div>

          {{-- Email --}}
          <div class="sm:col-span-1">
            <label class="text-sm font-medium text-gray-700">Email <span class="text-red-600">*</span></label>
            <input type="email" name="email" wire:model.defer="edit.email" autocomplete="email"
                   aria-invalid="{{ $errors->has('edit.email') ? 'true' : 'false' }}"
                   placeholder="nama@perusahaan.com"
                   @class([
                        'w-full rounded-xl border bg-white/80 px-3 py-2  focus:outline-none',
                        'focus:ring-emerald-500 focus:border-emerald-500 border-gray-300' => !$errors->has('edit.email'),
                        'border-red-400 focus:ring-red-500 focus:border-red-500' => $errors->has('edit.email'),
                   ])>
            @error('edit.email')
              <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
          </div>

          {{-- Divisi --}}
          <div class="sm:col-span-1 sm:col-start-2">
            <label class="text-sm font-medium text-gray-700">Divisi <span class="text-red-600">*</span></label>
            <input type="text" name="divisi" wire:model.defer="edit.divisi"
                   aria-invalid="{{ $errors->has('edit.divisi') ? 'true' : 'false' }}"
                   placeholder="Mis. Operasional"
                   @class([
                        'mt-1 w-full rounded-xl border bg-white/80 px-3 py-2 focus:outline-none',
                        'focus:ring-emerald-500 focus:border-emerald-500 border-gray-300' => !$errors->has('edit.divisi'),
                        'border-red-400 focus:ring-red-500 focus:border-red-500' => $errors->has('edit.divisi'),
                   ])>
            @error('edit.divisi')
              <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
          </div>

          {{-- Jabatan --}}
          <div class="sm:col-span-1">
            <label class="text-sm font-medium text-gray-700">Jabatan <span class="text-red-600">*</span></label>
            <input type="text" name="jabatan" wire:model.defer="edit.jabatan"
                   aria-invalid="{{ $errors->has('edit.jabatan') ? 'true' : 'false' }}"
                   placeholder="Mis. Supervisor"
                   @class([
                        'mt-1 w-full rounded-xl border bg-white/80 px-3 py-2 focus:outline-none',
                        'focus:ring-emerald-500 focus:border-emerald-500 border-gray-300' => !$errors->has('edit.jabatan'),
                        'border-red-400 focus:ring-red-500 focus:border-red-500' => $errors->has('edit.jabatan'),
                   ])>
            @error('edit.jabatan')
              <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
          </div>

          {{-- Foto --}}
          <div class="sm:col-span-2">
            <label class="text-sm font-medium text-gray-700">Foto (opsional)</label>
            <div class="mt-1 grid grid-cols-1 sm:grid-cols-3 gap-3">
              <div class="sm:col-span-2">
                <input type="file" name="foto" id="editFoto" x-ref="fotoInput"
                       wire:model="edit.foto" accept=".jpg,.jpeg,.png,.webp" @change="onFotoChange"
                       aria-invalid="{{ $errors->has('edit.foto') ? 'true' : 'false' }}"
                       @class([
                            'w-full rounded-xl border border-dashed bg-white/60 px-3 py-2 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-600 file:px-3 file:py-1.5 file:text-white hover:border-emerald-300 focus:outline-none',
                            'border-gray-300 focus:ring-emerald-500 focus:border-emerald-500' => !$errors->has('edit.foto'),
                            'border-red-400 focus:ring-red-500 focus:border-red-500' => $errors->has('edit.foto'),
                       ])>

                {{-- ✅ progress upload --}}
                <div class="mt-2" x-show="uploading" x-transition>
                  <div class="h-2 w-full rounded bg-gray-200 overflow-hidden">
                    <div class="h-2 bg-emerald-500" :style="`width:${uploadProgress}%;`"></div>
                  </div>
                  <p class="mt-1 text-xs text-gray-500">
                    Mengunggah foto… <span x-text="uploadProgress + '%'"></span>
                  </p>
                </div>

                <p class="mt-1 text-xs text-gray-500">
                  Maks 2 MB • JPG/PNG/WebP. Tidak memilih file = tetap pakai foto lama.
                </p>
                @error('edit.foto')
                  <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
              </div>

              {{-- ✅ lindungi preview dari morph/re-render Livewire --}}
              <div class="sm:col-span-1" wire:ignore>
                <div class="aspect-square rounded-xl border border-gray-200 bg-gray-50 overflow-hidden grid place-items-center">
                  <img x-ref="fotoPreview" alt="Preview Foto" class="hidden h-full w-full object-cover" />
                  <span x-ref="fotoEmpty" class="text-xs text-gray-400">Preview</span>
                </div>
              </div>
            </div>
          </div>

          {{-- Role --}}
          <div class="sm:col-span-2">
            <label class="text-sm font-medium text-gray-700">Role <span class="text-red-600">*</span></label>
            <select name="role" wire:model.defer="edit.role"
                    aria-invalid="{{ $errors->has('edit.role') ? 'true' : 'false' }}"
                    @class([
                        'mt-1 w-full rounded-xl border bg-white/80 px-3 py-2 focus:outline-none',
                        'focus:ring-emerald-500 focus:border-emerald-500 border-gray-300' => !$errors->has('edit.role'),
                        'border-red-400 focus:ring-red-500 focus:border-red-500' => $errors->has('edit.role'),
                    ])>
              <option value="">— Pilih Role —</option>
              <option value="admin">Admin</option>
              <option value="karyawan">Karyawan</option>
            </select>
            @error('edit.role')
              <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
          </div>

          {{-- Footer --}}
          <div class="sm:col-span-2">
            <div class="sticky bottom-0 z-20 mt-2 bg-white px-5 py-3 border-t border-gray-100 flex items-center justify-end gap-2">
              <button type="button"
                      class="px-4 py-2 rounded-xl border border-gray-300 bg-white hover:bg-gray-50"
                      @click="close()">Batal</button>

              <button type="submit"
                      class="px-4 py-2 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm"
                      wire:loading.attr="disabled"
                      wire:target="update,edit.foto"
                      :disabled="uploading">
                <span wire:loading.remove wire:target="update,edit.foto">Update</span>
                <span wire:loading wire:target="update,edit.foto">Memproses…</span>
              </button>
            </div>
          </div>
        </form>
      </div>

    </div>
  </div>
</div>

<script>
function editModal() {
  return {
    open: false,
    uploading: false,       // ✅ state untuk progress upload
    uploadProgress: 0,      // ✅ nilai progress 0-100

    form: { id:'', nik:'', nama:'', alamat:'', email:'', divisi:'', jabatan:'', role:'' },

    resetPreview() {
      const input = this.$refs.fotoInput, img = this.$refs.fotoPreview, empty = this.$refs.fotoEmpty;
      if (input) input.value = '';
      if (img) { img.src = ''; img.classList.add('hidden'); }
      if (empty) empty.classList.remove('hidden');
    },
    onFotoChange(e) {
      const file = e.target.files?.[0], img = this.$refs.fotoPreview, empty = this.$refs.fotoEmpty;
      if (!file) { this.resetPreview(); return; }
      const url = URL.createObjectURL(file);
      img.src = url;
      img.onload = () => URL.revokeObjectURL(url);
      img.classList.remove('hidden');
      empty.classList.add('hidden');
    },
    openWith(p) {
      this.form.id = p?.id ?? '';
      this.form.nik = p?.nik ?? '';
      this.form.nama = p?.nama ?? '';
      this.form.alamat = p?.alamat ?? '';
      this.form.email = p?.email ?? '';
      this.form.divisi = p?.divisi ?? '';
      this.form.jabatan = p?.jabatan ?? '';
      this.form.role = p?.role ?? '';
      this.resetPreview();
      this.open = true;
      this.$nextTick(() => {
        this.$refs.panel?.animate?.(
          [{ transform:'scale(0.98)', opacity:0 }, { transform:'scale(1)', opacity:1 }],
          { duration:160, easing:'cubic-bezier(.2,.8,.2,1)' }
        );
        const first = document.querySelector('#formEdit input, #formEdit select, #formEdit textarea, #formEdit button');
        first?.focus?.();
      });
    },
    close() {
      this.open = false;
      this.resetPreview();
      this.uploading = false;      // ✅ bersihkan state ketika modal ditutup
      this.uploadProgress = 0;
      $wire.resetEditForm?.();
    }
  }
}
</script>
