<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\Attributes\Url;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\WithPagination;
use Carbon\Carbon;
use Illuminate\Validation\Rule;
use App\Models\Izin;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Support\Facades\Auth;

#[Layout('admin.app_admin')]
#[Title('Pengajuan Izin')]
class DataPengajuanIzin extends Component
{
    use WithPagination;

    /** gunakan tailwind seperti di contoh */
    protected string $paginationTheme = 'tailwind';

    #[Url(as: 'mode', except: 'hari')]
    public string $mode = 'hari'; // hari|bulan|tahun

    #[Url(as: 'date')]
    public ?string $date = null;   // Y-m-d

    #[Url(as: 'month')]
    public ?string $month = null;  // Y-m

    #[Url(as: 'year')]
    public ?string $year = null;   // Y

    #[Url(as: 'q', except: '')]
    public string $q = '';

    /** perPage seperti contoh (tanpa mengubah UI) */
    public int $perPage = 10;

    // State untuk modal ubah status
    public ?int $selectedIzinId = null;
    public string $initialStatus = 'pending';
    public string $selectedStatus = 'pending';
    public string $selectedNama = '';
    public string $selectedMulai = '';
    public string $selectedAkhir = '';

    protected string $tz = 'Asia/Makassar';

    // Duplikasi supaya query string tetap sinkron (kompatibel Livewire lama)
    protected $queryString = [
        'mode'  => ['except' => 'hari'],
        'date'  => ['except' => null],
        'month' => ['except' => null],
        'year'  => ['except' => null],
        'q'     => ['except' => ''],
        // NOTE: perPage tidak dimasukkan ke query string agar UI tetap tidak berubah
    ];

    public function mount(): void
    {
        $this->tz = config('app.timezone', 'Asia/Makassar');
        $this->ensureDefaultsForMode();
    }

    /** reset page saat parameter pencarian berubah — mengikuti pola contoh */
    public function updatingQ()       { $this->resetPage(); }
    public function updatedDate()     { $this->resetPage(); }
    public function updatedMonth()    { $this->resetPage(); }
    public function updatedYear()     { $this->resetPage(); }
    public function updatedPerPage()  { $this->resetPage(); }

    public function setMode(string $mode): void
    {
        $this->mode = in_array($mode, ['hari','bulan','tahun'], true) ? $mode : 'hari';
        $this->ensureDefaultsForMode();
        $this->resetPage();
    }

    private function ensureDefaultsForMode(): void
    {
        $now = Carbon::now($this->tz);

        if ($this->mode === 'bulan') {
            $this->month ??= $now->format('Y-m');
            $this->date = null;
            $this->year = null;
        } elseif ($this->mode === 'tahun') {
            $this->year ??= $now->format('Y');
            $this->date = null;
            $this->month = null;
        } else { // hari
            $this->date ??= $now->toDateString();
            $this->month = null;
            $this->year = null;
        }
    }

    /** Hitung rentang [start, end] berdasarkan mode pemilihan waktu. */
    private function resolveRange(): array
    {
        if ($this->mode === 'bulan') {
            $start = $this->month
                ? Carbon::parse("{$this->month}-01", $this->tz)->startOfMonth()
                : Carbon::now($this->tz)->startOfMonth();
            $end = (clone $start)->endOfMonth();
        } elseif ($this->mode === 'tahun') {
            $yr    = $this->year ? (int)$this->year : Carbon::now($this->tz)->year;
            $start = Carbon::createFromDate($yr, 1, 1, $this->tz)->startOfYear();
            $end   = (clone $start)->endOfYear();
        } else {
            $date  = $this->date ?: Carbon::now($this->tz)->toDateString();
            $start = Carbon::parse($date, $this->tz)->startOfDay();
            $end   = Carbon::parse($date, $this->tz)->endOfDay();
        }
        return [$start, $end];
    }

    /**
     * Batas edit bulanan = tanggal 5 bulan berikutnya, akhir hari (23:59:59)
     * contoh: 2025-09-xx -> 2025-10-05 23:59:59
     */
    private function monthlyEditDeadline(Carbon|string $tanggalPengajuan): Carbon
    {
        $submitted = $tanggalPengajuan instanceof Carbon
            ? $tanggalPengajuan->copy()->setTimezone($this->tz)
            : Carbon::parse($tanggalPengajuan, $this->tz);

        return $submitted->copy()
            ->addMonthNoOverflow()
            ->startOfMonth()
            ->addDays(4) // pindah ke tanggal 5
            ->endOfDay();
    }

    /** Apakah sekarang masih <= batas edit bulanan */
    private function isWithinMonthlyEditWindow(Carbon|string $tanggalPengajuan): bool
    {
        $now    = Carbon::now($this->tz);
        $cutoff = $this->monthlyEditDeadline($tanggalPengajuan);
        return $now->lte($cutoff);
    }

