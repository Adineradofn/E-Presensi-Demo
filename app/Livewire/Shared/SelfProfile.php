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
    use WithFileUploads;

    /**
     * STATE FORM EDIT (khusus diri sendiri)
     * HANYA: nama, alamat, email, foto (opsional ganti/hapus)
     */
    public $edit = [
        'id'     => null,
        'nama'   => '',
        'alamat' => '',
        'email'  => '',
        'foto'   => null, // Livewire\TemporaryUploadedFile
    ];

    /** URL foto saat ini (untuk preview awal) */
    public $currentPhotoUrl = null;

    /** Flag dari tombol "Hapus Foto" di UI */
    public $removePhoto = false;

    // STATE FORM UBAH PASSWORD (tetap)
    public $password = [
        'id' => null,
        'nama' => '',
        'password' => '',
        'password_confirmation' => '',
    ];

    // Pesan validasi
    protected $messages = [
        'required' => ':attribute wajib diisi.',
        'unique'   => ':attribute sudah terdaftar.',
        'max'      => ':attribute maksimal :max karakter.',
        'email'    => 'Format email tidak valid.',

        // password
        'password.password.min'       => 'Password minimal 6 karakter.',
        'password.password.regex'     => 'Password wajib memiliki ≥1 huruf besar & ≥1 angka.',
        'password.password.confirmed' => 'Konfirmasi password tidak cocok.',

        // foto
        'edit.foto.max'   => 'Ukuran foto maksimal 2 MB.',
        'edit.foto.mimes' => 'Format foto harus JPG, JPEG, PNG, atau WebP.',
        'edit.foto.image' => 'File harus berupa gambar.',
    ];

    // Label atribut
    protected $validationAttributes = [
        'edit.nama'   => 'Nama',
        'edit.alamat' => 'Alamat',
        'edit.email'  => 'Email',
        'edit.foto'   => 'Foto',
        'password.password' => 'Password',
        'password.password_confirmation' => 'Konfirmasi Password',
    ];

    /** Aturan validasi edit diri sendiri */
    protected function rulesEditSelf($id)
    {
        return [
            'edit.nama'   => ['required', 'max:100'],
            'edit.alamat' => ['required', 'max:255'],
            'edit.email'  => ['required', 'email', 'max:150', Rule::unique('karyawan', 'email')->ignore($id, 'id')],
            'edit.foto'   => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'], // 2MB
        ];
    }

    public function render()
    {
        return view('livewire.shared.self-profile');
    }

    /** Prefill form dari user login + siapkan URL foto untuk preview awal */
    public function fillFromAuth(): void
    {
        $me = Auth::user();
        abort_unless($me, 401);

        $this->resetValidation();

        $this->edit = [
            'id'     => $me->getKey(), // <- pakai PK yang benar (id)
            'nama'   => $me->nama,
            'alamat' => $me->alamat,
            'email'  => $me->email,
            'foto'   => null,
        ];

        $this->currentPhotoUrl = $me->foto
            ? route('data.karyawan.foto', ['karyawan' => $me->getKey()]) . '?v=' . now()->timestamp
            : null;

        $this->removePhoto = false;
    }

    /** Dipanggil dari Alpine untuk baca URL foto saat ini (hindari nama tabrakan properti) */
    public function getCurrentPhotoUrl(): ?string
    {
        return $this->currentPhotoUrl;
    }

    /** Prefill password tetap */
    public function fillPasswordFromAuth(): void
    {
        $me = Auth::user();
        abort_unless($me, 401);

        $this->resetValidation();
        $this->password = [
            'id' => $me->getKey(), // <- pakai id
            'nama' => $me->nama,
            'password' => '',
            'password_confirmation' => '',
        ];
    }

    // Helpers reset form
    public function resetEditForm(): void
    {
        $this->reset('edit', 'currentPhotoUrl', 'removePhoto');
    }
    public function resetPasswordForm(): void
    {
        $this->reset('password');
    }

    /** Update profil diri sendiri */
    public function updateSelf()
    {
        $auth = Auth::user();
        abort_unless($auth, 401);

        $id = $this->edit['id'] ?? null;
        if ((int) $auth->getKey() !== (int) $id) {
            abort(403, 'Tidak boleh mengubah data milik orang lain dari modal ini.');
        }

        $this->validate($this->rulesEditSelf($id), $this->messages, $this->validationAttributes);

        DB::beginTransaction();
        try {
            $karyawan = Karyawan::findOrFail($id);

            // === LOGIKA FOTO ===
            $newPath = $karyawan->foto;

            if ($this->edit['foto']) {
                // Upload baru override removePhoto
                if ($karyawan->foto && Storage::disk('local')->exists($karyawan->foto)) {
                    Storage::disk('local')->delete($karyawan->foto);
                }
                $newPath = $this->edit['foto']->store('karyawan', 'local');
                $this->removePhoto = false;
            } elseif ($this->removePhoto) {
                if ($karyawan->foto && Storage::disk('local')->exists($karyawan->foto)) {
                    Storage::disk('local')->delete($karyawan->foto);
                }
                $newPath = null;
            }

            // Update field yang diizinkan
            $karyawan->update([
                'nama'   => $this->edit['nama'],
                'alamat' => $this->edit['alamat'],
                'email'  => $this->edit['email'],
                'foto'   => $newPath,
            ]);

            DB::commit();

            // bump versi avatar (cache bust)
            $ver = now()->timestamp;
            session()->flash('pf_ver', $ver);

            // Refresh user + perbarui currentPhotoUrl
            $auth->refresh();
            Auth::setUser($auth);

            $this->currentPhotoUrl = $karyawan->foto
                ? route('data.karyawan.foto', ['karyawan' => $karyawan->getKey()]) . '?v=' . $ver
                : null;

            // Event UI
            $this->dispatch('profile-photo-updated', version: $ver);
            $this->dispatch('self-profile:name-updated', name: $this->edit['nama']);
            $this->dispatch('self-profile:success');

            Alert::success('Berhasil', 'Profil berhasil diupdate');

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
        if ((int) $auth->getKey() !== (int) $id) {
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
