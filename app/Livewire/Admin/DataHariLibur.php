<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use App\Models\HariLibur;
use Carbon\Carbon;
use RealRashid\SweetAlert\Facades\Alert;

#[Layout('admin.app_admin')]
#[Title('Data Hari Libur')]
class DataHariLibur extends Component
{
    use WithPagination;

    #[Url(as: 'q', except: '')]
    public string $q = '';

    public int $perPage = 10;

    // Mode filter (hanya 'bulan' | 'tahun')
    #[Url(as: 'mode', except: 'bulan')]
    public string $mode = 'bulan';

    // Nilai filter
    #[Url(as: 'month', except: '')]
    public string $month = ''; // format: YYYY-MM

    #[Url(as: 'year', except: '')]
    public string $year = '';  // contoh: 2025

    // State create
    public array $create = [
        'nama_hari'       => '',
        'tanggal_mulai'   => '',
        'tanggal_selesai' => '',
        'keterangan'      => '',
    ];

    // State edit (diset dari UI/Alpine agar instan tanpa delay)
    public array $edit = [
        'id'              => null,
        'nama_hari'       => '',
        'tanggal_mulai'   => '',
        'tanggal_selesai' => '',
        'keterangan'      => '',
    ];

    protected string $tz = 'Asia/Makassar';

    protected $queryString = [
        'q' => ['except' => ''],
    ];

    /**
     * Pesan validasi berbahasa Indonesia
     */
    protected array $messages = [
        // Pesan umum
        'required'       => ':attribute wajib diisi.',
        'string'         => ':attribute harus berupa teks.',
        'max'            => ':attribute maksimal :max karakter.',
        'date'           => ':attribute harus berupa tanggal yang valid.',
        'integer'        => ':attribute harus berupa angka.',
        'exists'         => ':attribute tidak ditemukan.',
        'after_or_equal' => ':attribute harus pada atau setelah :date.',

        // Spesifik field (agar lebih natural)
        'create.tanggal_mulai.after_or_equal' => 'Tanggal mulai minimal besok (:date).',
        'edit.tanggal_mulai.after_or_equal'   => 'Tanggal mulai minimal besok (:date).',

        'create.tanggal_selesai.after_or_equal' => 'Tanggal selesai harus pada atau setelah Tanggal mulai.',
        'edit.tanggal_selesai.after_or_equal'   => 'Tanggal selesai harus pada atau setelah Tanggal mulai.',
    ];

    public function mount(): void
    {
        $this->tz = config('app.timezone', 'Asia/Makassar');

        // Default filter awal
        $now = Carbon::now($this->tz);
        if ($this->mode !== 'bulan' && $this->mode !== 'tahun') {
            $this->mode = 'bulan';
        }
        if ($this->month === '') {
            $this->month = $now->format('Y-m');
        }
        if ($this->year === '') {
            $this->year = (string) $now->year;
        }
    }

    public function updatingQ(): void     { $this->resetPage(); }
    public function updatingMonth(): void { $this->resetPage(); }
    public function updatingYear(): void  { $this->resetPage(); }
    public function updatingMode(): void  { $this->resetPage(); }

    public function setMode(string $mode): void
    {
        if (!in_array($mode, ['bulan', 'tahun'], true)) return;

        $this->mode = $mode;

        // Pastikan nilai filter ada
        $now = Carbon::now($this->tz);
        if ($this->mode === 'bulan' && $this->month === '') {
            $this->month = $now->format('Y-m');
        }
        if ($this->mode === 'tahun' && $this->year === '') {
            $this->year = (string) $now->year;
        }
    }

    public function render()
    {
        $query = HariLibur::query();

        // ====== FILTER PENCARIAN BEBAS (q) ======
        if ($this->q !== '') {
            $q = trim($this->q);
            $query->where(function($w) use ($q) {
                $w->where('nama_hari', 'like', "%{$q}%")
                  ->orWhere('keterangan', 'like', "%{$q}%")
                  ->orWhereRaw('DATE_FORMAT(tanggal_mulai, "%Y-%m-%d") like ?', ["%{$q}%"])
                  ->orWhereRaw('DATE_FORMAT(tanggal_selesai, "%Y-%m-%d") like ?', ["%{$q}%"]);
            });
        }

        // ====== FILTER RENTANG (BULAN / TAHUN) ======
        $tz = $this->tz;
        if ($this->mode === 'bulan') {
            try {
                $start = Carbon::createFromFormat('Y-m', $this->month, $tz)->startOfMonth();
            } catch (\Throwable $e) {
                $start = Carbon::now($tz)->startOfMonth();
                $this->month = $start->format('Y-m');
            }
            $end = (clone $start)->endOfMonth();
        } else { // 'tahun'
            $y = (int) ($this->year ?: Carbon::now($tz)->year);
            $start = Carbon::create($y, 1, 1, 0, 0, 0, $tz)->startOfDay();
            $end   = Carbon::create($y, 12, 31, 23, 59, 59, $tz)->endOfDay();
        }

        // Ambil data yang OVERLAP dengan rentang [start, end]
        $query->whereDate('tanggal_mulai', '<=', $end->toDateString())
              ->whereDate('tanggal_selesai', '>=', $start->toDateString());

        $items = $query->orderBy('tanggal_mulai', 'desc')
            ->paginate($this->perPage)
            ->withQueryString();

        return view('livewire.admin.data-hari-libur', [
            'items'    => $items,
            'tomorrow' => Carbon::now($tz)->addDay()->toDateString(), // untuk attribute min di input date
        ]);
    }

