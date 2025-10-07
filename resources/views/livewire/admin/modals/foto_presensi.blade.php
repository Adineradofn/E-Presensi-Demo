<div id="photoModal" class="fixed inset-0 z-[60] hidden">
    <div class="absolute inset-0 bg-black/50" onclick="closePhotoModal()"></div>
    <div class="relative mx-auto my-8 w-[95%] max-w-4xl bg-white rounded-2xl shadow-xl overflow-hidden">
        <!-- Header (DIUBAH: gradient + teks putih + styling tombol) -->
        <div
            class="sticky top-0 z-20 flex items-center justify-between px-5 py-3 bg-gradient-to-r from-emerald-600 to-emerald-500 text-white">
            <div class="flex items-center gap-3">
                <!-- Wrapper untuk icon -->
                <span class="inline-flex h-9 w-9 items-center justify-center rounded-xl bg-white/20">
                    <!-- Jika tetap pakai file gambar -->
                    <img src="{{ asset('images/camera_icon.svg') }}" alt="Camera" class="h-5 w-5 object-contain" />
                </span>

                <!-- Judul -->
                <h3 class="font-semibold">Foto Absen</h3>
            </div>

            <button
                class="inline-flex h-9 w-9 items-center justify-center rounded-lg hover:bg-white/10 focus:outline-none focus:ring-2 focus:ring-white/60"
                onclick="closePhotoModal()" aria-label="Tutup">
                <img src="{{ asset('images/cancel_icon.svg') }}" class="h-6 w-6" alt="Tutup">
            </button>
        </div>

        <!-- Body (tetap seperti semula) -->
        <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <div class="mb-2 font-medium ">Absen Masuk</div>
                <div class="aspect-video bg-gray-50 border rounded-xl flex items-center justify-center overflow-hidden">
                    <img id="imgMasuk" alt="Foto Absen Masuk" class="hidden w-full h-full object-contain">
                    <div id="emptyMasuk" class="text-sm text-gray-500">Belum ada foto</div>
                </div>
            </div>
            <div>
                <div class="mb-2 font-medium">Absen Pulang</div>
                <div class="aspect-video bg-gray-50 border rounded-xl flex items-center justify-center overflow-hidden">
                    <img id="imgPulang" alt="Foto Absen Pulang" class="hidden w-full h-full object-contain">
                    <div id="emptyPulang" class="text-sm text-gray-500">Belum ada foto</div>
                </div>
            </div>
        </div>

        <div class="px-5 pb-5 flex justify-end">
            <button class="px-4 py-2 rounded-xl border bg-white hover:bg-gray-50"
                onclick="closePhotoModal()">Tutup</button>
        </div>
    </div>
</div>

<!-- Script tetap sama -->
<script>
    // === EXACT sama seperti versi Blade lama ===
    function openPhotoModal(btn) {
        const urlMasuk = btn.getAttribute('data-url-masuk') || '';
        const urlPulang = btn.getAttribute('data-url-pulang') || '';
        const hasMasuk = btn.getAttribute('data-has-masuk') === '1';
        const hasPulang = btn.getAttribute('data-has-pulang') === '1';

        const modal = document.getElementById('photoModal');
        const imgMasuk = document.getElementById('imgMasuk');
        const imgPulang = document.getElementById('imgPulang');
        const emptyMasuk = document.getElementById('emptyMasuk');
        const emptyPulang = document.getElementById('emptyPulang');

        // reset
        imgMasuk.src = '';
        imgMasuk.classList.add('hidden');
        emptyMasuk.classList.remove('hidden');
        imgPulang.src = '';
        imgPulang.classList.add('hidden');
        emptyPulang.classList.remove('hidden');

        if (hasMasuk && urlMasuk) {
            imgMasuk.src = urlMasuk;
            imgMasuk.onload = () => {
                imgMasuk.classList.remove('hidden');
                emptyMasuk.classList.add('hidden');
            };
            imgMasuk.onerror = () => {
                imgMasuk.classList.add('hidden');
                emptyMasuk.classList.remove('hidden');
            };
        }
        if (hasPulang && urlPulang) {
            imgPulang.src = urlPulang;
            imgPulang.onload = () => {
                imgPulang.classList.remove('hidden');
                emptyPulang.classList.add('hidden');
            };
            imgPulang.onerror = () => {
                imgPulang.classList.add('hidden');
                emptyPulang.classList.remove('hidden');
            };
        }

        modal.classList.remove('hidden');
    }

    function closePhotoModal() {
        document.getElementById('photoModal')?.classList.add('hidden');
    }
</script>
