<?php

namespace App\Livewire\Admin;

use Livewire\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Url;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Illuminate\Validation\Rule;
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

    // State create
    public array $create = [
        'nama_hari'       => '',
        'tanggal_mulai'   => '',
        'tanggal_selesai' => '',
        'keterangan'      => '',
    ];

    // State edit
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

    public function mount(): void
    {
        $this->tz = config('app.timezone', 'Asia/Makassar');
    }

    public function updatingQ(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = HariLibur::query();

        if ($this->q !== '') {
            $q = trim($this->q);
            $query->where(function($w) use ($q) {
                $w->where('nama_hari', 'like', "%{$q}%")
                  ->orWhere('keterangan', 'like', "%{$q}%")
                  ->orWhereRaw('DATE_FORMAT(tanggal_mulai, "%Y-%m-%d") like ?', ["%{$q}%"])
                  ->orWhereRaw('DATE_FORMAT(tanggal_selesai, "%Y-%m-%d") like ?', ["%{$q}%"]);
            });
        }

        $items = $query->orderBy('tanggal_mulai', 'desc')
            ->paginate($this->perPage)
            ->withQueryString();

        return view('livewire.admin.data-hari-libur', [
            'items' => $items,
            'tomorrow' => Carbon::now($this->tz)->addDay()->toDateString(), // untuk attribute min di input date
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
        $tomorrow = Carbon::now($tz)->addDay()->toDateString();

        $data = $this->validate([
            'create.nama_hari'       => ['required','string','max:150'],
            // ⛏️ H+1: minimal besok
            'create.tanggal_mulai'   => ['required','date','after_or_equal:'.$tomorrow],
            'create.tanggal_selesai' => ['required','date','after_or_equal:create.tanggal_mulai'],
            'create.keterangan'      => ['nullable','string','max:1000'],
        ], [], [
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
        $this->dispatch('modal-create-close');
        $this->resetCreateForm();
        $this->resetPage();
    }

    /** ========== EDIT ========== */
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

        // kirim payload ke modal edit (untuk mengisi judul dsb kalau perlu)
        $this->dispatch('open-edit', $this->edit);
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
            // tetap pakai H+1 sesuai permintaan (boleh dihapus jika ingin mengizinkan edit ke hari ini)
            'edit.tanggal_mulai'   => ['required','date','after_or_equal:'.$tomorrow],
            'edit.tanggal_selesai' => ['required','date','after_or_equal:edit.tanggal_mulai'],
            'edit.keterangan'      => ['nullable','string','max:1000'],
        ], [], [
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
        $this->dispatch('modal-edit-close');
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
