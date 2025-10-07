<?php

namespace App\Support;

use App\Models\Jadwal;
use Carbon\Carbon;

class AttendanceWindow
{
    /**
     ///// Hitung ketersediaan absen (masuk/pulang) untuk HARI INI.
     ///// Konsep:
     ///// - Check-in dibuka X menit sebelum jam_masuk (default 60; field: masuk_buka_sebelum)
     ///// - Check-in TUTUP tepat saat check-out DIBUKA
     ///// - Check-out dibuka pada jam_pulang atau X menit sebelum (field: pulang_buka_sebelum)
     ///// - Check-out DITUTUP tetap jam 23:00 waktu lokal
     ///// Catatan:
     ///// - Menggunakan timezone aplikasi (default Asia/Makassar)
     ///// - Belum meng-handle shift lintas hari (jam_pulang < jam_masuk → butuh penyesuaian)
     */
    public static function todayAvailability(int $idKaryawan): array
    {
        ///// Tetapkan timezone & waktu sekarang
        $tz   = 'Asia/Makassar';
        $now  = Carbon::now($tz);
        $date = $now->toDateString();

        ///// Ambil jadwal + relasi jamKerja untuk karyawan pada tanggal HARI INI
        $jadwal = Jadwal::with('jamKerja')
            ->where('id_karyawan', $idKaryawan)
            ->whereDate('tanggal', $date)
            ->first();

        ///// Jika tidak ada jadwal, jam kerja tidak terpasang, atau hari libur → tidak bisa check-in/out
        if (!$jadwal || !$jadwal->jamKerja || $jadwal->libur) {
            return [
                'checkin'  => false,
                'checkout' => false,
                'meta'     => [],
            ];
        }

        $jk = $jadwal->jamKerja;

        ///// Tentukan jam_masuk & jam_pulang untuk HARI INI
        ///// setDateFrom($now) menyalin tanggal dari $now, menjaga jam HH:MM:SS dari field DB
        $jamMasuk  = Carbon::parse($jk->jam_masuk,  $tz)->setDateFrom($now);
        $jamPulang = Carbon::parse($jk->jam_pulang, $tz)->setDateFrom($now);

        ///// Ambil parameter pembuka-pintu (dalam menit); gunakan default jika null
        $masukBukaSebelum  = (int)($jk->masuk_buka_sebelum  ?? 60); ///// default 60 menit sebelum masuk
        $pulangBukaSebelum = (int)($jk->pulang_buka_sebelum ?? 0);  ///// default 0 → tepat jam_pulang

        ///// Hitung window check-in & check-out:
        ///// - Check-in dibuka pada (jamMasuk - masukBukaSebelum)
        ///// - Check-out dibuka pada (jamPulang - pulangBukaSebelum)
        ///// - Check-in ditutup PERSIS saat check-out dibuka
        $checkinOpen   = $jamMasuk->copy()->subMinutes(max($masukBukaSebelum, 0));
        $checkoutOpen  = $jamPulang->copy()->subMinutes(max($pulangBukaSebelum, 0));
        $checkinClose  = $checkoutOpen->copy(); ///// penutupan check-in disamakan dengan pembukaan check-out

        ///// Check-out tutup fix pukul 23:00 pada hari yang sama (lokal)
        $checkoutClose = Carbon::parse('23:00:00', $tz)->setDateFrom($now);

        ///// Aturan enable:
        ///// - check-in aktif pada interval [checkinOpen, checkinClose)  → pakai < untuk tutup tepat waktu
        ///// - check-out aktif pada interval [checkoutOpen, checkoutClose] → pakai <= agar menit terakhir valid
        $checkin  = $now->gte($checkinOpen)  && $now->lt($checkinClose);
        $checkout = $now->gte($checkoutOpen) && $now->lte($checkoutClose);

        ///// Kembalikan status & meta (timestamps Carbon untuk keperluan UI/logging)
        return [
            'checkin'  => $checkin,
            'checkout' => $checkout,
            'meta'     => [
                'id_jadwal'     => $jadwal->id_jadwal,
                'jam_masuk'     => $jamMasuk,
                'jam_pulang'    => $jamPulang,
                'checkinOpen'   => $checkinOpen,
                'checkinClose'  => $checkinClose,
                'checkoutOpen'  => $checkoutOpen,
                'checkoutClose' => $checkoutClose,
            ],
        ];
    }
}
