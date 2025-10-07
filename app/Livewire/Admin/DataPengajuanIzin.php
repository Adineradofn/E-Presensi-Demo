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

#[Layout('admin.app_admin')]
#[Title('Pengajuan Izin')]
class DataPengajuanIzin extends Component
{
    use WithPagination;

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
    ];

    public function mount(): void
    {
        $this->tz = config('app.timezone', 'Asia/Makassar');
        $this->ensureDefaultsForMode();
    }

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

        // ⛏️ FIX: gunakan karyawan_id (bukan id_karyawan) & standar PK id
        $items = $query
            ->orderBy('tanggal_pengajuan', 'desc')
            ->orderBy('karyawan_id', 'asc')
            ->paginate(15)
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

        // Hanya boleh ubah untuk 'izin terlambat' & 'tugas' (sesuai spesifikasi)
        if (!in_array($izin->jenis, ['izin terlambat','tugas'], true)) {
            Alert::error('Ditolak', 'Perubahan status hanya untuk izin terlambat & tugas.');
            $this->dispatch('close-izin-modal');
            return;
        }

        $izin->update(['status' => $data['selectedStatus']]);

        // Recalculate presensi di range izin (opsional, tapi sesuai desain final)
        try {
            $svc = app(\App\Services\AttendanceService::class);
            $start = Carbon::parse($izin->tanggal_mulai, $this->tz);
            $end   = Carbon::parse($izin->tanggal_selesai, $this->tz);
            $svc->recalculateRange($izin->karyawan_id, $start, $end);
        } catch (\Throwable $e) {
            // Jangan gagal UI, cukup lanjut (bisa dilog jika diinginkan)
        }

        Alert::success('Berhasil', 'Status pengajuan izin diperbarui.');
        $this->dispatch('close-izin-modal');

        // Tetap di halaman dengan filter aktif
        return redirect()->route('admin.pengajuan-izin', array_filter([
            'mode'  => $this->mode,
            'date'  => $this->date,
            'month' => $this->month,
            'year'  => $this->year,
            'q'     => $this->q ?: null,
        ]));
    }
}
