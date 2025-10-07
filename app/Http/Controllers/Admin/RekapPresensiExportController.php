<?php

namespace App\Http\Controllers\Admin;

use App\Exports\RekapPresensiExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Maatwebsite\Excel\Facades\Excel;

class RekapPresensiExportController
{
    public function __invoke(Request $request)
    {
        $mode  = $request->string('mode')->lower()->value() ?: 'bulan'; // 'bulan' | 'tahun'
        $month = $request->string('month')->value(); // YYYY-MM
        $year  = $request->string('year')->value();  // YYYY
        $q     = $request->string('q')->value() ?? '';

        $tz = Config::get('app.timezone', 'Asia/Makassar');

        $export = new RekapPresensiExport(
            mode: $mode,
            month: $month,
            year: $year,
            q: $q,
            tz: $tz
        );

        $filename = 'rekap-presensi-' . now($tz)->format('Ymd_His') . '.xlsx';
        return Excel::download($export, $filename);
    }
}
