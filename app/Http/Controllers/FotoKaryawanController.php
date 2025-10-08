<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class FotoKaryawanController extends Controller
{
    /** ADMIN: boleh lihat foto siapa saja. */
    public function photoAdmin(Karyawan $karyawan)
    {
        $user = Auth::user();
        if (!$user) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        $isAdmin =
            (method_exists($user, 'hasRole') && $user->hasRole('admin')) ||
            (($user->role ?? null) === 'admin') ||
            ((optional($user->karyawan)->role ?? null) === 'admin');

        if (!$isAdmin) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $this->streamPhotoOr404($karyawan);
    }

    /** SELF: hanya pemilik (user login) yang boleh akses. */
    public function photoSelf(Karyawan $karyawan)
    {
        $user = Auth::user();
        if (!$user) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        // gunakan id_karyawan jika ada, fallback ke id user
        $ownId = $user->id_karyawan ?? $user->id;

        if ((int) $ownId !== (int) $karyawan->getKey()) {
            abort(Response::HTTP_FORBIDDEN);
        }

        return $this->streamPhotoOr404($karyawan);
    }

    private function streamPhotoOr404(Karyawan $karyawan)
    {
        if (!$karyawan->foto || !Storage::disk('local')->exists($karyawan->foto)) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $path = Storage::disk('local')->path($karyawan->foto);

        return response()->file($path, [
            'Content-Type'        => @mime_content_type($path) ?: 'application/octet-stream',
            'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
            'Cache-Control'       => 'private, no-store, max-age=0',
        ]);
    }
}
