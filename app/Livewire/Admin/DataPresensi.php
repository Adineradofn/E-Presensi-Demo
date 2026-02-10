<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use App\Models\Presensi;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

/**
 * Komponen Admin: Data Presensi
 * - Hapus fitur edit status
 * - Tampilkan data presensi sesuai filter
 * - Eager load 'izin' untuk label "Hadir (Tugas)"
 */
#[Layout('admin.app_admin')]
#[Title('Data Presensi')]
class DataPresensi extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    #[Url(as: 'mode', except: 'hari')]
    public string $mode = 'hari';

    #[Url(as: 'date')]
    public ?string $date = null;

    #[Url(as: 'month')]
    public ?string $month = null;

    #[Url(as: 'year')]
    public ?string $year = null;

    #[Url(as: 'q', except: '')]
    public string $q = '';

    #[Url(as: 'perPage', except: 10)]
    public int $perPage = 10;

    public array $perPageOptions = [10, 25, 50];

    protected string $tz = 'Asia/Makassar';

    public function mount()
    {
        $this->tz = config('app.timezone', 'Asia/Makassar');
        $this->ensureDefaultsForMode();
    }

    public function setMode(string $mode)
    {
        $this->mode = in_array($mode, ['hari','bulan','tahun']) ? $mode : 'hari';
        $this->ensureDefaultsForMode();
        $this->resetPage();
    }

    public function updatingQ()     { $this->resetPage(); }
    public function updatingDate()  { $this->resetPage(); }
    public function updatingMonth() { $this->resetPage(); }
    public function updatingYear()  { $this->resetPage(); }
    public function updatingPerPage() { $this->resetPage(); }

    public function updatedPerPage($value)
    {
        $value = (int)$value;
        if (!in_array($value, $this->perPageOptions, true)) {
            $this->perPage = $this->perPageOptions[0];
        }
    }

    private function ensureDefaultsForMode(): void
    {
        $now = Carbon::now($this->tz);

        if ($this->mode === 'bulan') {
            $this->month ??= $now->format('Y-m');
            $this->date = null; $this->year = null;
        } elseif ($this->mode === 'tahun') {
            $this->year ??= $now->format('Y');
            $this->date = null; $this->month = null;
        } else {
            $this->date ??= $now->toDateString();
            $this->month = null; $this->year = null;
        }
    }

    private function resolveRange(): array
    {
        if ($this->mode === 'bulan') {
            $start = $this->month
                ? Carbon::parse($this->month . '-01', $this->tz)->startOfMonth()
                : Carbon::now($this->tz)->startOfMonth();
            $end   = (clone $start)->endOfMonth();
        } elseif ($this->mode === 'tahun') {
            $yr    = $this->year ? (int) $this->year : Carbon::now($this->tz)->year;
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

        // ⬇️ Penting: eager-load 'karyawan' + 'izin' agar accessor tidak N+1
        $query = Presensi::with(['karyawan','izin'])
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()]);

        if ($this->q !== '') {
            $q = $this->q;
            $query->where(function ($w) use ($q) {
                $w->whereHas('karyawan', function ($k) use ($q) {
                    $k->where('nama', 'like', "%{$q}%")
                        ->orWhere('nik', 'like', "%{$q}%")
                        ->orWhere('divisi', 'like', "%{$q}%")
                        ->orWhere('jabatan', 'like', "%{$q}%");
                })
                ->orWhere('status_presensi', 'like', "%{$q}%")
                ->orWhere('tanggal', 'like', "%{$q}%");
            });
        }

        $items = $query
            ->orderBy('tanggal', 'desc')
            ->orderBy('karyawan_id', 'asc')
            ->paginate($this->perPage)
            ->withQueryString();

        $current_date  = $this->mode === 'hari'  ? $start->toDateString() : null;
        $current_month = $this->mode === 'bulan' ? $start->format('Y-m') : null;
        $current_year  = $this->mode === 'tahun' ? $start->format('Y')   : null;

        return view('livewire.admin.data-presensi', [
            'items'         => $items,
            'mode'          => $this->mode,
            'current_date'  => $current_date,
            'current_month' => $current_month,
            'current_year'  => $current_year,
        ]);
    }
}
