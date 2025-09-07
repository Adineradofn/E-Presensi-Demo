<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class KaryawanController extends Controller
{
    /**
     * List + Search (nip/nik/nama/email/divisi/jabatan)
     */
    public function index(Request $request)
    {
        $q = $request->query('q');

        $karyawans = Karyawan::when($q, function ($query) use ($q) {
                $like = "%{$q}%";
                $query->where(function ($sub) use ($like) {
                    $sub->where('nip', 'like', $like)
                        ->orWhere('nik', 'like', $like)
                        ->orWhere('nama', 'like', $like)
                        ->orWhere('email', 'like', $like)
                        ->orWhere('divisi', 'like', $like)
                        ->orWhere('jabatan', 'like', $like);
                });
            })
            ->orderBy('id_karyawan', 'desc')
            ->paginate(10)
            ->withQueryString();

        return view('admin.karyawan.data-karyawan', [
            'title'     => 'Data Karyawan',
            'karyawans' => $karyawans,
            'q'         => $q,
        ]);
    }

    public function create()
    {
        return view('admin.karyawan.karyawan-create', ['title' => 'Tambah Karyawan']);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nip'      => ['required', 'max:100', 'unique:karyawan,nip'],
            'nik'      => ['required', 'max:100'],
            'nama'     => ['required', 'max:100'],
            'alamat'   => ['required', 'max:100'],
            'email'    => ['required', 'email', 'max:100'],
            'password' => ['required', 'min:3'],
            'divisi'   => ['required', 'max:100'],
            'jabatan'  => ['required', 'max:100'],
            'foto'     => ['nullable', 'max:100'],
            'role'     => ['required', Rule::in(['admin', 'karyawan'])],
        ]);

        // password akan di-hash oleh mutator di Model (lihat file Model di bawah)
        Karyawan::create($validated);

        return redirect()->route('admin.data.karyawan')->with('success', 'Karyawan berhasil ditambahkan');
    }

    public function edit($id)
    {
        $karyawan = Karyawan::findOrFail($id);
        return view('admin.karyawan.karyawan-edit', [
            'title'    => 'Ubah Data Karyawan',
            'karyawan' => $karyawan,
        ]);
    }

    public function update(Request $request, $id)
    {
        $karyawan = Karyawan::findOrFail($id);

        $validated = $request->validate([
            'nip'      => ['required', 'max:100', Rule::unique('karyawan', 'nip')->ignore($karyawan->id_karyawan, 'id_karyawan')],
            'nik'      => ['required', 'max:100'],
            'nama'     => ['required', 'max:100'],
            'alamat'   => ['required', 'max:100'],
            'email'    => ['required', 'email', 'max:100'],
            'divisi'   => ['required', 'max:100'],
            'jabatan'  => ['required', 'max:100'],
            'foto'     => ['nullable', 'max:100'],
            'role'     => ['required', Rule::in(['admin', 'karyawan'])],
        ]);

        $karyawan->update($validated);

        return redirect()->route('admin.data.karyawan')->with('success', 'Data karyawan berhasil diupdate');
    }

    /** Halaman ubah password (terpisah) */
    public function editPassword($id)
    {
        $karyawan = Karyawan::findOrFail($id);
        return view('admin.karyawan.karyawan-password', [
            'title'    => 'Ubah Password',
            'karyawan' => $karyawan,
        ]);
    }

    public function updatePassword(Request $request, $id)
    {
        $karyawan = Karyawan::findOrFail($id);

        $validated = $request->validate([
            'password' => ['required', 'min:3', 'confirmed'],
        ]);

        $karyawan->password = $validated['password']; // akan di-hash oleh mutator
        $karyawan->save();

        return redirect()->route('admin.data.karyawan')->with('success', 'Password karyawan berhasil diubah');
    }

    public function destroy($id)
    {
        $karyawan = Karyawan::findOrFail($id);
        $karyawan->delete();

        return redirect()->route('admin.data.karyawan')->with('success', 'Karyawan berhasil dihapus');
    }
}
