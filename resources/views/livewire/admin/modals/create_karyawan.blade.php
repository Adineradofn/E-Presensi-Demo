<!--
  Modal "Tambah Karyawan" (Create) + PROGRESS UPLOAD
  - Alpine: state & interaksi
  - Livewire: submit + upload file (create.foto)
  - Tambahan: indikator loading/progress saat upload, lindungi preview dari re-render (wire:ignore),
              disable tombol submit saat upload berlangsung.
-->
<div id="modalCreate"
     x-data="{
        // ======== STATE DASAR ========
        open: false,              // apakah modal terbuka
        previewUrl: '',           // URL blob preview gambar yang dipilih
        action: '',               // (opsional) action form jika perlu (sumber: #routeTpl)
        showPass: false,          // toggle tampil/sembunyi password
        showPassConfirm: false,   // toggle tampil/sembunyi konfirmasi password

        // ======== STATE UPLOAD (Livewire → Alpine) ========
        uploading: false,         // true ketika Livewire sedang mengupload berkas
        uploadProgress: 0,        // angka 0..100 progres upload

        // Ambil route/action dari elemen #routeTpl (opsional)
        $routeTpl() { return document.getElementById('routeTpl')?.dataset?.create || ''; },

        // Buka modal: set action (opsional), buka, lalu fokus ke input pertama
        openModal() {
            this.action = this.$routeTpl();
            this.open = true;
            this.$nextTick(() => {
                const first = document.querySelector('#formCreate input, #formCreate select, #formCreate textarea, #formCreate button');
                first?.focus?.();
            });
        },

        // Tutup modal: sembunyikan, bersihkan preview & toggle, reset state Livewire form
        close() {
            this.open = false;
            this.resetPreview();
            this.showPass = false;
            this.showPassConfirm = false;
            this.uploading = false;       // pastikan reset state upload
            this.uploadProgress = 0;
            $wire.resetCreateForm?.();
        },

        // Reset form HTML (fallback) + bersihkan preview + reset Livewire state
        resetForm() {
            const form = document.getElementById('formCreate');
            form?.reset?.();
            this.resetPreview();
            this.showPass = false;
            this.showPassConfirm = false;
            this.uploading = false;
            this.uploadProgress = 0;
            $wire.resetCreateForm?.();
        },

        // Hapus preview blob & kosongkan input file
        resetPreview() {
            const input = this.$refs?.fotoInput;
            if (this.previewUrl) { try { URL.revokeObjectURL(this.previewUrl); } catch (e) {} }
            this.previewUrl = '';
            if (input) input.value = '';
        },

        // Saat file foto berubah: buat objectURL untuk preview; jika batal → reset preview
        onFotoChange(e) {
            const file = e.target.files?.[0];
            if (!file) { this.resetPreview(); return; }
            if (this.previewUrl) { try { URL.revokeObjectURL(this.previewUrl); } catch (e) {} }
            this.previewUrl = URL.createObjectURL(file);
        }
     }"
     x-cloak
     x-show="open"
     x-transition.opacity
     class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/70"
     role="dialog" aria-modal="true" aria-labelledby="modalCreateTitle"

     {{-- Buka/tutup via CustomEvent dari luar --}}
     x-on:modal-create-open.window="openModal()"
     x-on:modal-create-close.window="close()"

     {{-- ✅ sinkron Livewire → Alpine untuk loading & progress upload --}}
     x-on:livewire-upload-start="uploading = true; uploadProgress = 0"
     x-on:livewire-upload-finish="uploading = false; uploadProgress = 0"
     x-on:livewire-upload-error="uploading = false"
     x-on:livewire-upload-progress="uploadProgress = $event.detail.progress"