    public function render()
    {
        [$start, $end] = $this->resolveRange();

        $query = Izin::with('karyawan');

        // Filter berdasarkan tanggal_pengajuan
        if ($this->mode === 'hari') {
            $query->whereDate('tanggal_pengajuan', '=', $start->toDateString());
        } else {
            $query->whereBetween('tanggal_pengajuan', [$start->toDateString(), $end->toDateString()]);
        }

        // Pencarian
        if ($this->q !== '') {
            $q = $this->q;
            $query->where(function ($w) use ($q) {
                $w->whereHas('karyawan', function ($k) use ($q) {
                        $k->where('nama', 'like', "%{$q}%")
                          ->orWhere('divisi', 'like', "%{$q}%")
                          ->orWhere('jabatan', 'like', "%{$q}%")
                          ->orWhere('nik', 'like', "%{$q}%");
                    })
                  ->orWhere('jenis', 'like', "%{$q}%")
                  ->orWhere('status', 'like', "%{$q}%")
                  ->orWhere('alasan', 'like', "%{$q}%")
                  ->orWhereRaw('DATE_FORMAT(tanggal_pengajuan, "%Y-%m-%d") like ?', ["%{$q}%"])
                  ->orWhereRaw('DATE_FORMAT(tanggal_mulai, "%Y-%m-%d") like ?', ["%{$q}%"])
                  ->orWhereRaw('DATE_FORMAT(tanggal_selesai, "%Y-%m-%d") like ?', ["%{$q}%"]);
            });
        }

        // ================== INTI PERMINTAAN BARU ==================
        // - admin: HANYA melihat 'tugas'
        // - co-admin: melihat SELAIN 'tugas'
        $role = strtolower((string) (Auth::user()->role ?? ''));
        if ($role === 'admin') {
            $query->where('jenis', 'tugas');
        } elseif ($role === 'co-admin') {
            $query->where('jenis', '!=', 'tugas');
        }
        // ===========================================================

        $items = $query
            ->orderBy('tanggal_pengajuan', 'desc')
            ->orderBy('karyawan_id', 'asc')
            ->paginate($this->perPage)    // ← seperti contoh
            ->withQueryString();

        $current_date  = $this->mode === 'hari'  ? $start->toDateString() : null;
        $current_month = $this->mode === 'bulan' ? $start->format('Y-m') : null;
        $current_year  = $this->mode === 'tahun' ? $start->format('Y')   : null;

        return view('livewire.admin.data-pengajuan-izin', [
            'items'         => $items,
            'mode'          => $this->mode,
            'current_date'  => $current_date,
            'current_month' => $current_month,
            'current_year'  => $current_year,
        ]);
    }

    public function saveStatus()
    {
        // Validasi input modal (standar approval: pending|disetujui|ditolak)
        $data = $this->validate([
            'selectedIzinId' => ['required', 'integer', 'exists:izin,id'],
            'selectedStatus' => ['required', Rule::in(['pending','disetujui','ditolak'])],
        ]);

        $izin = Izin::with('karyawan')->findOrFail($data['selectedIzinId']);

        // Co-admin tidak boleh memproses 'tugas'
        $role = strtolower((string) (Auth::user()->role ?? ''));
        if ($role !== 'admin' && $izin->jenis === 'tugas') {
            Alert::error('Ditolak', 'Hanya admin yang dapat memproses pengajuan tugas.');
            $this->dispatch('close-izin-modal');
            return;
        }

        // Hanya boleh ubah untuk 'izin terlambat' & 'tugas'
        if (!in_array($izin->jenis, ['izin terlambat','tugas'], true)) {
            Alert::error('Ditolak', 'Perubahan status hanya untuk izin terlambat & tugas.');
            $this->dispatch('close-izin-modal');
            return;
        }

        // >>> Guard: Batas waktu edit bulanan (maks tgl 5 bulan berikutnya, end-of-day)
        if (!$this->isWithinMonthlyEditWindow($izin->tanggal_pengajuan)) {
            $cutoff = $this->monthlyEditDeadline($izin->tanggal_pengajuan)->format('Y-m-d');
            Alert::error('Ditolak', "Pengajuan bulan {$izin->tanggal_pengajuan->format('Y-m')} hanya bisa diubah sampai {$cutoff}.");
            $this->dispatch('close-izin-modal');
            return;
        }

        $izin->update(['status' => $data['selectedStatus']]);

        // Recalculate presensi di range izin (opsional)
        try {
            $svc = app(\App\Services\AttendanceService::class);
            $start = Carbon::parse($izin->tanggal_mulai, $this->tz);
            $end   = Carbon::parse($izin->tanggal_selesai, $this->tz);
            $svc->recalculateRange($izin->karyawan_id, $start, $end);
        } catch (\Throwable $e) {
            // optional logging
        }

        Alert::success('Berhasil', 'Status pengajuan izin diperbarui.');
        $this->dispatch('close-izin-modal');

        return redirect()->route('admin.pengajuan-izin', array_filter([
            'mode'  => $this->mode,
            'date'  => $this->date,
            'month' => $this->month,
            'year'  => $this->year,
            'q'     => $this->q ?: null,
        ]));
    }
}
