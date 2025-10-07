@extends('user.app_user')

@section('content')
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="flex items-center gap-3">
            <div
                  class="h-10 w-10 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-500 text-white
                  flex items-center justify-center shadow-md">
                {{-- clipboard-check icon --}}
                <img src="{{ asset('images/presensi_white_icon.svg') }}" class="h-8 w-8" alt="">
            </div>
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-gray-900">Presensi</h1>
            </div>
        </div>
    </div>

  <div class="max-w-2xl mx-auto p-4" x-data="absenPage()" x-init="initStatus()">
    {{-- Flash message --}}
    @if (session('success'))
      <div class="mb-3 p-3 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">
        {{ session('success') }}
      </div>
    @endif
    @if (session('error'))
      <div class="mb-3 p-3 rounded bg-red-50 text-red-700 border border-red-200">
        {{ session('error') }}
      </div>
    @endif
    @if ($errors->any())
      <div class="mb-3 p-3 rounded bg-red-50 text-red-700 border border-red-200">
        <ul class="list-disc list-inside">
          @foreach ($errors->all() as $e)
            <li>{{ $e }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    {{-- ============ CARD ABSEN MASUK ============ --}}
    <div
      class="relative overflow-hidden rounded-2xl border bg-white/80 p-5 mb-5 shadow-sm ring-1 ring-black/5 transition-all hover:-translate-y-[1px] hover:shadow-lg">
      <div class="pointer-events-none absolute -top-20 -right-16 h-56 w-56 rounded-full bg-emerald-400/20 blur-3xl"></div>
      <div class="pointer-events-none absolute -bottom-24 -left-16 h-40 w-40 rounded-full bg-teal-400/10 blur-2xl"></div>

      <div class="relative flex items-start justify-between gap-4">
        <div class="flex items-start gap-3">
          <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm">
            <img src="{{ asset('images/in_icon.svg') }}" class="h-7 w-7" alt="">
          </span>

          <div>
            <h2 class="text-lg sm:text-xl font-semibold tracking-tight">Absen Masuk</h2>
            <p class="mt-1 text-sm text-gray-600">
              <span class="font-medium">Status:</span>
              {{-- ⛏️ Ubah: tak ada “Dibuka/Ditutup”, selalu “Siap” jika belum absen --}}
              <span
                :class="checkinDone ? 'text-gray-800 font-semibold' : 'text-emerald-600 font-semibold'"
                x-text="checkinDone ? 'Anda Sudah Absen' : 'Siap'"></span>
              <template x-if="doneAt.checkin">
                <span class="text-xs text-gray-500" x-text="' (' + doneAt.checkin + ')'"></span>
              </template>
            </p>
          </div>
        </div>

        {{-- ⛏️ Chip status: tak tergantung enabled --}}
        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border shadow-sm"
          :class="checkinDone ? 'bg-white text-gray-700 border-gray-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200'">
          <template x-if="checkinDone">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293A1 1 0 003.293 10.707l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z"/></svg>
          </template>
          <template x-if="!checkinDone">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293A1 1 0 003.293 10.707l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z"/></svg>
          </template>
          <span x-text="checkinDone ? 'Sudah Absen' : 'Siap'"></span>
        </span>
      </div>

      <div class="relative mt-4">
        <button
          class="group relative w-full sm:w-auto overflow-hidden rounded-xl px-5 py-3 text-white shadow-sm transition active:scale-[.98] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-400"
          :disabled="checkinDone" {{-- ⛏️ Hapus ketergantungan enabled --}}
          :class="checkinDone ? 'bg-gray-300 cursor-not-allowed' : 'bg-gradient-to-r from-emerald-600 to-teal-600 hover:brightness-110'"
          @click="openModal('in')" aria-live="polite" :aria-disabled="checkinDone">
          <span class="relative z-10">Tekan Untuk Absen</span>
          <span class="pointer-events-none absolute inset-0 opacity-0 group-hover:opacity-10 transition bg-white"></span>
        </button>
      </div>
    </div>

    {{-- ============ CARD ABSEN PULANG ============ --}}
    <div
      class="relative overflow-hidden rounded-2xl border bg-white/80 p-5 shadow-sm ring-1 ring-black/5 transition-all hover:-translate-y-[1px] hover:shadow-lg">
      <div class="pointer-events-none absolute -top-20 -right-16 h-56 w-56 rounded-full bg-indigo-400/20 blur-3xl"></div>
      <div class="pointer-events-none absolute -bottom-24 -left-16 h-40 w-40 rounded-full bg-fuchsia-400/10 blur-2xl"></div>

      <div class="relative flex items-start justify-between gap-4">
        <div class="flex items-start gap-3">
          <span class="inline-flex size-10 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm">
            <img src="{{ asset('images/out_icon.svg') }}" class="h-7 w-7" alt="">
          </span>

          <div>
            <h2 class="text-lg sm:text-xl font-semibold tracking-tight">Absen Pulang</h2>
            <p class="mt-1 text-sm text-gray-600">
              <span class="font-medium">Status:</span>
              {{-- ⛏️ Sama: tak ada “Dibuka/Ditutup” --}}
              <span
                :class="checkoutDone ? 'text-gray-800 font-semibold' : 'text-indigo-600 font-semibold'"
                x-text="checkoutDone ? 'Anda Sudah Absen' : 'Siap'"></span>
              <template x-if="doneAt.checkout">
                <span class="text-xs text-gray-500" x-text="' (' + doneAt.checkout + ')'"></span>
              </template>
            </p>
          </div>
        </div>

        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium border shadow-sm"
          :class="checkoutDone ? 'bg-white text-gray-700 border-gray-200' : 'bg-indigo-50 text-indigo-700 border-indigo-200'">
          <template x-if="checkoutDone">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293A1 1 0 003.293 10.707l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z"/></svg>
          </template>
          <template x-if="!checkoutDone">
            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 00-1.414 0L8 12.586 4.707 9.293A1 1 0 003.293 10.707l4 4a1 1 0 001.414 0l8-8a1 1 0 000-1.414z"/></svg>
          </template>
          <span x-text="checkoutDone ? 'Sudah Absen' : 'Siap'"></span>
        </span>
      </div>

      <div class="relative mt-4">
        <button
          class="group relative w-full sm:w-auto overflow-hidden rounded-xl px-5 py-3 text-white shadow-sm transition active:scale-[.98] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-indigo-400"
          :disabled="checkoutDone" {{-- ⛏️ Hapus ketergantungan enabled --}}
          :class="checkoutDone ? 'bg-gray-300 cursor-not-allowed' : 'bg-gradient-to-r from-indigo-600 to-violet-600 hover:brightness-110'"
          @click="openModal('out')" aria-live="polite" :aria-disabled="checkoutDone">
          <span class="relative z-10">Tekan Untuk Absen</span>
          <span class="pointer-events-none absolute inset-0 opacity-0 group-hover:opacity-10 transition bg-white"></span>
        </button>
      </div>
    </div>

    {{-- ===================== MODAL ===================== --}}
    <div
      x-show="showModal"
      x-transition.opacity
      class="fixed inset-0 z-60 p-4 bg-black/50 overflow-y-auto flex items-start sm:items-center justify-center"
      @keydown.escape.window="closeModal()"
      x-cloak
      style="-webkit-overflow-scrolling: touch;"
    >
      <div class="w-full max-w-md">
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden max-h-[85vh] flex flex-col">
          {{-- Header --}}
          <div class="px-5 py-4 border-b flex items-center justify-between shrink-0">
            <h3 class="font-semibold" x-text="mode === 'in' ? 'Kamera Absen Masuk' : 'Kamera Absen Pulang'"></h3>
            <button class="p-2 rounded-lg hover:bg-gray-100" @click="closeModal()">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
          </div>

          {{-- Body (scrollable) --}}
          <div class="px-5 pt-4 pb-3 space-y-3 overflow-y-auto" style="-webkit-overflow-scrolling: touch;">
            <div class="relative bg-black rounded-xl">
              {{-- Video tanpa crop (kamera depan) --}}
              <video
                x-ref="video"
                class="w-full max-h-[70vh] bg-black rounded-xl object-contain"
                autoplay playsinline muted></video>

              {{-- Overlay error kamera --}}
              <template x-if="cameraError">
                <div class="absolute inset-0 bg-black/70 text-white flex flex-col items-center justify-center rounded-xl text-center p-4">
                  <div>
                    <p class="font-medium mb-1">Tidak bisa mengakses kamera.</p>
                    <p class="text-sm text-white/80 mb-3">Izinkan akses kamera di browser/device, lalu coba lagi.</p>
                    <button type="button" class="px-3 py-1.5 rounded-lg bg-white/90 text-black"
                      @click="retryCamera()">Coba Lagi</button>
                  </div>
                </div>
              </template>

              {{-- Overlay menunggu izin --}}
              <template x-if="!cameraError && !hasCameraAccess">
                <div class="absolute inset-0 bg-black/60 text-white flex flex-col items-center justify-center rounded-xl text-center p-4">
                  <p class="font-medium">Meminta izin kamera…</p>
                  <p class="text-xs text-white/80 mt-1">Konfirmasi dialog izin di browser Anda.</p>
                </div>
              </template>
            </div>

            <template x-if="previewSrc">
              <div class="relative bg-black rounded-xl">
                {{-- Preview foto tanpa crop --}}
                <img :src="previewSrc" alt="Preview" class="w-full max-h-[70vh] rounded-xl border shadow-sm object-contain bg-black">
                <button type="button"
                  class="absolute top-2 right-2 bg-white/90 backdrop-blur px-2 py-1 rounded-lg text-xs border hover:bg-white"
                  @click="retake()">Ulangi</button>
              </div>
            </template>

            <p class="text-xs text-gray-500">Tips: pegang stabil, pencahayaan cukup, wajah terlihat jelas.</p>
          </div>

          {{-- Footer --}}
          <div class="px-5 pb-5 pt-2 flex items-center justify-between gap-3 shrink-0">
            <div class="flex items-center gap-2">
              <button type="button"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border bg-white hover:bg-gray-50"
                x-show="!hasCameraAccess && !cameraError"
                @click="requestCamera()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M15 10l4.553-2.276A1 1 0 0121 8.618V15.5a1 1 0 01-1.447.894L15 14m0-4v4m0-4l-3.553-1.776A1 1 0 0010 9.382V16.5a1 1 0 001.447.894L15 14"></path></svg>
                Izinkan Kamera
              </button>

              <button type="button"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border bg-white hover:bg-gray-50 disabled:opacity-60"
                :disabled="taking || previewSrc || !hasCameraAccess"
                @click="capture()">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"></circle></svg>
                Ambil Foto
              </button>
            </div>

            <form method="POST" :action="mode === 'in' ? checkinAction : checkoutAction"
              enctype="multipart/form-data" class="flex-1 flex justify-end"
              @submit="ensureCapturedOrBlock($event)">
              @csrf
              <input type="file" class="hidden" x-ref="hiddenPostFile">
              <button type="submit"
                class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-white shadow-sm disabled:opacity-60"
                :class="mode === 'in' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-indigo-600 hover:bg-indigo-700'"
                :disabled="!capturedFile">
                Kirim Absen
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div> {{-- END x-data root --}}
@endsection

@push('scripts')
<script data-navigate-once>
  window.absenPage = function () {
    return {
      // ===== state utama =====
      // ⛏️ HAPUS checkinEnabled/checkoutEnabled -> tak ada konsep buka/tutup di UI
      checkinDone: false,
      checkoutDone: false,
      doneAt: { checkin: null, checkout: null },

      // kamera & modal
      showModal: false,
      mode: 'in',
      stream: null,
      taking: false,
      cameraError: false,
      hasCameraAccess: false,
      capturedFile: null,
      previewSrc: null,

      // endpoint
      statusUrl: "{{ route('presensi.status.today') }}",
      checkinAction: "{{ route('presensi.checkin') }}",
      checkoutAction: "{{ route('presensi.checkout') }}",

      // load status awal
      async initStatus() {
        try {
          const r = await fetch(this.statusUrl, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
          const d = await r.json();

          // ⛏️ Abaikan d.checkin_enabled / d.checkout_enabled
          this.checkinDone  = !!d.checkin_done;
          this.checkoutDone = !!d.checkout_done;
          this.doneAt       = d.done_at || { checkin: null, checkout: null };
        } catch (_e) {
          // diamkan; backend controller tetap mengamankan aturan waktu
        }
      },

      // modal
      async openModal(which) {
        if (which === 'in' && this.checkinDone)  { alert('Anda sudah absen masuk.');  return; }
        if (which === 'out' && this.checkoutDone){ alert('Anda sudah absen pulang.'); return; }

        this.mode = which;
        this.showModal = true;
        this.cameraError = false;
        this.hasCameraAccess = false;
        this.capturedFile = null;
        if (this.previewSrc) URL.revokeObjectURL(this.previewSrc);
        this.previewSrc = null;

        await this.startCamera();
      },
      closeModal() {
        this.stopCamera();
        this.showModal = false;
      },

      // kamera
      async requestCamera() { await this.startCamera(); },
      async retryCamera()   { this.cameraError = false; await this.startCamera(); },
      async startCamera() {
        try {
          this.stream = await navigator.mediaDevices.getUserMedia({
            video: { facingMode: { ideal: 'user' } }, audio: false
          });
          const video = this.$refs.video;
          if (video) { video.srcObject = this.stream; video.muted = true; await video.play(); }
          this.hasCameraAccess = true;
          this.cameraError = false;
        } catch (err) {
          console.error('getUserMedia error:', err);
          this.hasCameraAccess = false;
          this.cameraError = true;
        }
      },
      stopCamera() {
        if (this.stream) { this.stream.getTracks().forEach(t => t.stop()); this.stream = null; }
        const video = this.$refs.video; if (video) video.srcObject = null;
        this.hasCameraAccess = false;
      },

      // capture
      async capture() {
        if (!this.$refs.video || !this.hasCameraAccess) return;
        this.taking = true;

        const video = this.$refs.video;
        const vw = video.videoWidth || 1280;
        const vh = video.videoHeight || 720;

        const canvas = document.createElement('canvas');
        canvas.width = vw; canvas.height = vh;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(video, 0, 0, vw, vh);

        await new Promise(resolve => {
          canvas.toBlob((blob) => {
            if (!blob) { this.taking = false; return resolve(); }
            const stamp = new Date().toISOString().replace(/[-:.TZ]/g, '');
            const fname = (this.mode === 'in' ? 'masuk_' : 'pulang_') + stamp + '.jpg';
            const file = new File([blob], fname, { type: 'image/jpeg' });
            this.capturedFile = file;

            if (this.previewSrc) URL.revokeObjectURL(this.previewSrc);
            this.previewSrc = URL.createObjectURL(file);

            const dt = new DataTransfer();
            dt.items.add(file);
            this.$refs.hiddenPostFile.name = (this.mode === 'in' ? 'foto_masuk' : 'foto_pulang');
            this.$refs.hiddenPostFile.files = dt.files;
            resolve();
          }, 'image/jpeg', 0.92);
        });

        this.taking = false;
      },

      retake() {
        this.capturedFile = null;
        if (this.previewSrc) URL.revokeObjectURL(this.previewSrc);
        this.previewSrc = null;
        if (this.$refs.hiddenPostFile) this.$refs.hiddenPostFile.value = '';
      },

      async ensureCapturedOrBlock(ev) {
        if ((this.mode === 'in' && this.checkinDone) || (this.mode === 'out' && this.checkoutDone)) {
          ev.preventDefault(); alert('Anda sudah absen.'); return;
        }
        if (!this.capturedFile) {
          ev.preventDefault();
          if (!this.hasCameraAccess) { await this.startCamera(); }
          await this.capture();
          if (this.capturedFile) { this.stopCamera(); ev.target.submit(); }
        } else {
          this.stopCamera();
        }
      },
    }
  }
</script>
@endpush
