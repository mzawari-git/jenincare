<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Response;
use Intervention\Image\Facades\Image;

class OptimizedImageController extends Controller
{
    public function show(Request $request, $path)
    {
        try {
            $fullPath = $this->resolvePath($path);
            if (!$fullPath || !File::exists($fullPath)) {
                abort(404);
            }

            $width = $request->get('w');
            $height = $request->get('h');
            $quality = min(100, max(10, (int) $request->get('q', 80)));
            $webp = $request->get('webp');

            $cacheKey = 'img_' . md5($path . $width . $height . $quality . ($webp ? 'wp' : ''));
            $cacheDir = storage_path('app/public/cache/optimized');
            if (!File::isDirectory($cacheDir)) {
                File::makeDirectory($cacheDir, 0755, true);
            }

            $ext = $webp ? 'webp' : strtolower(pathinfo($path, PATHINFO_EXTENSION));
            $cachePath = $cacheDir . '/' . $cacheKey . '.' . $ext;

            if (File::exists($cachePath)) {
                return $this->serveFile($cachePath, $ext);
            }

            $img = Image::make($fullPath);

            if ($width || $height) {
                $img->resize($width, $height, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });
            }

            if ($webp) {
                $img->encode('webp', $quality);
            } else {
                $img->encode($img->extension() ?: 'jpg', $quality);
            }

            $img->save($cachePath, $quality);

            return $this->serveFile($cachePath, $ext);

        } catch (\Exception $e) {
            abort(404);
        }
    }

    public function thumbnail(Request $request, $path)
    {
        try {
            $fullPath = $this->resolvePath($path);
            if (!$fullPath || !File::exists($fullPath)) {
                abort(404);
            }

            $cacheKey = 'thumb_' . md5($path);
            $cacheDir = storage_path('app/public/cache/optimized');
            if (!File::isDirectory($cacheDir)) {
                File::makeDirectory($cacheDir, 0755, true);
            }

            $cachePath = $cacheDir . '/' . $cacheKey . '.webp';

            if (File::exists($cachePath)) {
                return $this->serveFile($cachePath, 'webp');
            }

            $img = Image::make($fullPath);
            $img->resize(300, 300, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
            $img->encode('webp', 75);
            $img->save($cachePath, 75);

            return $this->serveFile($cachePath, 'webp');

        } catch (\Exception $e) {
            abort(404);
        }
    }

    private function resolvePath(string $path): ?string
    {
        $storagePath = storage_path('app/public/' . $path);
        if (File::exists($storagePath)) {
            return $storagePath;
        }
        $publicPath = public_path('storage/' . $path);
        if (File::exists($publicPath)) {
            return $publicPath;
        }
        return null;
    }

    private function serveFile(string $filePath, string $ext): \Illuminate\Http\Response
    {
        $mime = match ($ext) {
            'webp' => 'image/webp',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            default => 'image/jpeg',
        };

        return Response::make(File::get($filePath))
            ->header('Content-Type', $mime)
            ->header('Cache-Control', 'public, max-age=31536000, immutable')
            ->header('Content-Length', File::size($filePath));
    }
}
