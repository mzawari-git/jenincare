<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppUpdateController extends Controller
{
    public function check(): JsonResponse
    {
        $apkPath = public_path('app-update.apk');
        $exists = file_exists($apkPath);

        return response()->json([
            'latest_version' => $exists ? (config('app.update_version', '1.0.0')) : null,
            'version_code' => $exists ? (config('app.update_version_code', 1)) : null,
            'download_url' => $exists ? url('/api/v1/app-update/download') : null,
            'release_notes' => $exists ? (config('app.update_release_notes', '')) : null,
            'force_update' => $exists ? (config('app.update_force', false)) : false,
            'update_available' => $exists,
        ]);
    }

    public function download(Request $request)
    {
        $apkPath = public_path('app-update.apk');

        if (!file_exists($apkPath)) {
            abort(404, 'APK not found');
        }

        return response()->file($apkPath, [
            'Content-Type' => 'application/vnd.android.package-archive',
            'Content-Disposition' => 'attachment; filename="JeninCare-SkinAnalyzer.apk"',
        ]);
    }
}
