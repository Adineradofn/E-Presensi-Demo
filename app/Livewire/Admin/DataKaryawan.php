<?php

namespace App\Livewire\Admin;

use App\Models\Karyawan;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Livewire\WithFileUploads;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use RealRashid\SweetAlert\Facades\Alert;

#[Layout('admin.app_admin')]
#[Title('Data Karyawan')]
class DataKaryawan extends Component
{
    use WithPagination, WithFileUploads;

    public $q = '';
    public $perPage = 10;

    public $create = [
        'nik' => '',
        'nama' => '',
        'alamat' => '',
        'email' => '',
        'password' => '',
        'password_confirmation' => '',
        'divisi' => '',
        'jabatan' => '',
        'role' => '',
        'foto' => null,
    ];

    public $edit = [
        'id' => null,
        'nik' => '',
        'nama' => '',
        'alamat' => '',
        'email' => '',
        'divisi' => '',
        'jabatan' => '',
        'role' => '',
        'foto' => null,
    ];

    public $password = [
        'id' => null,
        'nama' => '',
        'password' => '',
        'password_confirmation' => '',
    ];

    protected $queryString = ['q'];

    protected function rulesCreate()
    {
        return [
            'create.nik'      => ['required', 'max:100', 'unique:karyawan,nik'],
            'create.nama'     => ['required', 'max:100'],
            'create.alamat'   => ['required', 'max:255'],
            'create.email'    => ['required', 'email', 'max:150', 'unique:karyawan,email'],
            'create.password' => ['required', 'min:6', 'regex:/^(?=.*[A-Z])(?=.*\d).{6,}$/', 'confirmed'],
            'create.divisi'   => ['required', 'max:100'],
            'create.jabatan'  => ['required', 'max:100'],
            'create.role'     => ['required', Rule::in(['admin', 'karyawan'])],
            'create.foto'     => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    protected function rulesEdit($id)
    {
        return [
            'edit.nik'     => ['required', 'max:100', Rule::unique('karyawan', 'nik')->ignore($id, 'id')],
            'edit.nama'    => ['required', 'max:100'],
            'edit.alamat'  => ['required', 'max:255'],
            'edit.email'   => ['required', 'email', 'max:150', Rule::unique('karyawan', 'email')->ignore($id, 'id')],
            'edit.divisi'  => ['required', 'max:100'],
            'edit.jabatan' => ['required', 'max:100'],
            'edit.role'    => ['required', Rule::in(['admin', 'karyawan'])],
            'edit.foto'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    protected $messages = [
        'required' => ':attribute wajib diisi.',
        'unique'   => ':attribute sudah terdaftar.',
        'max'      => ':attribute maksimal :max karakter.',
        'email'    => 'Format email tidak valid.',
        'image'    => 'File harus berupa gambar.',
        'mimes'    => 'Format file harus: :values.',

        'create.password.min'       => 'Password minimal 6 karakter.',
        'create.password.regex'     => 'Password wajib memiliki ≥1 huruf besar & ≥1 angka.',
        'create.password.confirmed' => 'Konfirmasi password tidak cocok.',

        'create.foto.max'   => 'Ukuran foto maksimal 2 MB.',
        'edit.foto.max'     => 'Ukuran foto maksimal 2 MB.',
        'create.foto.mimes' => 'Format foto harus JPG, JPEG, PNG, atau WebP.',
        'edit.foto.mimes'   => 'Format foto harus JPG, JPEG, PNG, atau WebP.',
        'create.foto.image' => 'File harus berupa gambar.',
        'edit.foto.image'   => 'File harus berupa gambar.',
    ];

    protected $validationAttributes = [
        'create.nik' => 'NIK',
        'create.nama' => 'Nama',
        'create.alamat' => 'Alamat',
        'create.email' => 'Email',
        'create.password' => 'Password',
        'create.password_confirmation' => 'Konfirmasi Password',
        'create.divisi' => 'Divisi',
        'create.jabatan' => 'Jabatan',
        'create.role' => 'Role',
        'create.foto' => 'Foto',

        'edit.nik' => 'NIK',
        'edit.nama' => 'Nama',
        'edit.alamat' => 'Alamat',
        'edit.email' => 'Email',
        'edit.divisi' => 'Divisi',
        'edit.jabatan' => 'Jabatan',
        'edit.role' => 'Role',
        'edit.foto' => 'Foto',

        'password.password' => 'Password',
        'password.password_confirmation' => 'Konfirmasi Password',
    ];

    public function updatingQ() { $this->resetPage(); }

    // Open modals
    public function openCreateModal()
    {
        $this->resetValidation();
        $this->resetCreateForm();
        $this->dispatch('modal-create-open');
    }

    public function openEditModal($id)
    {
        $this->resetValidation();
        $k = Karyawan::findOrFail($id);
        $this->edit = [
            'id'     => $k->id, // ⬅️ gunakan id standar
            'nik'    => $k->nik,
            'nama'   => $k->nama,
            'alamat' => $k->alamat,
            'email'  => $k->email,
            'divisi' => $k->divisi,
            'jabatan'=> $k->jabatan,
            'role'   => $k->role,
            'foto'   => null,
        ];
        $this->dispatch('modal-edit-open');
    }

    public function openPasswordModal($id)
    {
        $this->resetValidation();
        $k = Karyawan::findOrFail($id);
        $this->password = [
            'id' => $k->id, // ⬅️ gunakan id standar
            'nama' => $k->nama,
            'password' => '',
            'password_confirmation' => '',
        ];
        $this->dispatch('modal-password-open');
    }

    // STORE
    public function store()
    {
        try {
            $this->validate($this->rulesCreate(), $this->messages, $this->validationAttributes);
        } catch (ValidationException $e) {
            $this->dispatch('create-form-has-errors', from: 'store');
            throw $e;
        }

        DB::beginTransaction();
        try {
            if ($this->create['foto']) {
                $this->create['foto'] = $this->create['foto']->store('karyawan', 'local');
            }
            Karyawan::create($this->create);

            DB::commit();
            Alert::success('Berhasil', 'Data karyawan berhasil ditambahkan');
            return redirect()->route('admin.data.karyawan');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            Alert::error('Gagal', 'Data karyawan gagal ditambahkan');
            return redirect()->route('admin.data.karyawan');
        }
    }

    // UPDATE
    public function update()
    {
        $id = $this->edit['id'] ?? null;

        try {
            $this->validate($this->rulesEdit($id), $this->messages, $this->validationAttributes);
        } catch (ValidationException $e) {
            $this->dispatch('edit-form-has-errors', from: 'update');
            throw $e;
        }

        DB::beginTransaction();
        try {
            $karyawan = Karyawan::findOrFail($id);

            $auth = Auth::user();
            if (!$auth) abort(401);
            if ($auth->role !== 'admin' && $auth->id !== $karyawan->id) {
                abort(403, 'Tidak boleh mengubah data milik orang lain.');
            }

            // Default: pertahankan foto lama
            $newPath = $karyawan->foto;

            // Jika upload foto baru → hapus lama & simpan baru
            if ($this->edit['foto']) {
                if ($newPath && Storage::disk('local')->exists($newPath)) {
                    Storage::disk('local')->delete($newPath);
                }
                $newPath = $this->edit['foto']->store('karyawan', 'local');
            }

            $karyawan->update([
                'nik'    => $this->edit['nik'],
                'nama'   => $this->edit['nama'],
                'alamat' => $this->edit['alamat'],
                'email'  => $this->edit['email'],
                'divisi' => $this->edit['divisi'],
                'jabatan'=> $this->edit['jabatan'],
                'role'   => $this->edit['role'],
                'foto'   => $newPath,
            ]);

            DB::commit();

            if (($auth->id ?? null) === $karyawan->id) {
                session()->flash('pf_ver', now()->timestamp);
                $this->dispatch('profile-photo-updated', version: now()->timestamp);
            }

            Alert::success('Berhasil', 'Data karyawan berhasil diupdate');
            return redirect()->route('admin.data.karyawan');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            Alert::error('Gagal', 'Data karyawan gagal diupdate');
            return redirect()->route('admin.data.karyawan');
        }
    }

    // UPDATE PASSWORD
    public function updatePassword()
    {
        try {
            $this->validate(
                ['password.password' => ['required', 'min:6', 'regex:/^(?=.*[A-Z])(?=.*\d).{6,}$/', 'confirmed']],
                [
                    'password.password.min' => 'Password minimal 6 karakter.',
                    'password.password.regex' => 'Password wajib memiliki ≥1 huruf besar & ≥1 angka.',
                    'password.password.confirmed' => 'Konfirmasi password tidak cocok.',
                    'required' => ':attribute wajib diisi.',
                ],
                $this->validationAttributes,
            );
        } catch (ValidationException $e) {
            $this->dispatch('password-form-has-errors', from: 'updatePassword');
            throw $e;
        }

        DB::beginTransaction();
        try {
            $karyawan = Karyawan::findOrFail($this->password['id']);

            $auth = Auth::user();
            if (!$auth) abort(401);
            if ($auth->role !== 'admin' && $auth->id !== $karyawan->id) {
                abort(403, 'Tidak boleh mengubah password milik orang lain.');
            }

            $karyawan->password = $this->password['password'];
            $karyawan->save();

            DB::commit();
            Alert::success('Berhasil', 'Password karyawan berhasil diubah');
            return redirect()->route('admin.data.karyawan');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            Alert::error('Gagal', 'Password karyawan gagal diubah');
            return redirect()->route('admin.data.karyawan');
        }
    }

    // DELETE
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $auth = Auth::user();
            if (!$auth) abort(401);
            if ($auth->role !== 'admin') abort(403, 'Hanya admin yang boleh menghapus karyawan.');

            $k = Karyawan::findOrFail($id);

            if ($k->foto && Storage::disk('local')->exists($k->foto)) {
                Storage::disk('local')->delete($k->foto);
            }

            $nama = $k->nama;
            $k->delete();

            DB::commit();

            Alert::success('Berhasil', "Karyawan {$nama} berhasil dihapus");
            return redirect()->route('admin.data.karyawan');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);
            Alert::error('Gagal', 'Karyawan gagal dihapus');
            return redirect()->route('admin.data.karyawan');
        }
    }

    // Helpers
    public function resetCreateForm()   { $this->reset('create'); }
    public function resetEditForm()     { $this->reset('edit'); }
    public function resetPasswordForm() { $this->reset('password'); }

    public function render()
    {
        $q = trim($this->q);

        $karyawans = Karyawan::when($q, function ($query) use ($q) {
            $like = "%{$q}%";
            $query->where(function ($sub) use ($like) {
                $sub->where('nik', 'like', $like)
                    ->orWhere('nama', 'like', $like)
                    ->orWhere('email', 'like', $like)
                    ->orWhere('divisi', 'like', $like)
                    ->orWhere('jabatan', 'like', $like);
            });
        })
        ->orderByDesc('id') // ⬅️ gunakan kolom id standar
        ->paginate($this->perPage);

        return view('livewire.admin.data-karyawan', compact('karyawans'));
    }

    // Self edit/password
    public function openSelfEdit()
    {
        $this->resetValidation();
        $me = Auth::user();
        abort_unless($me, 403);

        $this->edit = [
            'id'     => $me->id, // ⬅️ id standar
            'nik'    => $me->nik,
            'nama'   => $me->nama,
            'alamat' => $me->alamat,
            'email'  => $me->email,
            'divisi' => $me->divisi,
            'jabatan'=> $me->jabatan,
            'role'   => $me->role,
            'foto'   => null,
        ];

        $this->dispatch('modal-edit-open');
    }

    public function openSelfPassword()
    {
        $this->resetValidation();
        $me = Auth::user();
        abort_unless($me, 403);

        $this->password = [
            'id' => $me->id, // ⬅️ id standar
            'nama' => $me->nama,
            'password' => '',
            'password_confirmation' => '',
        ];

        $this->dispatch('modal-password-open');
    }
}
