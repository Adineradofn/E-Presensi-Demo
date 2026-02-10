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

    /** Penanda anti-race agar response lama tidak menimpa state terbaru */
    public ?int $openRequestedAt = null;

    public $create = [
        'nik' => '',
        'nama' => '',
        'alamat' => '',
        'email' => '',
        'password' => '',
        'password_confirmation' => '',
        'divisi' => '',
        'jabatan' => '',
        'jenis_kelamin' => '',
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
        'jenis_kelamin' => '',
        'role' => '',
        'foto' => null,
    ];

    public $password = [
        'id' => null,
        'nama' => '',
        'password' => '',
        'password_confirmation' => '',
    ];

    /** URL foto lama untuk preview di modal edit (admin) */
    public $editCurrentPhotoUrl = null;

    /** Flag dari tombol hapus foto (admin modal) */
    public $editRemovePhoto = false;

    /** 🔁 Alias supaya kompatibel jika Blade memanggil $wire.set('removePhoto', ...) */
    public $removePhoto = false;

    protected $queryString = ['q'];

    // ===========================
    // Validation
    // ===========================
    protected function rulesCreate()
    {
        return [
            'create.nik' => ['required', 'max:100', 'unique:karyawan,nik'],
            'create.nama' => ['required', 'max:100'],
            'create.alamat' => ['required', 'max:255'],
            'create.email' => ['required', 'email', 'max:150', 'unique:karyawan,email'],
            'create.password' => ['required', 'min:6', 'regex:/^(?=.*[A-Z])(?=.*\d).{6,}$/', 'confirmed'],
            'create.divisi' => ['required', 'max:100'],
            'create.jabatan' => ['required', 'max:100'],
            'create.jenis_kelamin' => ['required', Rule::in(['Laki-Laki', 'Perempuan'])],
            'create.role' => ['required', Rule::in(['admin', 'co-admin', 'karyawan'])],
            'create.foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    protected function rulesEdit($id)
    {
        return [
            'edit.nik' => ['required', 'max:100', Rule::unique('karyawan', 'nik')->ignore($id, 'id')],
            'edit.nama' => ['required', 'max:100'],
            'edit.alamat' => ['required', 'max:255'],
            'edit.email' => ['required', 'email', 'max:150', Rule::unique('karyawan', 'email')->ignore($id, 'id')],
            'edit.divisi' => ['required', 'max:100'],
            'edit.jabatan' => ['required', 'max:100'],
            'edit.jenis_kelamin' => ['required', Rule::in(['Laki-Laki', 'Perempuan'])],
            'edit.role' => ['required', Rule::in(['admin', 'co-admin', 'karyawan'])],
            'edit.foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    protected $messages = [
        'required' => ':attribute wajib diisi.',
        'unique' => ':attribute sudah terdaftar.',
        'max' => ':attribute maksimal :max karakter.',
        'email' => 'Format email tidak valid.',
        'image' => 'File harus berupa gambar.',
        'mimes' => 'Format file harus: :values.',

        'create.password.min' => 'Password minimal 6 karakter.',
        'create.password.regex' => 'Password wajib memiliki ≥1 huruf besar & ≥1 angka.',
        'create.password.confirmed' => 'Konfirmasi password tidak cocok.',

        'create.foto.max' => 'Ukuran foto maksimal 2 MB.',
        'edit.foto.max' => 'Ukuran foto maksimal 2 MB.',
        'create.foto.mimes' => 'Format foto harus JPG, JPEG, PNG, atau WebP.',
        'edit.foto.mimes' => 'Format foto harus JPG, JPEG, PNG, atau WebP.',
        'create.foto.image' => 'File harus berupa gambar.',
        'edit.foto.image' => 'File harus berupa gambar.',
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
        'create.jenis_kelamin' => 'Jenis Kelamin',
        'create.role' => 'Role',
        'create.foto' => 'Foto',

        'edit.nik' => 'NIK',
        'edit.nama' => 'Nama',
        'edit.alamat' => 'Alamat',
        'edit.email' => 'Email',
        'edit.divisi' => 'Divisi',
        'edit.jabatan' => 'Jabatan',
        'edit.jenis_kelamin' => 'Jenis Kelamin',
        'edit.role' => 'Role',
        'edit.foto' => 'Foto',

        'password.password' => 'Password',
        'password.password_confirmation' => 'Konfirmasi Password',
    ];

    public function updatingQ()
    {
        $this->resetPage();
    }

    // ===========================
    // Open modals (tanpa membuka modal di server)
    // ===========================
    public function openCreateModal()
    {
        // (tidak dipakai lagi untuk buka/tutup; dibiarkan agar kompatibel)
        $this->resetValidation();
        $this->resetCreateForm();

        // anti-race
        $this->openRequestedAt = hrtime(true);

        return true; // Biarkan Blade yang membuka modal setelah await
    }

    public function openEditModal($id)
    {
        $this->resetValidation();

        // anti-race: stempel request paling baru
        $stamp = hrtime(true);
        $this->openRequestedAt = $stamp;

        $k = Karyawan::findOrFail($id);

        // siapkan payload
        $payload = [
            'id' => $k->id,
            'nik' => $k->nik,
            'nama' => $k->nama,
            'alamat' => $k->alamat,
            'email' => $k->email,
            'divisi' => $k->divisi,
            'jabatan' => $k->jabatan,
            'jenis_kelamin' => $k->jenis_kelamin,
            'role' => $k->role,
            'foto' => null,
        ];

        $url = $k->foto ? route('admin.data.karyawan.foto', ['karyawan' => $k->getKey()]) . '?v=' . now()->timestamp : null;

        // jika ada request yang lebih baru, jangan timpa state
        if ($this->openRequestedAt !== $stamp) {
            return false;
        }

        $this->edit = $payload;
        $this->editCurrentPhotoUrl = $url;
        $this->editRemovePhoto = false;
        $this->removePhoto = false;

        return true;
    }

    public function openPasswordModal($id)
    {
        $this->resetValidation();

        // anti-race
        $this->openRequestedAt = hrtime(true);

        $k = Karyawan::findOrFail($id);
        $this->password = [
            'id' => $k->id,
            'nama' => $k->nama,
            'password' => '',
            'password_confirmation' => '',
        ];

        return true;
    }

    /** Dipanggil Alpine untuk ambil URL preview (admin modal) */
    public function getEditCurrentPhotoUrl(): ?string
    {
        return $this->editCurrentPhotoUrl;
    }

    // ===========================
    // STORE
    // ===========================
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

    // ===========================
    // UPDATE
    // ===========================
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
            if (!$auth) {
                abort(401);
            }

            $canManageOthers = in_array($auth->role, ['admin', 'co-admin'], true);
            if (!$canManageOthers && $auth->id !== $karyawan->id) {
                abort(403, 'Tidak boleh mengubah data milik orang lain.');
            }

            // === LOGIKA FOTO (sinkron dengan preview) ===
            $newPath = $karyawan->foto;

            if ($this->edit['foto']) {
                if ($newPath && Storage::disk('local')->exists($newPath)) {
                    Storage::disk('local')->delete($newPath);
                }
                $newPath = $this->edit['foto']->store('karyawan', 'local');
                $this->editRemovePhoto = false;
                $this->removePhoto = false;
            } elseif ($this->editRemovePhoto || $this->removePhoto) {
                if ($newPath && Storage::disk('local')->exists($newPath)) {
                    Storage::disk('local')->delete($newPath);
                }
                $newPath = null;
            }

            $karyawan->update([
                'nik' => $this->edit['nik'],
                'nama' => $this->edit['nama'],
                'alamat' => $this->edit['alamat'],
                'email' => $this->edit['email'],
                'divisi' => $this->edit['divisi'],
                'jabatan' => $this->edit['jabatan'],
                'jenis_kelamin' => $this->edit['jenis_kelamin'],
                'role' => $this->edit['role'],
                'foto' => $newPath,
            ]);

            DB::commit();

            if (($auth->id ?? null) === $karyawan->id) {
                $ver = now()->timestamp;
                session()->flash('pf_ver', $ver);
                $this->dispatch('profile-photo-updated', version: $ver);
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

    // ===========================
    // UPDATE PASSWORD
    // ===========================
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
            if (!$auth) {
                abort(401);
            }

            $canManageOthers = in_array($auth->role, ['admin', 'co-admin'], true);
            if (!$canManageOthers && $auth->id !== $karyawan->id) {
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

    // ===========================
    // DELETE
    // ===========================
    public function destroy($id)
    {
        $auth = Auth::user();
        abort_if(!$auth, 401);
        abort_if(!in_array($auth->role, ['admin', 'co-admin'], true), 403, 'Hanya admin & co-admin yang boleh menghapus karyawan.');

        $k = Karyawan::findOrFail($id);

        if ($k->presensi()->exists()) {
            Alert::error('Tidak dapat dihapus', 'Karyawan sudah memiliki data presensi');
            return redirect()->route('admin.data.karyawan');
        }

        DB::beginTransaction();
        try {
            $fotoPath = $k->foto;
            $nama = $k->nama;

            $k->delete();

            if ($fotoPath) {
                DB::afterCommit(function () use ($fotoPath) {
                    try {
                        if (Storage::disk('local')->exists($fotoPath)) {
                            Storage::disk('local')->delete($fotoPath);
                        }
                    } catch (\Throwable $e) {
                        report($e);
                    }
                });
            }

            DB::commit();

            Alert::success('Berhasil', "Karyawan {$nama} berhasil dihapus");
            return redirect()->route('admin.data.karyawan');
        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            if ($k->presensi()->exists()) {
                Alert::error('Tidak dapat dihapus', 'Karyawan sudah memiliki data presensi');
            } else {
                Alert::error('Gagal', 'Karyawan gagal dihapus');
            }
            return redirect()->route('admin.data.karyawan');
        }
    }

    // ===========================
    // Helpers
    // ===========================
    public function resetCreateForm()
    {
        $this->reset('create');
    }
    public function resetEditForm()
    {
        $this->reset('edit', 'editCurrentPhotoUrl', 'editRemovePhoto', 'removePhoto');
    }
    public function resetPasswordForm()
    {
        $this->reset('password');
    }

    public function updatedRemovePhoto($val)
    {
        $this->editRemovePhoto = (bool) $val;
    }
    public function updatedEditRemovePhoto($val)
    {
        $this->removePhoto = (bool) $val;
    }

    // ===========================
    // Render
    // ===========================
    public function render()
    {
        $q = trim($this->q);

        $karyawans = Karyawan::when($q, function ($query) use ($q) {
            $like = "%{$q}%";
            $query->where(function ($sub) use ($like) {
                $sub->where('nik', 'like', $like)->orWhere('nama', 'like', $like)->orWhere('email', 'like', $like)->orWhere('divisi', 'like', $like)->orWhere('jabatan', 'like', $like);
            });
        })
            ->orderByDesc('id')
            ->paginate($this->perPage);

        return view('livewire.admin.data-karyawan', compact('karyawans'));
    }

    // ===========================
    // (Opsional) Self edit/password lewat modal yang sama
    // ===========================
    public function openSelfEdit()
    {
        $this->resetValidation();
        $me = Auth::user();
        abort_unless($me, 403);

        // anti-race
        $stamp = hrtime(true);
        $this->openRequestedAt = $stamp;

        $this->edit = [
            'id' => $me->id,
            'nik' => $me->nik,
            'nama' => $me->nama,
            'alamat' => $me->alamat,
            'email' => $me->email,
            'divisi' => $me->divisi,
            'jabatan' => $me->jabatan,
            'jenis_kelamin' => $me->jenis_kelamin,
            'role' => $me->role,
            'foto' => null,
        ];

        $this->editCurrentPhotoUrl = $me->foto ? route('data.karyawan.foto', ['karyawan' => $me->getKey()]) . '?v=' . now()->timestamp : null;

        if ($this->openRequestedAt !== $stamp) {
            return false;
        }

        $this->editRemovePhoto = false;
        $this->removePhoto = false;

        return true;
    }

    public function openSelfPassword()
    {
        $this->resetValidation();
        $me = Auth::user();
        abort_unless($me, 403);

        // anti-race
        $this->openRequestedAt = hrtime(true);

        $this->password = [
            'id' => $me->id,
            'nama' => $me->nama,
            'password' => '',
            'password_confirmation' => '',
        ];

        return true;
    }
}
