@extends('admin.app-admin')

@section('content')
<div class="p-4">
  <h2 class="text-xl sm:text-2xl font-bold mb-4">Pengajuan Izin</h2>

  <!-- Tabel -->
  <div class="overflow-x-auto bg-white rounded-xl border border-gray-200 shadow-sm">
    <table class="min-w-full whitespace-nowrap">
      <thead class="bg-gray-50 text-left text-gray-700">
        <tr>
          <th class="px-4 py-3 text-sm font-semibold">ID</th>
          <th class="px-4 py-3 text-sm font-semibold">Nama</th>
          <th class="px-4 py-3 text-sm font-semibold">Jabatan</th>
          <th class="px-4 py-3 text-sm font-semibold">Divisi</th>
          <th class="px-4 py-3 text-sm font-semibold">Tanggal Mulai</th>
          <th class="px-4 py-3 text-sm font-semibold">Tanggal Akhir</th>
          <th class="px-4 py-3 text-sm font-semibold">Alasan</th>
          <th class="px-4 py-3 text-sm font-semibold">Status</th>
        </tr>
      </thead>

      <tbody class="divide-y divide-gray-100">
        <!-- Dummy Row 1 -->
        <tr class="hover:bg-gray-50">
          <td class="px-4 py-3 text-sm text-gray-700">1</td>
          <td class="px-4 py-3 text-sm font-medium text-gray-900">Fulan</td>
          <td class="px-4 py-3 text-sm text-gray-700">Staff</td>
          <td class="px-4 py-3 text-sm text-gray-700">IT</td>
          <td class="px-4 py-3 text-sm text-gray-700">2025-08-24</td>
          <td class="px-4 py-3 text-sm text-gray-700">2025-08-25</td>
          <td class="px-4 py-3 text-sm text-gray-700">Urusan keluarga</td>
          <td class="px-4 py-3 text-sm">
            <form action="#" method="POST" class="flex items-center gap-2">
              <select name="status"
                class="status-select rounded-lg px-3 py-1.5 text-sm font-medium">
                <option value="pending" selected>Pending</option>
                <option value="diterima">Diterima</option>
                <option value="ditolak">Ditolak</option>
              </select>
              <button type="submit"
                class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm">
                Simpan
              </button>
            </form>
          </td>
        </tr>

        <!-- Dummy Row 2 -->
        <tr class="hover:bg-gray-50">
          <td class="px-4 py-3 text-sm text-gray-700">2</td>
          <td class="px-4 py-3 text-sm font-medium text-gray-900">Ahmad</td>
          <td class="px-4 py-3 text-sm text-gray-700">Manager</td>
          <td class="px-4 py-3 text-sm text-gray-700">HRD</td>
          <td class="px-4 py-3 text-sm text-gray-700">2025-08-26</td>
          <td class="px-4 py-3 text-sm text-gray-700">2025-08-26</td>
          <td class="px-4 py-3 text-sm text-gray-700">Dinas luar</td>
          <td class="px-4 py-3 text-sm">
            <form action="#" method="POST" class="flex items-center gap-2">
              <select name="status"
                class="status-select rounded-lg px-3 py-1.5 text-sm font-medium">
                <option value="pending">Pending</option>
                <option value="diterima" selected>Diterima</option>
                <option value="ditolak">Ditolak</option>
              </select>
              <button type="submit"
                class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm">
                Simpan
              </button>
            </form>
          </td>
        </tr>

        <!-- Dummy Row 3 -->
        <tr class="hover:bg-gray-50">
          <td class="px-4 py-3 text-sm text-gray-700">3</td>
          <td class="px-4 py-3 text-sm font-medium text-gray-900">Siti</td>
          <td class="px-4 py-3 text-sm text-gray-700">Staff</td>
          <td class="px-4 py-3 text-sm text-gray-700">Finance</td>
          <td class="px-4 py-3 text-sm text-gray-700">2025-08-20</td>
          <td class="px-4 py-3 text-sm text-gray-700">2025-08-22</td>
          <td class="px-4 py-3 text-sm text-gray-700">Sakit</td>
          <td class="px-4 py-3 text-sm">
            <form action="#" method="POST" class="flex items-center gap-2">
              <select name="status"
                class="status-select rounded-lg px-3 py-1.5 text-sm font-medium">
                <option value="pending">Pending</option>
                <option value="diterima">Diterima</option>
                <option value="ditolak" selected>Ditolak</option>
              </select>
              <button type="submit"
                class="px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm">
                Simpan
              </button>
            </form>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

{{-- Script untuk ubah warna dropdown sesuai value --}}
<script>
  function updateSelectColor(select) {
    select.classList.remove(
      'bg-gray-100','text-gray-700',
      'bg-emerald-100','text-emerald-700',
      'bg-red-100','text-red-700'
    );

    if (select.value === 'pending') {
      select.classList.add('bg-gray-100','text-gray-700');
    } else if (select.value === 'diterima') {
      select.classList.add('bg-emerald-100','text-emerald-700');
    } else if (select.value === 'ditolak') {
      select.classList.add('bg-red-100','text-red-700');
    }
  }

  document.querySelectorAll('.status-select').forEach(sel => {
    updateSelectColor(sel);
    sel.addEventListener('change', () => updateSelectColor(sel));
  });
</script>
@endsection
