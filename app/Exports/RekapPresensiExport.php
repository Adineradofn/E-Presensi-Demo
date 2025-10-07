<?php

namespace App\Exports;

use App\Models\Presensi;
use App\Models\Karyawan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class RekapPresensiExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    public function __construct(
        protected string $mode = 'bulan',
        protected ?string $month = null, // YYYY-MM
        protected ?string $year  = null, // YYYY
        protected string $q = '',
        protected string $tz = 'Asia/Makassar',
    ) {}

    protected function resolveRange(): array
    {
        if ($this->mode === 'tahun') {
            $yr    = $this->year ? (int)$this->year : Carbon::now($this->tz)->year;
            $start = Carbon::createFromDate($yr,1,1,$this->tz)->startOfYear();
            $end   = (clone $start)->endOfYear();
        } else {
            $start = $this->month
                ? Carbon::parse($this->month.'-01', $this->tz)->startOfMonth()
                : Carbon::now($this->tz)->startOfMonth();
            $end   = (clone $start)->endOfMonth();
        }
        return [$start, $end];
    }

    public function query(): Builder
    {
        [$start, $end] = $this->resolveRange();

        $agg = Presensi::select([
                'karyawan_id',
                DB::raw("SUM(CASE WHEN status_presensi='hadir'   THEN 1 ELSE 0 END) AS hadir"),
                DB::raw("SUM(CASE WHEN status_presensi='alpa'    THEN 1 ELSE 0 END) AS alpa"),
                DB::raw("SUM(CASE WHEN status_presensi='izin'    THEN 1 ELSE 0 END) AS izin"),
                DB::raw("SUM(CASE WHEN status_presensi='sakit'   THEN 1 ELSE 0 END) AS sakit"),
                DB::raw("SUM(CASE WHEN status_presensi='cuti'    THEN 1 ELSE 0 END) AS cuti"),
                DB::raw("SUM(CASE WHEN status_presensi='invalid' THEN 1 ELSE 0 END) AS invalid"),
                DB::raw("SUM(CASE WHEN status_presensi='hadir'   THEN 1 ELSE 0 END) AS total_kehadiran"),
                DB::raw("
                    SUM(CASE WHEN status_presensi='alpa'    THEN 1 ELSE 0 END) +
                    SUM(CASE WHEN status_presensi='izin'    THEN 1 ELSE 0 END) +
                    SUM(CASE WHEN status_presensi='sakit'   THEN 1 ELSE 0 END) +
                    SUM(CASE WHEN status_presensi='cuti'    THEN 1 ELSE 0 END) +
                    SUM(CASE WHEN status_presensi='invalid' THEN 1 ELSE 0 END)
                AS total_ketidakhadiran"),
            ])
            ->whereBetween('tanggal', [$start->toDateString(), $end->toDateString()])
            ->groupBy('karyawan_id');

        $query = Karyawan::query()
            ->leftJoinSub($agg, 'agg', 'agg.karyawan_id', '=', 'karyawan.id')
            ->select([
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
                DB::raw('COALESCE(agg.total_kehadiran,0) AS total_kehadiran'),
                DB::raw('COALESCE(agg.total_ketidakhadiran,0) AS total_ketidakhadiran'),
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

        return $query->orderBy('karyawan.nama', 'asc');
    }

    public function headings(): array
    {
        return [
            'NIK', 'Nama', 'Jabatan', 'Divisi',
            'Hadir', 'Alpa', 'Izin', 'Sakit', 'Cuti', 'Invalid',
            'Total Kehadiran', 'Total Ketidakhadiran',
        ];
    }

    public function map($row): array
    {
        return [
            $row->nik,
            $row->nama,
            $row->jabatan,
            $row->divisi,
            (int)$row->hadir,
            (int)$row->alpa,
            (int)$row->izin,
            (int)$row->sakit,
            (int)$row->cuti,
            (int)$row->invalid,
            (int)$row->total_kehadiran,
            (int)$row->total_ketidakhadiran,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $lastColumn = Coordinate::stringFromColumnIndex(count($this->headings()));
                $headerRange = "A1:{$lastColumn}1";
                $lastRow = $sheet->getHighestRow();

                // Header style: emerald, white, bold, center
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '10B981'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['argb' => 'FF000000'], // hitam
                        ],
                    ],
                ]);
                $sheet->getRowDimension(1)->setRowHeight(24);

                // Tanpa AutoFilter (tidak ada icon sort di header)
                // $sheet->setAutoFilter($headerRange); // <-- DIHAPUS

                // Freeze header (opsional, boleh dibiarkan)
                $sheet->freezePane('A2');

                // Body center + border hitam untuk semua sel
                if ($lastRow >= 2) {
                    $tableRange = "A1:{$lastColumn}{$lastRow}";
                    $bodyRange  = "A2:{$lastColumn}{$lastRow}";

                    $sheet->getStyle($bodyRange)->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                        ->setVertical(Alignment::VERTICAL_CENTER);

                    $sheet->getStyle($tableRange)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => 'FF000000'], // hitam
                            ],
                        ],
                    ]);
                } else {
                    // Hanya baris header, tetap kasih border
                    $sheet->getStyle($headerRange)->applyFromArray([
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['argb' => 'FF000000'],
                            ],
                        ],
                    ]);
                }

                // Footer info
                $footerRow1 = $lastRow + 2;
                $footerRow2 = $lastRow + 3;

                $sheet->mergeCells("A{$footerRow1}:{$lastColumn}{$footerRow1}");
                $sheet->mergeCells("A{$footerRow2}:{$lastColumn}{$footerRow2}");

                $sheet->setCellValue("A{$footerRow1}", 'Presensi: ' . $this->periodLabel());
                $sheet->setCellValue(
                    "A{$footerRow2}",
                    'Diekspor pada tanggal ' . now($this->tz)->locale('id')->translatedFormat('d F Y H:i') . ' ' . $this->tz
                );

                $sheet->getStyle("A{$footerRow1}:A{$footerRow2}")->applyFromArray([
                    'font' => ['italic' => true, 'size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
                ]);
            },
        ];
    }

    private function periodLabel(): string
    {
        if ($this->mode === 'tahun') {
            $yr = $this->year ?: Carbon::now($this->tz)->format('Y');
            return "Tahun {$yr}";
        }

        $dt = $this->month
            ? Carbon::parse($this->month.'-01', $this->tz)
            : Carbon::now($this->tz);

        return $dt->locale('id')->translatedFormat('F Y');
    }
}
