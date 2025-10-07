<div id="self-profile-root">
  {{-- Semua konten komponen (style, modal, script) harus berada DI DALAM wrapper ini agar tetap 1 root element --}}
  @once
    <style>[x-cloak]{display:none!important}</style>
  @endonce

  @php($err = $errors)

  {{-- 2 modal sebagai partials --}}
  @include('livewire.shared.modals.edit_profile')
  @include('livewire.shared.modals.edit_password')

  {{-- Auto-open kembali jika validasi gagal --}}
  @if ($errors->has('edit.nik') || $errors->has('edit.nama') || $errors->has('edit.alamat') || $errors->has('edit.email') || $errors->has('edit.divisi') || $errors->has('edit.jabatan') || $errors->has('edit.foto'))
    <script>window.dispatchEvent(new CustomEvent('self-profile:edit-open'));</script>
  @endif

  @if ($errors->has('password.password') || $errors->has('password.password_confirmation'))
    <script>window.dispatchEvent(new CustomEvent('self-profile:password-open'));</script>
  @endif
</div>
