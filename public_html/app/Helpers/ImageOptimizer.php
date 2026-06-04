<?php

namespace App\Helpers;

use Illuminate\Support\Facades\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Intervention\Image\Facades\Image;

class ImageOptimizer
{
    private const MAX_WIDTH = 1920;
    private const MAX_HEIGHT = 1080;
    private const THUMBNAIL_WIDTH = 300;
    private const THUMBNAIL_HEIGHT = 300;
    private const WEBP_QUALITY = 85;
    private const JPEG_QUALITY = 85;

    public function uploadAndOptimize(UploadedFile $file, string $directory = 'products'): array
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $sanitizedName = $this->sanitizeFileName($originalName);
        $extension = strtolower($file->getClientOriginalExtension());

        $fileName = $sanitizedName . '-' . Str::random(8) . '.' . $extension;
        $webpFileName = $sanitizedName . '-' . Str::random(8) . '.webp';
        $thumbnailFileName = $sanitizedName . '-thumb-' . Str::random(8) . '.webp';

        $uploadPath = public_path("storage/{$directory}");
        $this->ensureDirectoryExists($uploadPath);

        $fullPath = "{$uploadPath}/{$fileName}";
        $webpPath = "{$uploadPath}/{$webpFileName}";
        $thumbnailPath = "{$uploadPath}/thumbnails/{$thumbnailFileName}";

        $image = Image::make($file->getRealPath());

        if ($image->width() > self::MAX_WIDTH || $image->height() > self::MAX_HEIGHT) {
            $image->resize(self::MAX_WIDTH, self::MAX_HEIGHT, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }

        $image->save($fullPath, self::JPEG_QUALITY);

        $image->resize(self::THUMBNAIL_WIDTH, self::THUMBNAIL_HEIGHT, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });

        $this->ensureDirectoryExists("{$uploadPath}/thumbnails");
        $image->save($thumbnailPath, self::WEBP_QUALITY, 'webp');

        $webpImage = Image::make($file->getRealPath());
        $webpImage->resize(self::MAX_WIDTH, self::MAX_HEIGHT, function ($constraint) {
            $constraint->aspectRatio();
            $constraint->upsize();
        });
        $webpImage->save($webpPath, self::WEBP_QUALITY, 'webp');

        return [
            'original' => "{$directory}/{$fileName}",
            'webp' => "{$directory}/{$webpFileName}",
            'thumbnail' => "{$directory}/thumbnails/{$thumbnailFileName}",
            'width' => $image->width(),
            'height' => $image->height(),
            'size' => File::size($fullPath),
        ];
    }

    public function deleteImages(?string $original, ?string $webp = null, ?string $thumbnail = null): void
    {
        $paths = array_filter([$original, $webp, $thumbnail]);

        foreach ($paths as $path) {
            $fullPath = public_path("storage/{$path}");
            if (File::exists($fullPath)) {
                File::delete($fullPath);
            }
        }
    }

    public function generatePlaceholder(string $text, int $width = 400, int $height = 400): string
    {
        $image = Image::canvas($width, $height, '#f0f0f0');
        $image->text($text, $width / 2, $height / 2, function ($font) {
            $font->size(24);
            $font->color('#999999');
            $font->align('center');
            $font->valign('center');
        });

        $fileName = 'placeholder-' . Str::random(8) . '.png';
        $path = public_path("storage/placeholder/{$fileName}");
        $this->ensureDirectoryExists(dirname($path));
        $image->save($path);

        return "placeholder/{$fileName}";
    }

    public function convertToWebP(UploadedFile $file, string $directory = 'products'): ?string
    {
        try {
            $image = Image::make($file->getRealPath());
            $sanitizedName = $this->sanitizeFileName(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
            $fileName = $sanitizedName . '-' . Str::random(8) . '.webp';
            $path = public_path("storage/{$directory}/{$fileName}");

            $this->ensureDirectoryExists(dirname($path));

            $image->save($path, self::WEBP_QUALITY, 'webp');

            return "{$directory}/{$fileName}";

        } catch (\Exception $e) {
            return null;
        }
    }

    public function resizeAndSave(UploadedFile $file, string $path, int $width, int $height): bool
    {
        try {
            $image = Image::make($file->getRealPath());
            $image->fit($width, $height, function ($constraint) {
                $constraint->upsize();
            });
            $image->save(public_path("storage/{$path}"), self::JPEG_QUALITY);

            return true;

        } catch (\Exception $e) {
            return false;
        }
    }

    private function sanitizeFileName(string $name): string
    {
        $name = preg_replace('/[^\p{L\p{N}\s\-\_]/u', '', $name);
        $name = preg_replace('/[\s\_]+/', '-', $name);
        $name = trim($name, '-');

        return Str::limit($name, 50, '');
    }

    private function ensureDirectoryExists(string $path): void
    {
        if (!File::exists($path)) {
            File::makeDirectory($path, 0755, true);
        }
    }

    public function getImageDimensions(string $path): ?array
    {
        try {
            $image = Image::make(public_path("storage/{$path}"));
            return [
                'width' => $image->width(),
                'height' => $image->height(),
            ];
        } catch (\Exception $e) {
            return null;
        }
    }
}
