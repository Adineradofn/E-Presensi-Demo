{{-- resources/views/user/pengajuan_izin_user.blade.php --}}
@extends('user.app_user')

@section('content')
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-3">
            <div
                class="h-10 w-10 rounded-2xl bg-gradient-to-br from-sky-500 to-emerald-500 text-white
                  flex items-center justify-center shadow-md">
                {{-- clipboard-check icon --}}
                <img src="{{ asset('images/pengajuan_izin_white_icon.svg') }}" class="h-8 w-8" alt="">
            </div>
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900">Pengajuan Izin</h1>
            </div>
        </div>
    </div>

    {{-- 
        x-data: Alpine store untuk logika form di sisi UI (tanpa mengubah desain) 
        - Menambah dukungan jenis: izin, sakit, cuti, izin terlambat, tugas
        - Jika jenis = "izin terlambat" => tanggal selesai = tanggal mulai (dikunci)
        - Min tanggal = today (front-end guard; back-end juga validasi)
    --}}
    <div 
        class="max-w-2xl mx-auto p-4"
        x-data="izinForm()"
        x-init="init($el.dataset.mulai, $el.dataset.akhir, $el.dataset.alasan, $el.dataset.today, $el.dataset.jenis)"
        data-mulai="{{ old('tanggal_mulai') }}"
        data-akhir="{{ old('tanggal_selesai') }}"
        data-alasan="{{ old('alasan') }}"
        data-jenis="{{ old('jenis') }}"
        data-today="{{ now()->toDateString() }}"
    >
        {{-- Flash (Success & Error Global) --}}
        @if (session('success'))
            <div class="mb-4 p-3 rounded-xl bg-emerald-50 text-emerald-700 border border-emerald-200">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 p-3 rounded-xl bg-red-50 text-red-700 border border-red-200">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-2xl border shadow-sm">
            {{-- Header --}}
            <div class="px-5 pt-5 pb-3 border-b flex items-center justify-between">
                <div>
                    <h1 class="text-xl font-semibold">Pengajuan Izin</h1>
                    <p class="text-sm text-gray-500 mt-1">
                        Ajukan izin/sakit/cuti atau izin terlambat/tugas. Bukti (foto/PDF) dapat dilampirkan bila perlu.
                    </p>
                </div>
            </div>

            {{-- Form --}}
            <form method="POST" action="{{ route('user.izin.store') }}" enctype="multipart/form-data" novalidate
                  class="p-5 space-y-4">
                @csrf

                {{-- Jenis Izin (PERBAIKAN: ejaan & pilihan lengkap) --}}
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Jenis Izin <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select 
                            name="jenis"
                            x-model="jenis"
                            x-on:change="onJenisChange()"
                            class="w-full rounded-xl border border-gray-300 px-4 py-2.5 pr-10 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none"
                            required
                        >
                            <option value="">— pilih —</option>
                            <option value="izin">Izin</option>
                            <option value="sakit">Sakit</option>
                            <option value="cuti">Cuti</option>
                            <option value="izin terlambat">Izin Terlambat (1 hari)</option>
                            <option value="tugas">Tugas</option>
                        </select>
                    </div>
                    @error('jenis')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror

                    {{-- Hint singkat sesuai kebijakan (informasi, tidak mengubah desain) --}}
                    <ul class="mt-2 text-[12px] text-gray-500 list-disc list-inside space-y-1">
                        <li x-show="jenis==='izin terlambat'">Izin terlambat dibatasi <b>1 hari</b>. Sistem akan mengunci tanggal selesai = tanggal mulai.</li>
                        <li x-show="jenis==='tugas'">Saat <b>tugas disetujui</b>, presensi diblokir dan status kehadiran dianggap hadir.</li>
                        <li>Jika <b>range tanggal</b> sudah memiliki pengajuan lain, sistem akan <b>menolak</b> (tidak bisa dobel pengajuan).</li>
                    </ul>
                </div>

                {{-- Tanggal Mulai & Selesai --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tanggal Mulai <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input 
                                type="date" 
                                name="tanggal_mulai" 
                                x-model="mulai" 
                                x-on:change="onMulaiChange()"
                                :min="today"
                                class="w-full rounded-xl border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none"
                                required
                            >
                        </div>
                        @error('tanggal_mulai')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Tanggal Selesai <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input 
                                type="date" 
                                name="tanggal_selesai" 
                                x-model="akhir" 
                                :min="mulai || today"
                                x-on:change="onAkhirChange()" 
                                value="{{ old('tanggal_selesai') }}"
                                class="w-full rounded-xl border border-gray-300 px-4 py-2.5 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none"
                                :disabled="jenis==='izin terlambat'"
                                required
                            >
                            {{-- Badge kecil saat izin terlambat agar user paham kenapa disabled --}}
                            <div 
                                x-show="jenis==='izin terlambat'" 
                                class="absolute right-2 top-1/2 -translate-y-1/2 text-[11px] px-2 py-0.5 rounded-full bg-amber-100 text-amber-700"
                            >Otomatis = mulai</div>
                        </div>
                        <p x-show="akhir && mulai && akhir < mulai" class="mt-1 text-xs text-red-600">
                            Tanggal selesai tidak boleh sebelum tanggal mulai.
                        </p>
                        @error('tanggal_selesai')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror>
                    </div>
                </div>

                {{-- Alasan (Wajib) --}}
                <div>
                    <div class="flex items-center justify-between">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Alasan <span class="text-red-500">*</span>
                        </label>
                        <span class="text-xs text-gray-400" x-text="alasanCount + '/255'"></span>
                    </div>
                    <textarea 
                        name="alasan" 
                        x-model="alasan" 
                        maxlength="255" 
                        rows="3" 
                        required
                        class="w-full rounded-xl border border-gray-300 px-4 py-3 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 focus:outline-none resize-y"
                        placeholder="Tulis alasan singkat…">{{ old('alasan') }}</textarea>
                    @error('alasan')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Bukti (foto / PDF) + Preview 
                     Catatan: tidak di-"required" di front-end (opsional), 
                     back-end juga sudah mengizinkan opsional. 
                     Untuk izin/sakit/cuti biasanya melampirkan bukti sangat dianjurkan. --}}
                <div x-on:dragover.prevent x-on:drop.prevent="handleDrop($event)" class="group">
                    <input 
                        id="bukti" 
                        type="file" 
                        name="bukti" 
                        accept="image/*,application/pdf" 
                        class="hidden"
                        x-ref="file" 
                        x-on:change="onFileChange"
                    >
                    <label for="bukti"
                        class="block rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 hover:border-emerald-300 transition cursor-pointer">
                        <div class="px-5 py-6 flex items-center gap-3">
                            <div class="shrink-0 text-gray-400">
                                <img src="{{ asset('images/upload_icon.svg') }}" class="h-8 w-8" alt="">
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm text-gray-700">
                                    <span class="font-medium">Klik untuk pilih file</span>
                                    <span class="text-gray-400">atau seret ke sini</span>
                                </p>
                                <p class="text-xs text-gray-500">Format: JPG/PNG/HEIC/PDF • Maks 10MB</p>
                                <p class="text-xs text-gray-600 mt-1" x-show="fileName" x-cloak x-text="fileName"></p>
                            </div>
                            <div class="ml-auto">
                                <button type="button" x-show="fileSelected" x-cloak x-on:click="clearFile"
                                    class="text-xs ml-2 px-2.5 py-1 rounded-lg border bg-white hover:bg-gray-50">
                                    Ganti
                                </button>
                            </div>
                        </div>
                    </label>
                    @error('bukti')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror

                    {{-- Preview --}}
                    <div x-show="fileSelected" x-cloak class="mt-3">
                        <template x-if="isImage">
                            <div class="relative">
                                <img :src="previewUrl" alt="Preview bukti"
                                    class="w-full max-h-72 object-contain rounded-xl border bg-white"
                                    x-on:error="imgError = true">
                                <div x-show="imgError"
                                    class="absolute inset-0 grid place-items-center rounded-xl border bg-gray-50 text-xs text-gray-500">
                                    Preview tidak didukung oleh browser. File: <span class="ml-1"
                                        x-text="fileName"></span>
                                </div>
                            </div>
                        </template>

                        <template x-if="isPdf">
                            <div>
                                <embed :src="previewUrl" type="application/pdf"
                                    class="w-full h-72 rounded-xl border bg-white" />
                                <div class="flex items-center justify-between mt-2 text-xs text-gray-600">
                                    <span class="truncate" x-text="fileName"></span>
                                    <a :href="previewUrl" target="_blank"
                                        class="text-emerald-600 hover:underline">Buka di tab baru</a>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Submit --}}
                <div class="pt-2">
                    <button 
                        type="submit" 
                        :disabled="(akhir && mulai && akhir < mulai) || loading || jenis===''"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white bg-emerald-600 hover:bg-emerald-700 disabled:opacity-60 focus:outline-none"
                    >
                        <img src="{{ asset('images/letter_white_icon.svg') }}" class="h-5 w-5" alt="">
                        <img x-show="loading" x-cloak src="{{ asset('images/loading_icon.svg') }}"
                            class="h-5 w-5 animate-spin" alt="Loading">
                        <span x-text="loading ? 'Mengirim…' : 'Kirim Pengajuan'"></span>
                    </button>
                </div>
            </form>

            <div class="px-5 pb-5 text-xs text-gray-500">
                * Pengajuan dibatasi 1 kali per hari (per tanggal). Jika rentang tanggal sudah ada pengajuan lain, sistem akan menolak.
            </div>
        </div>
    </div>

    <script>
        function izinForm() {
            return {
                // nilai form
                jenis: '',
                mulai: '',
                akhir: '',
                alasan: '',
                today: '',
                loading: false,

                // file/preview state
                fileName: '',
                fileSelected: false,
                previewUrl: null,
                isImage: false,
                isPdf: false,
                imgError: false,

                // counter alasan
                get alasanCount() {
                    return (this.alasan || '').length;
                },

                // INIT state dari old() + today dari blade
                init(mulaiOld, akhirOld, alasanOld, todayStr, jenisOld) {
                    this.today  = (todayStr || '').trim();
                    this.mulai  = (mulaiOld || '').trim();
                    this.akhir  = (akhirOld || '').trim();
                    this.alasan = (alasanOld || '');
                    this.jenis  = (jenisOld || '').trim();

                    // Normalisasi tanggal berdasarkan aturan (min = today, selesai >= mulai)
                    this.fixStartIfNeeded();
                    this.fixEndIfNeeded();
                    this.onJenisChange(); // kunci tanggal jika perlu

                    // cegah double submit
                    document.addEventListener('submit', (e) => {
                        if (e.target && e.target.matches('form[enctype="multipart/form-data"]')) {
                            this.loading = true;
                        }
                    });
                },

                // EVENT: perubahan jenis
                onJenisChange() {
                    // Jika "izin terlambat" => paksa 1 hari: akhir = mulai & field disabled (di-bind via :disabled)
                    if (this.jenis === 'izin terlambat') {
                        if (!this.mulai) {
                            // kalau belum pilih mulai, set minimal = today agar konsisten
                            this.mulai = this.today;
                        }
                        this.akhir = this.mulai;
                    }
                },

                // EVENT: perubahan tanggal mulai/akhir
                onMulaiChange() {
                    this.fixStartIfNeeded();
                    // Jika izin terlambat, kunci akhir = mulai
                    if (this.jenis === 'izin terlambat') {
                        this.akhir = this.mulai;
                    } else {
                        this.fixEndIfNeeded();
                    }
                },
                onAkhirChange() {
                    this.fixEndIfNeeded();
                },

                // GUARD tanggal
                fixStartIfNeeded() {
                    if (this.today && this.mulai && this.mulai < this.today) {
                        this.mulai = this.today;
                    }
                },
                fixEndIfNeeded() {
                    if (!this.akhir) return;
                    if (this.mulai && this.akhir < this.mulai) {
                        this.akhir = this.mulai;
                    }
                },

                // ----- Upload handlers -----
                onFileChange() {
                    const f = this.$refs.file?.files?.[0];
                    this.setPreviewFromFile(f);
                },
                handleDrop(e) {
                    const f = e.dataTransfer?.files?.[0];
                    if (!f) return;
                    const dt = new DataTransfer();
                    dt.items.add(f);
                    this.$refs.file.files = dt.files;
                    this.setPreviewFromFile(f);
                },
                setPreviewFromFile(f) {
                    this.clearPreviewUrl();
                    if (!f) {
                        this.resetFileState();
                        return;
                    }

                    this.fileName = f.name;
                    this.fileSelected = true;
                    this.imgError = false;

                    const type = (f.type || '').toLowerCase();
                    const ext = (f.name.split('.').pop() || '').toLowerCase();

                    this.isPdf = type === 'application/pdf' || ext === 'pdf';
                    this.isImage = !this.isPdf && type.startsWith('image/');

                    try {
                        this.previewUrl = URL.createObjectURL(f);
                    } catch (_e) {
                        try {
                            const reader = new FileReader();
                            reader.onload = (ev) => {
                                this.previewUrl = ev.target.result;
                            };
                            reader.readAsDataURL(f);
                        } catch (__e) {
                            this.previewUrl = null;
                        }
                    }
                },
                clearFile() {
                    if (this.$refs.file) this.$refs.file.value = '';
                    this.resetFileState();
                    this.clearPreviewUrl();
                },
                resetFileState() {
                    this.fileName = '';
                    this.fileSelected = false;
                    this.isImage = false;
                    this.isPdf = false;
                    this.imgError = false;
                },
                clearPreviewUrl() {
                    if (this.previewUrl && this.previewUrl.startsWith('blob:')) {
                        try {
                            URL.revokeObjectURL(this.previewUrl);
                        } catch (_e) {}
                    }
                    this.previewUrl = null;
                }
            }
        }
    </script>
@endsection
