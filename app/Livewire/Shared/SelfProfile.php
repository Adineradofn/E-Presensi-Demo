<?php

namespace App\Livewire\Shared;

use App\Models\Karyawan;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Validation\Rule;
use RealRashid\SweetAlert\Facades\Alert;

class SelfProfile extends Component
{
    use WithFileUploads; // dukungan upload sementara Livewire

    // STATE FORM EDIT PROFIL (diri sendiri)
    public $edit = [
        'id' => null,
        'nik' => '',
        'nama' => '',
        'alamat' => '',
        'email' => '',
        'divisi' => '',
        'jabatan' => '',
        'foto' => null, // Livewire\TemporaryUploadedFile (nullable)
    ];

    // STATE FORM UBAH PASSWORD (diri sendiri)
    public $password = [
        'id' => null,
        'nama' => '',
        'password' => '',
        'password_confirmation' => '',
    ];

    // Pesan validasi (Bahasa Indonesia)
    protected $messages = [
        // umum
        'required' => ':attribute wajib diisi.',
        'unique'   => ':attribute sudah terdaftar.',
        'max'      => ':attribute maksimal :max karakter.', // untuk STRING

        'email' => 'Format email tidak valid.',
        'image' => 'File harus berupa gambar.',
        'mimes' => 'Format file harus: :values.',

        // password (kalau dipakai di validate() dengan key password.password*)
        'password.password.min'       => 'Password minimal 6 karakter.',
        'password.password.regex'     => 'Password wajib memiliki ≥1 huruf besar & ≥1 angka.',
        'password.password.confirmed' => 'Konfirmasi password tidak cocok.',

        // ✅ Override khusus FOTO (supaya tidak pakai “karakter”):
        'edit.foto.max'   => 'Ukuran foto maksimal 2 MB.',
        'edit.foto.mimes' => 'Format foto harus JPG, JPEG, PNG, atau WebP.',
        'edit.foto.image' => 'File harus berupa gambar.',
    ];

    // Label atribut
    protected $validationAttributes = [
        'edit.nik' => 'NIK',
        'edit.nama' => 'Nama',
        'edit.alamat' => 'Alamat',
        'edit.email' => 'Email',
        'edit.divisi' => 'Divisi',
        'edit.jabatan' => 'Jabatan',
        'edit.foto' => 'Foto',
        'password.password' => 'Password',
        'password.password_confirmation' => 'Konfirmasi Password',
    ];

