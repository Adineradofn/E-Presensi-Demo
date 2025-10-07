{{-- Modal Ubah Status Izin --}}
<div id="edit_status_izin"
    x-data
    x-cloak
    x-show="$store.izinModal.open"
    x-transition.opacity
    x-on:close-izin-modal.window="$store.izinModal.close()"
    {{-- >>> BARU: listener untuk membuka modal dari tombol row & menyetel Livewire props --}}
    x-on:open-izin-modal.window="
        // detail: { izinId, currentStatus, nama, mulai, akhir, targetId }
        const d = $event.detail || {};
        // normalisasi status agar selaras dengan backend (disetujui/pending/ditolak)
        const normalized = (d.currentStatus === 'diterima') ? 'disetujui' : (d.currentStatus || 'pending');
        $store.izinModal.openWith({
            targetId: d.targetId || 'edit_status_izin',
            currentStatus: normalized,
            nama: d.nama, mulai: d.mulai, akhir: d.akhir
        });
        // set Livewire properties
        $wire.$set('selectedIzinId', d.izinId);
        $wire.$set('selectedStatus', normalized);
    "
    class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/70"
    role="dialog" aria-modal="true" aria-labelledby="editStatusIzinTitle">

    <div class="absolute inset-0" @click="$store.izinModal.close()" aria-hidden="true"></div>

    <div class="relative z-10 w-[96%] max-w-md" @keydown.window.escape="$store.izinModal.close()">
        <div class="bg-white rounded-2xl shadow-2xl ring-1 ring-black/5 overflow-hidden origin-center" x-ref="panel" x-transition.scale>

            <div class="sticky top-0 z-20 flex items-center justify-between px-5 py-3 bg-gradient-to-r from-emerald-600 to-emerald-500 text-white">
                <div class="flex items-center gap-3">
                    <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-white/20">
                        <img src="{{ asset('images/edit_white_icon.svg') }}" class="h-6 w-6" alt="edit karyawan">
                    </span>
                    <h3 id="editStatusIzinTitle" class="text-base sm:text-lg font-semibold">
                        Ubah Status Pengajuan Izin
                    </h3>
                </div>
                <button type="button"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-lg hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/60"
                    aria-label="Tutup modal" @click="$store.izinModal.close()">
                    <img src="{{ asset('images/cancel_icon.svg') }}" class="h-6 w-6" alt="Tutup">
                </button>
            </div>

            <div class="max-h/[70vh] overflow-y-auto px-5 py-5">
                {{-- >>> BARU: hidden input agar Livewire punya selectedIzinId saat submit --}}
                <input type="hidden" wire:model="selectedIzinId">

                <form id="formStatusIzin" wire:submit.prevent="saveStatus" class="space-y-4" novalidate>
                    {{-- Select Status (fix value = disetujui) --}}
                    <div>
                        <label for="selectStatusIzin" class="text-sm font-medium text-gray-700">
                            Status <span class="text-red-600">*</span>
                        </label>
                        <div class="relative mt-1">
                            <select id="selectStatusIzin" name="status"
                                x-model="$store.izinModal.selectedStatus"
                                wire:model.live="selectedStatus"
                                class="w-full rounded-xl border bg-white/80 px-3 py-2  focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 border-gray-300 text-sm">
                                <option value="pending">Pending</option>
                                {{-- ⛏️ FIX: gunakan 'disetujui' (bukan 'diterima') --}}
                                <option value="disetujui">Disetujui</option>
                                <option value="ditolak">Ditolak</option>
                            </select>
                        </div>
                        <p class="mt-1 text-xs text-gray-500">Pilih status pengajuan izin yang sesuai.</p>
                    </div>

                    {{-- Ringkasan --}}
                    <div class="rounded-xl border border-gray-100 bg-gray-50 px-3 py-2 text-xs text-gray-600 space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1">
                                <img src="{{ asset('images/clock_icon.svg') }}" class="h-5 w-5" alt="clock icon">
                                Status saat ini:
                            </span>
                            <strong class="text-gray-800" x-text="$store.izinModal.initialStatusLabel"></strong>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1">
                                <img src="{{ asset('images/user_grey_icon.svg') }}" class="h-5 w-5" alt="">
                                Nama:
                            </span>
                            <span class="inline-flex items-center rounded-lg bg-white px-2 py-0.5 ring-1 ring-gray-200 text-gray-700"
                                x-text="$store.izinModal.nama || '—'"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1">
                               <img src="{{ asset('images/calendar_grey_icon.svg') }}" class="h-5 w-5" alt="calendar icon">
                                Tanggal mulai:
                            </span>
                            <span class="inline-flex items-center rounded-lg bg-white px-2 py-0.5 ring-1 ring-gray-200 text-gray-700"
                                x-text="$store.izinModal.mulai || '—'"></span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1">
                               <img src="{{ asset('images/calendar_grey_icon.svg') }}" class="h-5 w-5" alt="calendar icon">
                                Tanggal selesai:
                            </span>
                            <span class="inline-flex items-center rounded-lg bg-white px-2 py-0.5 ring-1 ring-gray-200 text-gray-700"
                                x-text="$store.izinModal.akhir || '—'"></span>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="sticky bottom-0 z-20 mt-2 bg-white px-5 py-3 border-t border-gray-100 flex items-center justify-end gap-2">
                        <button type="button"
                            class="px-4 py-2 rounded-xl border border-gray-300 bg-white hover:bg-gray-50"
                            @click="$store.izinModal.close()">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-2 rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm inline-flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="M17 3H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V7z" />
                                <path d="M17 3v6H7V3" />
                                <path d="M7 13h10v8H7z" />
                            </svg>
                            Simpan
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
</div>

{{-- Registrasi Alpine store --}}
<script>
(function ensureIzinModalStore(){
    function register(){
        const Alpine = window.Alpine;
        if(!Alpine) return;
        if(Alpine.store('izinModal')) return;

        Alpine.store('izinModal', {
            open: false,
            initialStatus: 'pending',
            selectedStatus: 'pending',
            nama: '',
            mulai: '',
            akhir: '',
            modalId: 'edit_status_izin',
            // ⛏️ FIX: dukung label 'disetujui'
            get initialStatusLabel(){
                const m = { pending:'Pending', disetujui:'Disetujui', ditolak:'Ditolak' };
                return m[this.initialStatus] ?? this.initialStatus;
            },
            openWith(payload = {}){
                if (payload.targetId && payload.targetId !== this.modalId) return;
                this.initialStatus = payload.currentStatus || 'pending';
                this.selectedStatus = this.initialStatus;
                this.nama  = payload.nama  || '';
                this.mulai = payload.mulai || '';
                this.akhir = payload.akhir || '';
                this.open = true;
                queueMicrotask(() => {
                    const el = document.querySelector('#formStatusIzin select, #formStatusIzin button');
                    if (el && el.focus) el.focus();
                });
            },
            close(){ this.open = false; },
        });
    }

    if (window.Alpine) {
        register();
    } else {
        document.addEventListener('alpine:init', register, { once:true });
    }
})();
</script>
