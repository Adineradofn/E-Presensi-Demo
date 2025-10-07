 function open(el)  { if (el) el.classList.add('show'); el?.classList.remove('hidden'); }
  function close(el) { if (el) el.classList.remove('show'); }

  // ambil template route
  const routeTplEl = document.getElementById('routeTpl');
  const updateRouteTpl = routeTplEl?.dataset?.update || '';
  const pwRouteTpl     = routeTplEl?.dataset?.pw || '';

  // ===== CREATE (biarkan seperti sudah kamu buat) =====
  const modalCreate   = document.getElementById('modalCreate');
  const btnOpenCreate = document.getElementById('btnOpenCreate');
  btnOpenCreate?.addEventListener('click', (e) => { e.preventDefault(); open(modalCreate); });
  modalCreate?.querySelectorAll('[data-close-modal], .btnCloseCreate')
    .forEach(btn => btn.addEventListener('click', () => close(modalCreate)));

  // ===== EDIT — listen ke event Alpine "$dispatch('open-edit', { ... })" =====
  const modalEdit = document.getElementById('modalEdit');
  const formEdit  = document.getElementById('formEdit');

  window.addEventListener('open-edit', (evt) => {
    const d = evt.detail || {};
    if (!formEdit || !d.id) return;

    formEdit.action = updateRouteTpl.replace('__ID__', d.id);

    // hidden markers
    const inputId   = formEdit.querySelector('input[name="id"]');
    const inputOpen = formEdit.querySelector('input[name="_open"]');
    if (inputId)   inputId.value = d.id;
    if (inputOpen) inputOpen.value = 'edit';

    // isi field (kalau mau override old())
    if (formEdit.nik)     formEdit.nik.value     = d.nik     ?? '';
    if (formEdit.nama)    formEdit.nama.value    = d.nama    ?? '';
    if (formEdit.alamat)  formEdit.alamat.value  = d.alamat  ?? '';
    if (formEdit.email)   formEdit.email.value   = d.email   ?? '';
    if (formEdit.divisi)  formEdit.divisi.value  = d.divisi  ?? '';
    if (formEdit.jabatan) formEdit.jabatan.value = d.jabatan ?? '';
    if (formEdit.role)    formEdit.role.value    = d.role    ?? '';

    open(modalEdit);
  });

  modalEdit?.querySelectorAll('[data-close-modal], .btnCloseEdit')
    .forEach(btn => btn.addEventListener('click', () => close(modalEdit)));

  // ===== PASSWORD — listen ke event Alpine "$dispatch('open-password', { ... })" =====
  const modalPassword = document.getElementById('modalPassword');
  const formPassword  = document.getElementById('formPassword');
  const pwNama        = document.getElementById('pwNama');

  window.addEventListener('open-password', (evt) => {
    const d = evt.detail || {};
    if (!formPassword || !d.id) return;

    const pwRouteTpl = document.getElementById('routeTpl')?.dataset?.pw || '';
    formPassword.action = pwRouteTpl.replace('__ID__', d.id);

    const inputId   = formPassword.querySelector('input[name="id"]');
    const inputOpen = formPassword.querySelector('input[name="_open"]');
    if (inputId)   inputId.value = d.id;
    if (inputOpen) inputOpen.value = 'password';

    if (pwNama) pwNama.textContent = d.nama ? `(${d.nama})` : ''; // FIX: backtick

    if (modalPassword) modalPassword.classList.add('show');
  });

  modalPassword?.querySelectorAll('[data-close-modal], .btnClosePassword')
    .forEach(btn => btn.addEventListener('click', () => close(modalPassword)));

  // ===== AUTO-OPEN setelah validasi gagal =====
  const stateEl   = document.getElementById('pageState');
  const hasErrors = stateEl?.dataset?.hasErrors === '1';
  const which     = stateEl?.dataset?.which || '';
  const oldId     = stateEl?.dataset?.id    || '';

  if (hasErrors) {
    if (which === 'create') open(modalCreate);
    if (which === 'edit' && oldId && formEdit) {
      formEdit.action = updateRouteTpl.replace('__ID__', oldId);
      open(modalEdit);
    }
    if (which === 'password' && oldId && formPassword) {
      formPassword.action = pwRouteTpl.replace('__ID__', oldId);
      open(modalPassword);
    }
  }

  // === Auto Search (debounce) ===
(function () {
  const form = document.getElementById('formSearch');
  const input = document.getElementById('searchInput');
  if (!form || !input) return;

  // helper debounce sederhana
  let t = null;
  const DEBOUNCE_MS = 500; // ganti 300–700ms sesuai selera

  // Saat mengetik, submit otomatis setelah berhenti ngetik selama DEBOUNCE_MS
  function queueSubmit() {
    clearTimeout(t);
    t = setTimeout(() => {
      // reset halaman ke 1 kalau ada query string "page"
      try {
        const url = new URL(form.action, window.location.origin);
        const params = new URLSearchParams(window.location.search);

        // set param q ke nilai terkini input
        if (input.value && input.value.trim() !== '') {
          params.set('q', input.value.trim());
        } else {
          // kosong: hapus q biar tampil semua data
          params.delete('q');
        }
        // kalau ada pagination sebelumnya, reset
        params.delete('page');

        url.search = params.toString();
        // navigasi GET biasa, sama seperti submit form
        window.location.assign(url.toString());
      } catch (e) {
        // fallback submit form standar
        form.submit();
      }
    }, DEBOUNCE_MS);
  }

  // Trigger untuk ketikan biasa & paste
  input.addEventListener('input', queueSubmit);
  input.addEventListener('paste', queueSubmit);

  // Opsional: kalau user tekan Escape untuk mengosongkan lalu lepas, lakukan submit
  input.addEventListener('keyup', (e) => {
    if (e.key === 'Escape') {
      input.value = '';
      queueSubmit();
    }
  });
})();


(function () {
    const forms = document.querySelectorAll('form.delete-form .btn-delete');
    if (!forms.length) return;

    forms.forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        const form = e.currentTarget.closest('form');

        // Pakai Swal dari RealRashid (sudah tersedia karena @include('sweetalert::alert'))
        if (typeof Swal === 'undefined') {
          // fallback kalau Swal belum ada
          if (confirm('Yakin ingin menghapus data karyawan ini?')) form.submit();
          return;
        }

        Swal.fire({
          title: 'Hapus Karyawan?',
          text: 'Yakin ingin menghapus data karyawan ini?',
          icon: 'warning',
          showCancelButton: true,
          confirmButtonText: 'Ya, hapus',
          cancelButtonText: 'Batal',
          reverseButtons: true
        }).then((result) => {
          if (result.isConfirmed) form.submit();
        });
      });
    });
  })();

