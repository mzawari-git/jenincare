<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ServeStorageController extends Controller
{
    public function show(string $path): BinaryFileResponse
    {
        $filePath = public_path('storage/' . $path);

        if (!file_exists($filePath) || !is_file($filePath)) {
            $fallbackPath = storage_path('app/public/' . $path);
            if (file_exists($fallbackPath) && is_file($fallbackPath)) {
                $filePath = $fallbackPath;
            } else {
                abort(404);
            }
        }

        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            'svg' => 'image/svg+xml',
        ];

        $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $mimeType = $mimeTypes[$ext] ?? 'application/octet-stream';

        return response()->file($filePath, [
            'Content-Type' => $mimeType,
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }
}