    // Aturan validasi edit profil diri sendiri
    protected function rulesEditSelf($id)
    {
        return [
            'edit.nik'     => ['required', 'max:100', Rule::unique('karyawan', 'nik')->ignore($id, 'id_karyawan')],
            'edit.nama'    => ['required', 'max:100'],
            'edit.alamat'  => ['required', 'max:255'],
            'edit.email'   => ['required', 'email', 'max:150', Rule::unique('karyawan', 'email')->ignore($id, 'id_karyawan')],
            'edit.divisi'  => ['required', 'max:100'],
            'edit.jabatan' => ['required', 'max:100'],
            // max:2048 = 2MB (Laravel hitung dalam KB)
            'edit.foto'    => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    // Render
    public function render()
    {
        return view('livewire.shared.self-profile');
    }

    // Prefill edit dari user login
    public function fillFromAuth(): void
    {
        $me = Auth::user();
        abort_unless($me, 401);

        $this->resetValidation();
        $this->edit = [
            'id'     => $me->id_karyawan,
            'nik'    => $me->nik,
            'nama'   => $me->nama,
            'alamat' => $me->alamat,
            'email'  => $me->email,
            'divisi' => $me->divisi,
            'jabatan'=> $me->jabatan,
            'foto'   => null, // input file selalu null saat buka form
        ];
    }

    // Prefill password dari user login
    public function fillPasswordFromAuth(): void
    {
        $me = Auth::user();
        abort_unless($me, 401);

        $this->resetValidation();
        $this->password = [
            'id' => $me->id_karyawan,
            'nama' => $me->nama,
            'password' => '',
            'password_confirmation' => '',
        ];
    }

    // Helpers reset form
    public function resetEditForm(): void     { $this->reset('edit'); }
    public function resetPasswordForm(): void { $this->reset('password'); }

    /** Update profil diri sendiri */
    public function updateSelf()
    {
        $auth = Auth::user();
        abort_unless($auth, 401);

        $id = $this->edit['id'] ?? null;
        if ((int) $auth->id_karyawan !== (int) $id) {
            abort(403, 'Tidak boleh mengubah data milik orang lain dari modal ini.');
        }

        // ✅ Pakai pesan & label yang sudah diset agar foto tidak “2048 karakter”
        $this->validate($this->rulesEditSelf($id), $this->messages, $this->validationAttributes);

        DB::beginTransaction();
        try {
            $karyawan = Karyawan::findOrFail($id);

            // LOGIKA FOTO:
            // - Upload baru → hapus lama & simpan baru
            // - Tidak upload → hapus lama & set null (sesuai UI “Kosongkan untuk menghapus foto lama”)
            if ($this->edit['foto']) {
                if ($karyawan->foto && Storage::disk('local')->exists($karyawan->foto)) {
                    Storage::disk('local')->delete($karyawan->foto);
                }
                $newPath = $this->edit['foto']->store('karyawan', 'local');
            } else {
                if ($karyawan->foto && Storage::disk('local')->exists($karyawan->foto)) {
                    Storage::disk('local')->delete($karyawan->foto);
                }
                $newPath = null;
            }

            // Update data
            $karyawan->update([
                'nik'    => $this->edit['nik'],
                'nama'   => $this->edit['nama'],
                'alamat' => $this->edit['alamat'],
                'email'  => $this->edit['email'],
                'divisi' => $this->edit['divisi'],
                'jabatan'=> $this->edit['jabatan'],
                'foto'   => $newPath,
            ]);

            DB::commit();

            // bump versi avatar (cache bust)
            $ver = now()->timestamp;
            session()->flash('pf_ver', $ver);

            // Refresh user
            $auth->refresh();
            Auth::setUser($auth);

            // Event UI
            $this->dispatch('profile-photo-updated', version: $ver);
            $this->dispatch('self-profile:name-updated', name: $this->edit['nama']);
            $this->dispatch('self-profile:success');

            // Notifikasi
            Alert::success('Berhasil', 'Profil berhasil diupdate');

            // Redirect penuh
            return redirect()->to(
                request()->headers->get('Referer') ?: url()->current()
            );

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            Alert::error('Gagal', 'Profil gagal diupdate');
            $this->dispatch('self-profile:success');

            return redirect()->to(url()->current());
        }
    }

    /** Update password diri sendiri */
    public function updateSelfPassword()
    {
        $auth = Auth::user();
        abort_unless($auth, 401);

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

        $id = $this->password['id'] ?? null;
        if ((int) $auth->id_karyawan !== (int) $id) {
            abort(403, 'Tidak boleh mengubah password milik orang lain dari modal ini.');
        }

        DB::beginTransaction();
        try {
            $karyawan = Karyawan::findOrFail($id);

            // Pastikan Model Karyawan punya cast/mutator password -> hashed
            $karyawan->password = $this->password['password'];
            $karyawan->save();

            DB::commit();

            $this->dispatch('self-profile:success');
            Alert::success('Berhasil', 'Password berhasil diubah');

            return redirect()->to(
                request()->headers->get('Referer') ?: url()->current()
            );

        } catch (\Throwable $e) {
            DB::rollBack();
            report($e);

            $this->dispatch('self-profile:success');
            Alert::error('Gagal', 'Password gagal diubah');

            return redirect()->to(url()->current());
        }
    }
}