    /** ========== CREATE ========== */
    public function openCreateModal(): void
    {
        $this->resetCreateForm();
    }

    public function resetCreateForm(): void
    {
        $this->create = [
            'nama_hari'       => '',
            'tanggal_mulai'   => '',
            'tanggal_selesai' => '',
            'keterangan'      => '',
        ];
    }

    public function store()
    {
        $tz = $this->tz;
        $tomorrow = Carbon::now($z = $tz)->addDay()->toDateString();

        $data = $this->validate([
            'create.nama_hari'       => ['required','string','max:150'],
            'create.tanggal_mulai'   => ['required','date','after_or_equal:'.$tomorrow],
            'create.tanggal_selesai' => ['required','date','after_or_equal:create.tanggal_mulai'],
            'create.keterangan'      => ['nullable','string','max:1000'],
        ], $this->messages, [
            'create.nama_hari'       => 'Nama hari',
            'create.tanggal_mulai'   => 'Tanggal mulai',
            'create.tanggal_selesai' => 'Tanggal selesai',
            'create.keterangan'      => 'Keterangan',
        ]);

        HariLibur::create([
            'nama_hari'       => $data['create']['nama_hari'],
            'tanggal_mulai'   => $data['create']['tanggal_mulai'],
            'tanggal_selesai' => $data['create']['tanggal_selesai'],
            'keterangan'      => $data['create']['keterangan'] ?: null,
        ]);

        Alert::success('Berhasil', 'Hari libur berhasil ditambahkan.');
        // modal create pakai partial lama (event nama lama)
        $this->dispatch('modal-create-close');
        $this->resetCreateForm();
        $this->resetPage();
    }

    /** ========== EDIT ========== */
    // Disediakan jika ingin buka modal via event server (opsional).
    public function openEditModal(int $id): void
    {
        $libur = HariLibur::findOrFail($id);
        $this->edit = [
            'id'              => $libur->id,
            'nama_hari'       => $libur->nama_hari,
            'tanggal_mulai'   => optional($libur->tanggal_mulai)->toDateString(),
            'tanggal_selesai' => optional($libur->tanggal_selesai)->toDateString(),
            'keterangan'      => (string) ($libur->keterangan ?? ''),
        ];

        // Event browser (jika ingin trigger dari server)
        $this->dispatch('open-libur-modal', [
            'targetId'        => 'modalEditLibur',
            'id'              => $this->edit['id'],
            'nama_hari'       => $this->edit['nama_hari'],
            'tanggal_mulai'   => $this->edit['tanggal_mulai'],
            'tanggal_selesai' => $this->edit['tanggal_selesai'],
            'keterangan'      => $this->edit['keterangan'],
        ]);
    }

    public function resetEditForm(): void
    {
        $this->edit = [
            'id'              => null,
            'nama_hari'       => '',
            'tanggal_mulai'   => '',
            'tanggal_selesai' => '',
            'keterangan'      => '',
        ];
    }

    public function update()
    {
        $tz = $this->tz;
        $tomorrow = Carbon::now($tz)->addDay()->toDateString();

        $data = $this->validate([
            'edit.id'              => ['required','integer','exists:hari_libur,id'],
            'edit.nama_hari'       => ['required','string','max:150'],
            'edit.tanggal_mulai'   => ['required','date','after_or_equal:'.$tomorrow],
            'edit.tanggal_selesai' => ['required','date','after_or_equal:edit.tanggal_mulai'],
            'edit.keterangan'      => ['nullable','string','max:1000'],
        ], $this->messages, [
            'edit.nama_hari'       => 'Nama hari',
            'edit.tanggal_mulai'   => 'Tanggal mulai',
            'edit.tanggal_selesai' => 'Tanggal selesai',
            'edit.keterangan'      => 'Keterangan',
        ]);

        $libur = HariLibur::findOrFail($data['edit']['id']);
        $libur->update([
            'nama_hari'       => $data['edit']['nama_hari'],
            'tanggal_mulai'   => $data['edit']['tanggal_mulai'],
            'tanggal_selesai' => $data['edit']['tanggal_selesai'],
            'keterangan'      => $data['edit']['keterangan'] ?: null,
        ]);

        Alert::success('Berhasil', 'Hari libur berhasil diperbarui.');
        $this->dispatch('close-libur-modal');
        $this->resetEditForm();
    }

    /** ========== DELETE ========== */
    public function destroy(int $id)
    {
        $libur = HariLibur::findOrFail($id);
        $libur->delete();

        Alert::success('Terhapus', 'Hari libur berhasil dihapus.');
        // biarkan pagination menyesuaikan sendiri
    }
}
