<?php

namespace App\Http\Middleware;

use App\Models\SkinAnalysis;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class EncryptScanImages
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->hasFile('image')) {
            $file = $request->file('image');

            if ($file && $file->isValid()) {
                try {
                    $rawContent = file_get_contents($file->getRealPath());

                    $request->merge([
                        '_encrypted_image_content' => Crypt::encryptString($rawContent),
                        '_encrypted_image_mime' => $file->getMimeType(),
                        '_encrypted_image_original_name' => $file->getClientOriginalName(),
                    ]);

                    Log::debug('Scan image encrypted via middleware', [
                        'original_name' => $file->getClientOriginalName(),
                        'size' => $file->getSize(),
                        'mime' => $file->getMimeType(),
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to encrypt scan image in middleware', [
                        'error' => $e->getMessage(),
                        'file' => $file->getClientOriginalName(),
                    ]);

                    return response()->json([
                        'message' => 'فشل تشفير الصورة، يرجى المحاولة مرة أخرى.',
                    ], Response::HTTP_INTERNAL_SERVER_ERROR);
                }
            }
        }

        return $next($request);
    }

    public static function encryptFile(string $path): string
    {
        if (!file_exists($path)) {
            throw new \RuntimeException("File not found for encryption: {$path}");
        }
        $contents = file_get_contents($path);
        $encrypted = encrypt($contents);
        $encryptedPath = $path . '.encrypted';
        file_put_contents($encryptedPath, $encrypted);
        return $encryptedPath;
    }

    public static function decryptFile(string $path): ?string
    {
        if (!file_exists($path)) {
            return null;
        }
        $encrypted = file_get_contents($path);
        return decrypt($encrypted);
    }

    public static function decryptScanImage(SkinAnalysis $scan): ?string
    {
        try {
            $disk = Storage::disk(config('skinanalyzer.scan_disk', 'local'));

            $path = $scan->image_path;

            if (! str_ends_with($path, '.enc')) {
                return $disk->get($path);
            }

            if (! $disk->exists($path)) {
                Log::warning('Encrypted scan file not found', [
                    'scan_id' => $scan->id,
                    'path' => $path,
                ]);
                return null;
            }

            $encrypted = $disk->get($path);
            return Crypt::decryptString($encrypted);
        } catch (\Exception $e) {
            Log::error('Failed to decrypt scan image', [
                'scan_id' => $scan->id,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }
}
