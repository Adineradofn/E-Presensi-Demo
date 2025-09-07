@extends('admin.app-admin')

@section('content')
<div class="p-4">
  <h2 class="text-xl sm:text-2xl font-bold mb-4">Data Karyawan</h2>

  {{-- Flash dari aksi non-AJAX (fallback) --}}
  @if (session('success'))
    <div class="mb-3 p-3 rounded bg-emerald-50 text-emerald-700 border border-emerald-200">
      {{ session('success') }}
    </div>
  @endif

  <!-- Toolbar -->
  <div class="flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between mb-4">
    <button id="btnOpenCreate"
       class="inline-flex items-center gap-2 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg shadow-sm">
      Tambah Karyawan
    </button>

    <form method="GET" action="{{ route('admin.data.karyawan') }}" class="sm:w-80">
      <input type="text" name="q" value="{{ $q }}"
        class="w-full rounded-lg border border-gray-300 px-4 py-2 pr-10 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500"
        placeholder="Cari NIP / NIK / Nama / Email / Divisi / Jabatan...">
    </form>
  </div>

  <!-- Tabel -->
  <div class="overflow-x-auto bg-white rounded-xl border border-gray-200 shadow-sm">
    <table class="min-w-full whitespace-nowrap">
      <thead class="bg-gray-50 text-left text-gray-700">
        <tr>
          <th class="px-4 py-3 text-sm font-semibold">ID</th>
          <th class="px-4 py-3 text-sm font-semibold">NIP</th>
          <th class="px-4 py-3 text-sm font-semibold">NIK</th>
          <th class="px-4 py-3 text-sm font-semibold">Nama</th>
          <th class="px-4 py-3 text-sm font-semibold">Email</th>
          <th class="px-4 py-3 text-sm font-semibold">Divisi</th>
          <th class="px-4 py-3 text-sm font-semibold">Jabatan</th>
          <th class="px-4 py-3 text-sm font-semibold">Role</th>
          <th class="px-4 py-3 text-sm font-semibold text-center">Aksi</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-gray-100" id="tableBody">
        @forelse ($karyawans as $k)
          <tr class="hover:bg-gray-50" data-row-id="{{ $k->id_karyawan }}">
            <td class="px-4 py-3 text-sm text-gray-700">{{ $k->id_karyawan }}</td>
            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $k->nip }}</td>
            <td class="px-4 py-3 text-sm text-gray-700">{{ $k->nik }}</td>
            <td class="px-4 py-3 text-sm text-gray-700">{{ $k->nama }}</td>
            <td class="px-4 py-3 text-sm text-gray-700">{{ $k->email }}</td>
            <td class="px-4 py-3 text-sm text-gray-700">{{ $k->divisi }}</td>
            <td class="px-4 py-3 text-sm text-gray-700">{{ $k->jabatan }}</td>
            <td class="px-4 py-3 text-sm text-gray-700">{{ $k->role }}</td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-2 justify-end">
                <button
                  class="btnEdit px-3 py-1.5 rounded-lg border border-emerald-600 text-emerald-700 hover:bg-emerald-50 text-sm"
                  data-id="{{ $k->id_karyawan }}"
                  data-nip="{{ $k->nip }}"
                  data-nik="{{ $k->nik }}"
                  data-nama="{{ $k->nama }}"
                  data-alamat="{{ $k->alamat }}"
                  data-email="{{ $k->email }}"
                  data-divisi="{{ $k->divisi }}"
                  data-jabatan="{{ $k->jabatan }}"
                  data-foto="{{ $k->foto }}"
                  data-role="{{ $k->role }}"
                >Ubah Data</button>

                <button
                  class="btnPassword px-3 py-1.5 rounded-lg border border-amber-600 text-amber-700 hover:bg-amber-50 text-sm"
                  data-id="{{ $k->id_karyawan }}"
                  data-nama="{{ $k->nama }}"
                >Ubah Password</button>

                <button
                  class="btnDelete px-3 py-1.5 rounded-lg border border-red-600 text-red-700 hover:bg-red-50 text-sm"
                  data-id="{{ $k->id_karyawan }}"
                >Hapus</button>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="9" class="px-4 py-6 text-center text-gray-500">Belum ada data</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="mt-4">
    {{ $karyawans->links() }}
  </div>
</div>

{{-- ======================= MODALS (dipisah ke partial) ======================= --}}
{{-- Style modal cukup sekali di halaman/layout --}}
<style>
  .modal { display: none; }
  .modal.show { display: flex; }
  [x-cloak]{ display: none !important; }
