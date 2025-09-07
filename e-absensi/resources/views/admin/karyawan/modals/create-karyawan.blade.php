{{-- Modal: Tambah --}}
<div id="modalCreate" class="modal fixed inset-0 items-center justify-center bg-black/40 z-50">
  <div class="bg-white w-full max-w-xl rounded-lg shadow p-5">
    <h3 class="text-lg font-semibold mb-3">Tambah Karyawan</h3>
    <form id="formCreate" class="grid grid-cols-2 gap-3">
      @csrf
      <div class="col-span-1">
        <label class="text-sm">NIP</label>
        <input name="nip" class="w-full border rounded px-3 py-2" required>
      </div>
      <div class="col-span-1">
        <label class="text-sm">NIK</label>
        <input name="nik" class="w-full border rounded px-3 py-2" required>
      </div>
      <div class="col-span-2">
        <label class="text-sm">Nama</label>
        <input name="nama" class="w-full border rounded px-3 py-2" required>
      </div>
      <div class="col-span-2">
        <label class="text-sm">Alamat</label>
        <input name="alamat" class="w-full border rounded px-3 py-2" required>
      </div>
      <div class="col-span-2">
        <label class="text-sm">Email</label>
        <input type="email" name="email" class="w-full border rounded px-3 py-2" required>
      </div>
      <div class="col-span-1">
        <label class="text-sm">Password</label>
        <input type="password" name="password" class="w-full border rounded px-3 py-2" required>
      </div>
      <div class="col-span-1">
        <label class="text-sm">Foto (opsional)</label>
        <input name="foto" class="w-full border rounded px-3 py-2" placeholder="nama_file.jpg">
      </div>
      <div class="col-span-1">
        <label class="text-sm">Divisi</label>
        <input name="divisi" class="w-full border rounded px-3 py-2" required>
      </div>
      <div class="col-span-1">
        <label class="text-sm">Jabatan</label>
        <input name="jabatan" class="w-full border rounded px-3 py-2" required>
      </div>
      <div class="col-span-2">
        <label class="text-sm">Role</label>
        <select name="role" class="w-full border rounded px-3 py-2" required>
          <option value="">-- Pilih Role --</option>
          <option value="admin">Admin</option>
          <option value="karyawan">Karyawan</option>
        </select>
      </div>

      <div class="col-span-2 flex justify-end gap-2 mt-2">
        <button type="button" class="btnCloseCreate px-4 py-2 border rounded">Batal</button>
        <button class="px-4 py-2 bg-emerald-600 text-white rounded">Simpan</button>
      </div>
    </form>
  </div>
</div>
