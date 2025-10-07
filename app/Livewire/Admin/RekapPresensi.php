<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\Presensi;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Rekap Presensi (Admin)
 * - Mode Bulan & Tahun
 * - Search: NIK, Nama, Jabatan, Divisi
 * - Agregasi count per status + Total Kehadiran + Total Ketidakhadiran
 */
#[Layout('admin.app_admin')]
#[Title('Rekap Presensi')]
class RekapPresensi extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    #[Url(as: 'mode', except: 'bulan')]
    public string $mode = 'bulan'; // bulan|tahun

    #[Url(as: 'month')]
    public ?string $month = null; // YYYY-MM

    #[Url(as: 'year')]
    public ?string $year = null; // YYYY

    #[Url(as: 'q', except: '')]
    public string $q = '';

    #[Url(as: 'perPage', except: 10)]
    public int $perPage = 10;

    public array $perPageOptions = [10, 25, 50];

    protected string $tz = 'Asia/Makassar';

    public function mount()
    {
        $this->tz = config('app.timezone', 'Asia/Makassar');
        $this->ensureDefaults();
    }

    private function ensureDefaults(): void
    {
        $now = Carbon::now($this->tz);
        if ($this->mode === 'tahun') {
            $this->year ??= $now->format('Y');
            $this->month = null;
        } else {
            $this->month ??= $now->format('Y-m');
            $this->year = null;
        }
    }

    public function setMode(string $mode)
    {
        $this->mode = in_array($mode, ['bulan','tahun']) ? $mode : 'bulan';
        $this->ensureDefaults();
        $this->resetPage();
    }

    private function resolveRange(): array
    {
        if ($this->mode === 'tahun') {
            $yr    = $this->year ? (int)$this->year : Carbon::now($this->tz)->year;
            $start = Carbon::createFromDate($yr, 1, 1, $this->tz)->startOfYear();
            $end   = (clone $start)->endOfYear();
        } else {
            $start = $this->month
                ? Carbon::parse($this->month . '-01', $this->tz)->startOfMonth()
                : Carbon::now($this->tz)->startOfMonth();
            $end   = (clone $start)->endOfMonth();
        }
        return [$start, $end];
    }

    public function render()
    {
        [$start, $end] = $this->resolveRange();

        // Subquery aggregate per karyawan di periode
        $agg = Presensi::select([
                'karyawan_id',
                DB::raw("SUM(CASE WHEN status_presensi = 'hadir' THEN 1 ELSE 0 END) AS hadir"),
                DB::raw("SUM(CASE WHEN status_presensi = 'alpa' THEN 1 ELSE 0 END) AS alpa"),
                DB::raw("SUM(CASE WHEN status_presensi = 'izin' THEN 1 ELSE 0 END) AS izin"),
                DB::raw("SUM(CASE WHEN status_presensi = 'sakit' THEN 1 ELSE 0 END) AS sakit"),
                DB::raw("SUM(CASE WHEN status_presensi = 'cuti' THEN 1 ELSE 0 END) AS cuti"),
                DB::raw("SUM(CASE WHEN status_presensi = 'invalid' THEN 1 ELSE 0 END) AS invalid"),
                DB::raw("SUM(CASE WHEN status_presensi = 'hadir' THEN 1 ELSE 0 END) AS total_kehadiran"),
                DB::raw("SUM(CASE WHEN status_presensi IN ('alpa','izin','sakit','cuti','invalid') THEN 1 ELSE 0 END) AS total_ketidakhadiran"),
            ])
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->groupBy('karyawan_id');

        // Join dengan karyawan untuk search & display
        $query = Karyawan::query()
            ->leftJoinSub($agg, 'agg', 'agg.karyawan_id', '=', 'karyawan.id')
            ->select([
                'karyawan.id',
                'karyawan.nik',
                'karyawan.nama',
                'karyawan.jabatan',
                'karyawan.divisi',
                DB::raw('COALESCE(agg.hadir,0) AS hadir'),
                DB::raw('COALESCE(agg.alpa,0) AS alpa'),
                DB::raw('COALESCE(agg.izin,0) AS izin'),
                DB::raw('COALESCE(agg.sakit,0) AS sakit'),
                DB::raw('COALESCE(agg.cuti,0) AS cuti'),
                DB::raw('COALESCE(agg.invalid,0) AS invalid'),
                DB::raw('COALESCE(agg.total_ketidakhadiran,0) AS total_ketidakhadiran'),
                DB::raw('COALESCE(agg.total_kehadiran,0) AS total_kehadiran'),
            ]);

        if ($this->q !== '') {
            $q = $this->q;
            $query->where(function ($w) use ($q) {
                $w->where('karyawan.nik', 'like', "%{$q}%")
                  ->orWhere('karyawan.nama', 'like', "%{$q}%")
                  ->orWhere('karyawan.jabatan', 'like', "%{$q}%")
                  ->orWhere('karyawan.divisi', 'like', "%{$q}%");
            });
        }

        $items = $query->orderBy('karyawan.nama', 'asc')->paginate($this->perPage)->withQueryString();

        $current_month = $this->mode === 'bulan' ? $start->format('Y-m') : null;
        $current_year  = $this->mode === 'tahun' ? $start->format('Y')   : null;

        return view('livewire.admin.rekap-presensi', [
            'items'         => $items,
            'mode'          => $this->mode,
            'current_month' => $current_month,
            'current_year'  => $current_year,
        ]);
    }
}