>

    <!-- Backdrop: klik di luar panel menutup modal -->
    <div class="absolute inset-0" @click="close()" aria-hidden="true"></div>

    <!-- Panel modal -->
    <div class="relative z-10 w-[96%] max-w-2xl" @keydown.window.escape="close()">
        <div class="bg-white rounded-2xl shadow-2xl ring-1 ring-black/5 overflow-hidden" x-ref="panel"
             x-transition.scale.origin.center>

            <!-- Header: judul + tombol tutup -->
            <div class="sticky top-0 z-20 flex items-center justify-between px-5 py-3 bg-gradient-to-r from-emerald-600 to-emerald-500 text-white">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-white/20">
                        <img src="{{ asset('images/add_icon.svg') }}" class="h-5 w-5" alt="add">
                    </span>
                    <h3 id="modalCreateTitle" class="text-base sm:text-lg font-semibold">Tambah Karyawan</h3>
                </div>
                <button type="button"
                        class="inline-flex h-9 w-9 items-center justify-center rounded-lg hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/60"
                        aria-label="Tutup modal" @click="close()">
                    <img src="{{ asset('images/cancel_icon.svg') }}" class="h-8 w-8" alt="cancel">
                </button>
            </div>

            <!-- Body: konten bisa discroll (agar header/footer tetap) -->
            <div class="max-h-[70vh] overflow-y-auto px-5 py-5" data-scroll-area="create">
                <form id="formCreate"
                      wire:submit.prevent="store"
                      class="grid grid-cols-1 sm:grid-cols-2 gap-4"
                      novalidate
                      enctype="multipart/form-data">

                    @csrf
                    <!-- Penanda untuk membuka ulang tab/form pada redirect (opsional) -->
                    <input type="hidden" name="_open" value="create">

                    <!-- Informasi bantuan -->
                    <div class="sm:col-span-2 -mt-1 -mb-1 text-sm text-gray-600">
                        <span>Kolom bertanda <span class="text-red-600">*</span> wajib diisi.</span>
                    </div>

                    <!-- NIK -->
                    <div class="sm:col-span-1">
                        <label class="text-sm font-medium text-gray-700">NIK <span class="text-red-600">*</span></label>
                        <input type="text" name="nik" wire:model.defer="create.nik"
                               aria-invalid="{{ $errors->has('create.nik') ? 'true' : 'false' }}"
                               placeholder="Masukkan NIK"
                               @class([
                                    'w-full rounded-xl border bg-white/80 px-3 py-2 focus:outline-none',
                                    'focus:ring-emerald-500 focus:border-emerald-500 border-gray-300' => !$errors->has('create.nik'),
                                    'border-red-400 focus:ring-red-500 focus:border-red-500' => $errors->has('create.nik'),
                               ])
                               autofocus>
                        @error('create.nik')
                            <p class="mt-1 text-xs text-red-600" data-create-error>{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nama -->
                    <div class="sm:col-span-1 sm:col-start-2">
                        <label class="text-sm font-medium text-gray-700">Nama <span class="text-red-600">*</span></label>
                        <input type="text" name="nama" wire:model.defer="create.nama"
                               aria-invalid="{{ $errors->has('create.nama') ? 'true' : 'false' }}"
                               placeholder="Nama lengkap"
                               @class([
                                    'mt-1 w-full rounded-xl border bg-white/80 px-3 py-2 focus:outline-none',
                                    'focus:ring-emerald-500 focus:border-emerald-500 border-gray-300' => !$errors->has('create.nama'),
                                    'border-red-400 focus:ring-red-500 focus:border-red-500' => $errors->has('create.nama'),
                               ])>
                        @error('create.nama')
                            <p class="mt-1 text-xs text-red-600" data-create-error>{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Alamat -->
                    <div class="sm:col-span-2">
                        <label class="text-sm font-medium text-gray-700">Alamat <span class="text-red-600">*</span></label>
                        <input type="text" name="alamat" wire:model.defer="create.alamat"
                               aria-invalid="{{ $errors->has('create.alamat') ? 'true' : 'false' }}"
                               placeholder="Jl. Contoh No. 123, Kota"
                               @class([
                                    'mt-1 w-full rounded-xl border bg-white/80 px-3 py-2 focus:outline-none',
                                    'focus:ring-emerald-500 focus:border-emerald-500 border-gray-300' => !$errors->has('create.alamat'),
                                    'border-red-400 focus:ring-red-500 focus:border-red-500' => $errors->has('create.alamat'),
                               ])>
                        @error('create.alamat')
                            <p class="mt-1 text-xs text-red-600" data-create-error>{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="sm:col-span-1">
                        <label class="text-sm font-medium text-gray-700">Email <span class="text-red-600">*</span></label>
                        <input type="email" name="email" wire:model.defer="create.email" autocomplete="email"
                               aria-invalid="{{ $errors->has('create.email') ? 'true' : 'false' }}"
                               placeholder="nama@perusahaan.com"
                               @class([
                                    'w-full rounded-xl border bg-white/80 px-3 py-2 focus:outline-none',
                                    'focus:ring-emerald-500 focus:border-emerald-500 border-gray-300' => !$errors->has('create.email'),
                                    'border-red-400 focus:ring-red-500 focus:border-red-500' => $errors->has('create.email'),
                               ])>
                        @error('create.email')
                            <p class="mt-1 text-xs text-red-600" data-create-error>{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password + Konfirmasi: dengan toggle tampil/sembunyi -->
                    <fieldset class="sm:col-span-2">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                            <!-- Password -->
                            <div>
                                <label class="text-sm font-medium text-gray-700">Password <span class="text-red-600">*</span></label>
                                <div class="relative mt-1">
                                    <input :type="showPass ? 'text' : 'password'"
                                           name="password" id="createPass"
                                           wire:model.defer="create.password"
                                           autocomplete="new-password"
                                           aria-invalid="{{ $errors->has('create.password') ? 'true' : 'false' }}"
                                           placeholder="Minimal 6 karakter"
                                           @class([
                                                'w-full rounded-xl border bg-white/80 px-3 py-2 pr-10 focus:outline-none',
                                                'focus:ring-emerald-500 focus:border-emerald-500 border-gray-300' => !$errors->has('create.password'),
                                                'border-red-400 focus:ring-red-500 focus:border-red-500' => $errors->has('create.password'),
                                           ])>
                                    <!-- Tombol toggle mata -->
                                    <button type="button"
                                            class="absolute inset-y-0 right-0 px-3 text-gray-500 hover:text-gray-700"
                                            :aria-label="showPass ? 'Sembunyikan password' : 'Tampilkan password'"
                                            :aria-pressed="showPass"
                                            @click="showPass = !showPass">
                                        <img x-cloak x-show="!showPass" src="{{ asset('images/eye_icon.svg') }}" class="h-5 w-5" alt="Tampilkan password">
                                        <img x-cloak x-show="showPass"  src="{{ asset('images/eye_hide_icon.svg') }}" class="h-5 w-5" alt="Sembunyikan password">
                                    </button>
                                </div>
                                <p class="mt-1 text-xs text-gray-500">Minimal 6 karakter, wajib 1 huruf besar & 1 angka.</p>
                                @error('create.password')
                                    <p class="mt-1 text-xs text-red-600" data-create-error>{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Konfirmasi Password -->
                            <div>
                                <label class="text-sm font-medium text-gray-700">Konfirmasi Password <span class="text-red-600">*</span></label>
                                <div class="relative mt-1">
                                    <input :type="showPassConfirm ? 'text' : 'password'"
                                           name="password_confirmation" id="createPassConfirm"
                                           wire:model.defer="create.password_confirmation"
                                           autocomplete="new-password"
                                           aria-invalid="{{ $errors->has('create.password_confirmation') ? 'true' : 'false' }}"
                                           placeholder="Ulangi password"
                                           @class([
                                                'w-full rounded-xl border bg-white/80 px-3 py-2 pr-10 focus:outline-none',
                                                'focus:ring-emerald-500 focus:border-emerald-500 border-gray-300' => !$errors->has('create.password_confirmation'),
                                                'border-red-400 focus:ring-red-500 focus:border-red-500' => $errors->has('create.password_confirmation'),
                                           ])>
                                    <!-- Tombol toggle mata -->
                                    <button type="button"
                                            class="absolute inset-y-0 right-0 px-3 text-gray-500 hover:text-gray-700"
                                            :aria-label="showPassConfirm ? 'Sembunyikan password' : 'Tampilkan password'"
                                            :aria-pressed="showPassConfirm"
                                            @click="showPassConfirm = !showPassConfirm">
                                        <img x-cloak x-show="!showPassConfirm" src="{{ asset('images/eye_icon.svg') }}" class="h-5 w-5" alt="Tampilkan password">
                                        <img x-cloak x-show="showPassConfirm"  src="{{ asset('images/eye_hide_icon.svg') }}" class="h-5 w-5" alt="Sembunyikan password">
                                    </button>
                                </div>
                                @error('create.password_confirmation')
                                    <p class="mt-1 text-xs text-red-600" data-create-error>{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </fieldset>

                    <!-- Foto (opsional) + Preview + PROGRESS -->
                    <div class="sm:col-span-2">
                        <label class="text-sm font-medium text-gray-700">Foto (opsional)</label>
                        <div class="mt-1 grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div class="sm:col-span-2">
                                <input type="file" name="foto" id="createFoto" x-ref="fotoInput"
                                       wire:model="create.foto"
                                       accept=".jpg,.jpeg,.png,.webp"
                                       @change="onFotoChange"
                                       aria-invalid="{{ $errors->has('create.foto') ? 'true' : 'false' }}"
                                       @class([
                                            'w-full rounded-xl border border-dashed bg-white/60 px-3 py-2 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-600 file:px-3 file:py-1.5 file:text-white hover:border-emerald-300 focus:outline-none',
                                            'border-gray-300 focus:ring-emerald-500 focus:border-emerald-500' => !$errors->has('create.foto'),
                                            'border-red-400 focus:ring-red-500 focus:border-red-500' => $errors->has('create.foto'),
                                       ])>

                                {{-- ✅ progress upload (Livewire → Alpine) --}}
                                <div class="mt-2" x-show="uploading" x-transition>
                                  <div class="h-2 w-full rounded bg-gray-200 overflow-hidden">
                                    <div class="h-2 bg-emerald-500" :style="`width:${uploadProgress}%;`"></div>
                                  </div>
                                  <p class="mt-1 text-xs text-gray-500">
                                    Mengunggah foto… <span x-text="uploadProgress + '%'"></span>
                                  </p>
                                </div>

                                <p class="mt-1 text-xs text-gray-500">Maks 2 MB • JPG/PNG/WebP.</p>
                                @error('create.foto')
                                    <p class="mt-1 text-xs text-red-600" data-create-error>{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Kotak preview persegi: wire:ignore agar tidak di-morph Livewire -->
                            <div class="sm:col-span-1" wire:ignore>
                                <div class="aspect-square rounded-xl border border-gray-200 bg-gray-50 overflow-hidden grid place-items-center">
                                    <img :src="previewUrl" x-show="!!previewUrl" alt="Preview Foto"
                                         class="h-full w-full object-cover" />
                                    <span x-show="!previewUrl" class="text-xs text-gray-400">Preview</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Divisi -->
                    <div class="sm:col-span-1">
                        <label class="text-sm font-medium text-gray-700">Divisi <span class="text-red-600">*</span></label>
                        <input type="text" name="divisi" wire:model.defer="create.divisi"
                               aria-invalid="{{ $errors->has('create.divisi') ? 'true' : 'false' }}"
                               placeholder="Mis. Operasional"
                               @class([
                                    'mt-1 w-full rounded-xl border bg-white/80 px-3 py-2 focus:outline-none',
                                    'focus:ring-emerald-500 focus:border-emerald-500 border-gray-300' => !$errors->has('create.divisi'),
                                    'border-red-400 focus:ring-red-500 focus:border-red-500' => $errors->has('create.divisi'),
                               ])>
                        @error('create.divisi')
                            <p class="mt-1 text-xs text-red-600" data-create-error>{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Jabatan -->
                    <div class="sm:col-span-1">
                        <label class="text-sm font-medium text-gray-700">Jabatan <span class="text-red-600">*</span></label>
                        <input type="text" name="jabatan" wire:model.defer="create.jabatan"
                               aria-invalid="{{ $errors->has('create.jabatan') ? 'true' : 'false' }}"
                               placeholder="Mis. Supervisor"
                               @class([
                                    'mt-1 w-full rounded-xl border bg-white/80 px-3 py-2 focus:outline-none',
                                    'focus:ring-emerald-500 focus:border-emerald-500 border-gray-300' => !$errors->has('create.jabatan'),
                                    'border-red-400 focus:ring-red-500 focus:border-red-500' => $errors->has('create.jabatan'),
                               ])>
                        @error('create.jabatan')
                            <p class="mt-1 text-xs text-red-600" data-create-error>{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Role -->
                    <div class="sm:col-span-2">
                        <label class="text-sm font-medium text-gray-700">Role <span class="text-red-600">*</span></label>
                        <select name="role" wire:model.defer="create.role"
                                aria-invalid="{{ $errors->has('create.role') ? 'true' : 'false' }}"
                                @class([
                                    'mt-1 w-full rounded-xl border bg-white/80 px-3 py-2 focus:outline-none',
                                    'focus:ring-emerald-500 focus:border-emerald-500 border-gray-300' => !$errors->has('create.role'),
                                    'border-red-400 focus:ring-red-500 focus:border-red-500' => $errors->has('create.role'),
                                ])>
                            <option value="">— Pilih Role —</option>
                            <option value="admin">Admin</option>
                            <option value="karyawan">Karyawan</option>
                        </select>
                        @error('create.role')
                            <p class="mt-1 text-xs text-red-600" data-create-error>{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Footer aksi (sticky) -->
                    <div class="sm:col-span-2">
                        <div class="sticky bottom-0 z-20 mt-2 bg-white px-5 py-3 border-t border-gray-100 flex items-center justify-end gap-2">
                            <!-- Reset semua field + preview -->
                            <button type="button"
                                    class="px-4 py-2 rounded-xl border border-gray-300 bg-white hover:bg-gray-50"
                                    @click.prevent="resetForm()">Hapus</button>

                            <!-- Submit ke Livewire::store; disabled saat loading/upload -->
                            <button type="submit"
                                    class="px-4 py-2 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm"
                                    wire:loading.attr="disabled"
                                    wire:target="store,create.foto"
                                    :disabled="uploading">
                                <span wire:loading.remove wire:target="store,create.foto">Simpan</span>
                                <span wire:loading wire:target="store,create.foto">Memproses…</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>