</style>

@include('admin.karyawan.modals.create-karyawan')
@include('admin.karyawan.modals.edit-karyawan')
@include('admin.karyawan.modals.password-karyawan')

{{-- ======================= JS (tetap sama) ======================= --}}
<script>
  const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

  // Helpers modal
  const show = el => el.classList.add('show');
  const hide = el => el.classList.remove('show');

  // ====== CREATE ======
  const modalCreate = document.getElementById('modalCreate');
  document.getElementById('btnOpenCreate').addEventListener('click', () => show(modalCreate));
  document.querySelector('.btnCloseCreate').addEventListener('click', () => hide(modalCreate));

  document.getElementById('formCreate').addEventListener('submit', async (e) => {
    e.preventDefault();
    const fd = new FormData(e.target);

    try {
      const res = await fetch("{{ route('admin.data.karyawan.store') }}", {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': token },
        body: fd
      });
      if (!res.ok) throw await res.json();
      hide(modalCreate);
      location.reload(); // sederhana: refresh tabel
    } catch (err) {
      alert(err.message || 'Gagal menambah karyawan');
    }
  });

  // ====== EDIT ======
  const modalEdit = document.getElementById('modalEdit');
  const formEdit = document.getElementById('formEdit');

  document.querySelectorAll('.btnEdit').forEach(btn => {
    btn.addEventListener('click', () => {
      formEdit.id.value      = btn.dataset.id;
      formEdit.nip.value     = btn.dataset.nip;
      formEdit.nik.value     = btn.dataset.nik;
      formEdit.nama.value    = btn.dataset.nama;
      formEdit.alamat.value  = btn.dataset.alamat;
      formEdit.email.value   = btn.dataset.email;
      formEdit.divisi.value  = btn.dataset.divisi;
      formEdit.jabatan.value = btn.dataset.jabatan;
      formEdit.foto.value    = btn.dataset.foto ?? '';
      formEdit.role.value    = btn.dataset.role;
      show(modalEdit);
    });
  });

  document.querySelector('.btnCloseEdit').addEventListener('click', () => hide(modalEdit));

  formEdit.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = formEdit.id.value;
    const fd = new FormData(formEdit);
    fd.append('_method', 'PUT');

    try {
      const res = await fetch(`{{ url('/data-karyawan') }}/${id}`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': token },
        body: fd
      });
      if (!res.ok) throw await res.json();
      hide(modalEdit);
      location.reload();
    } catch (err) {
      alert(err.message || 'Gagal mengubah data');
    }
  });

  // ====== PASSWORD ======
  const modalPassword = document.getElementById('modalPassword');
  const formPassword = document.getElementById('formPassword');
  const pwNama = document.getElementById('pwNama');

  document.querySelectorAll('.btnPassword').forEach(btn => {
    btn.addEventListener('click', () => {
      formPassword.id.value = btn.dataset.id;
      pwNama.textContent = btn.dataset.nama ? `(${btn.dataset.nama})` : '';
      show(modalPassword);
    });
  });

  document.querySelector('.btnClosePassword').addEventListener('click', () => hide(modalPassword));

  formPassword.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = formPassword.id.value;
    const fd = new FormData(formPassword);
    fd.append('_method', 'PUT');

    try {
      const res = await fetch(`{{ url('/data-karyawan') }}/${id}/password`, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': token },
        body: fd
      });
      if (!res.ok) throw await res.json();
      hide(modalPassword);
      alert('Password berhasil diubah');
    } catch (err) {
      alert(err.message || 'Gagal mengubah password');
    }
  });

  // ====== DELETE ======
  document.querySelectorAll('.btnDelete').forEach(btn => {
    btn.addEventListener('click', async () => {
      if (!confirm('Yakin ingin menghapus data ini?')) return;
      const id = btn.dataset.id;
      const fd = new FormData();
      fd.append('_method', 'DELETE');

      try {
        const res = await fetch(`{{ url('/data-karyawan') }}/${id}`, {
          method: 'POST',
          headers: { 'X-CSRF-TOKEN': token },
          body: fd
        });
        if (!res.ok) throw await res.json();
        const row = document.querySelector(`[data-row-id="${id}"]`);
        if (row) row.remove();
      } catch (err) {
        alert(err.message || 'Gagal menghapus');
      }
    });
  });
</script>
@endsection
