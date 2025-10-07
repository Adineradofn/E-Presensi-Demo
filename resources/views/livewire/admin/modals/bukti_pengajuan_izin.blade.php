<!-- MODAL BUKTI
     Komponen modal sederhana untuk menampilkan bukti pengajuan (gambar/PDF).
     - Disembunyikan default (class: hidden)
     - Aksesibilitas: role="dialog", aria-modal="true", aria-labelledby="#buktiTitle"
-->
<div id="buktiModal" class="fixed inset-0 z-50 hidden" role="dialog" aria-modal="true" aria-labelledby="buktiTitle">
  <!-- Backdrop gelap; klik backdrop menutup modal -->
  <div class="absolute inset-0 bg-black/50" onclick="closeBukti()"></div>

  <!-- Kontainer modal -->
  <div class="relative mx-auto my-8 w-[95%] max-w-4xl bg-white rounded-2xl shadow-xl overflow-hidden">
    <!-- Header modal -->
    <div class="px-5 py-3 flex items-center justify-between">
      <h3 id="buktiTitle" class="font-semibold">Bukti Pengajuan</h3>

      <!-- Tombol tutup (kanan atas) -->
      <button class="p-2 rounded-lg hover:bg-gray-100" onclick="closeBukti()" aria-label="Tutup">
        <img src="{{ asset('images/cancel_icon.svg') }}" class="h-8 w-8" alt="cancel">
      </button>
    </div>

    <!-- Isi modal -->
    <div class="p-5">
      <!-- Area pratinjau dengan rasio 16:9 (aspect-video) -->
      <div class="aspect-video bg-gray-50 border rounded-xl flex items-center justify-center overflow-hidden">
        <!-- Preview Gambar: dipakai untuk file image -->
        <img id="buktiImg" alt="Bukti" class="hidden w-full h-full object-contain">

        <!-- Preview PDF: dipakai untuk file PDF -->
        <!-- referrerpolicy=no-referrer → jangan kirim header Referer saat load -->
        <iframe id="buktiPdf" title="PDF preview" class="hidden w-full h-full" referrerpolicy="no-referrer"></iframe>

        <!-- Fallback: jika keduanya gagal ditampilkan -->
        <div id="buktiEmpty" class="text-sm text-gray-500 hidden">Tidak dapat menampilkan pratinjau.</div>
      </div>

      <!-- Aksi -->
      <div class="mt-3 flex items-center justify-between">
        <!-- Buka di tab baru -->
        <a id="buktiOpenNew" href="#" target="_blank" rel="noopener" class="text-sm text-emerald-700 hover:underline">
          Buka di tab baru
        </a>

        <!-- Unduh berkas -->
        <a id="buktiDownload" href="#" download
           class="inline-flex items-center gap-2 text-sm px-3 py-1.5 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700">
          Unduh
        </a>
      </div>
    </div>
  </div>
</div>

<script>
  /**
   * Buka modal bukti dan tampilkan pratinjau sesuai jenis file.
   * Dipanggil dari tombol/link yang punya attribute data-url="...".
   *
   * @param {HTMLElement} btn - Elemen pemicu (harus punya data-url berisi URL file)
   */
  function openBukti(btn) {
    // Ambil URL dari data attribute
    const url = (btn.getAttribute('data-url') || '').trim();
    if (!url) return; // guard: tidak ada URL

    // Ambil referensi elemen-elemen modal
    const modal = document.getElementById('buktiModal');
    const img   = document.getElementById('buktiImg');
    const pdf   = document.getElementById('buktiPdf');
    const empty = document.getElementById('buktiEmpty');
    const open  = document.getElementById('buktiOpenNew');
    const dl    = document.getElementById('buktiDownload');

    // Reset state pratinjau: kosongkan src & sembunyikan viewer
    [img, pdf].forEach(el => { el.src = ''; el.classList.add('hidden'); });
    empty.classList.add('hidden');

    // Set target link "Buka di tab baru" dan "Unduh"
    open.href = url;
    dl.href   = url;

    // Deteksi jenis konten berdasarkan ekstensi/hint di URL
    const lower = url.toLowerCase();
    const isImage = /\.(png|jpe?g|gif|webp|bmp|svg)(\?.*)?$/.test(lower);
    const isPdf =
      /\.pdf(\?.*)?$/.test(lower) ||
      lower.includes('application/pdf') ||
      lower.includes('response-content-type=application%2Fpdf');

    if (isImage) {
      // Tampilkan image viewer
      img.onload  = () => { img.classList.remove('hidden'); };  // sukses load → tampilkan
      img.onerror = () => { empty.classList.remove('hidden'); } // gagal load → fallback
      img.src = url;
    } else if (isPdf) {
      // Tampilkan PDF di iframe
      pdf.onload  = () => { pdf.classList.remove('hidden'); };
      pdf.onerror = () => { empty.classList.remove('hidden'); };
      // Tambah fragment viewer agar PDF fit-to-width/height (jika belum ada '#')
      pdf.src = url + (url.includes('#') ? '' : '#view=FitH');
    } else {
      // Jenis lain (semestinya tidak terjadi bila validasi hanya terima image/PDF)
      // Fallback: langsung buka di tab baru dan hentikan.
      window.open(url, '_blank');
      return;
    }

    // Tampilkan modal
    modal.classList.remove('hidden');

    // (Opsional) Kunci scroll body saat modal terbuka:
    // document.body.style.overflow = 'hidden';
  }

  /**
   * Tutup modal bukti dan hentikan loading pratinjau (hemat resource).
   */
  function closeBukti() {
    const modal = document.getElementById('buktiModal');
    const img   = document.getElementById('buktiImg');
    const pdf   = document.getElementById('buktiPdf');

    // Hentikan proses loading/render di viewer dengan mengosongkan src
    img.src = '';
    pdf.src = '';

    // Sembunyikan modal
    modal.classList.add('hidden');

    // (Opsional) Pulihkan scroll body:
    // document.body.style.overflow = '';
  }
</script>
