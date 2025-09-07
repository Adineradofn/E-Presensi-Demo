{{-- Modal: Ubah Password --}}
<div id="modalPassword" class="modal fixed inset-0 items-center justify-center bg-black/40 z-50">
  <div class="bg-white w-full max-w-md rounded-lg shadow p-5">
    <h3 class="text-lg font-semibold mb-3">Ubah Password <span id="pwNama" class="text-emerald-700"></span></h3>
    <form id="formPassword" class="grid grid-cols-1 gap-3">
      @csrf
      <input type="hidden" name="id">
      <div>
        <label class="text-sm">Password baru</label>
        <input type="password" name="password" class="w-full border rounded px-3 py-2" required>
      </div>
      <div>
        <label class="text-sm">Konfirmasi password baru</label>
        <input type="password" name="password_confirmation" class="w-full border rounded px-3 py-2" required>
      </div>

      <div class="flex justify-end gap-2 mt-1">
        <button type="button" class="btnClosePassword px-4 py-2 border rounded">Batal</button>
        <button class="px-4 py-2 bg-emerald-600 text-white rounded">Simpan</button>
      </div>
    </form>
  </div>
</div>
