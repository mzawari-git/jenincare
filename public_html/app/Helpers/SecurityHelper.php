<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class SecurityHelper
{
    public static function hashPassword(string $password): string
    {
        return Hash::make($password);
    }

    public static function verifyPassword(string $password, string $hash): bool
    {
        return Hash::check($password, $hash);
    }

    public static function generateToken(int $length = 32): string
    {
        return Str::random($length);
    }

    public static function generateApiKey(): string
    {
        return 'sk_' . Str::random(40);
    }

    public static function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        $name = $parts[0];
        $domain = $parts[1] ?? '';

        $maskedName = substr($name, 0, 2) . '***' . substr($name, -2);

        return $maskedName . '@' . $domain;
    }

    public static function maskPhone(string $phone): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        if (strlen($cleaned) < 4) {
            return '***';
        }

        return '***' . substr($cleaned, -4);
    }

    public static function sanitizeInput(string $input): string
    {
        return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
    }

    public static function sanitizeHtml(array $allowedTags = []): callable
    {
        return function ($value) use ($allowedTags) {
            if (is_string($value)) {
                $stripped = strip_tags($value, '<' . implode('><', $allowedTags) . '>');
                return htmlspecialchars($stripped, ENT_QUOTES, 'UTF-8');
            }
            return $value;
        };
    }

    public static function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function isValidPhone(string $phone): bool
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);
        return strlen($cleaned) >= 9 && strlen($cleaned) <= 15;
    }

    public static function formatPhone(string $phone, string $countryCode = '966'): string
    {
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        if (str_starts_with($cleaned, '0')) {
            $cleaned = substr($cleaned, 1);
        }

        if (!str_starts_with($cleaned, $countryCode)) {
            $cleaned = $countryCode . $cleaned;
        }

        return '+' . $cleaned;
    }

    public static function getClientIp(Request $request): string
    {
        $ipKeys = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR',
        ];

        foreach ($ipKeys as $key) {
            if ($request->server($key)) {
                $ip = $request->server($key);
                if (str_contains($ip, ',')) {
                    $ip = explode(',', $ip)[0];
                }
                return trim($ip);
            }
        }

        return $request->ip();
    }

    public static function isBlockedIp(string $ip): bool
    {
        $blockedIps = config('security.blocked_ips', []);

        if (in_array($ip, $blockedIps)) {
            return true;
        }

        $blockedRanges = config('security.blocked_ranges', []);
        foreach ($blockedRanges as $range) {
            if (self::ipInRange($ip, $range)) {
                return true;
            }
        }

        return false;
    }

    public static function ipInRange(string $ip, string $range): bool
    {
        if (str_contains($range, '/')) {
            [$subnet, $mask] = explode('/', $range);
            return (ip2long($ip) & ~((1 << (32 - $mask)) - 1)) === ip2long($subnet);
        }

        return false;
    }

    public static function generateSecureFilename(string $originalName): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $safeName = preg_replace('/[^a-zA-Z0-9]/', '-', pathinfo($originalName, PATHINFO_FILENAME));
        $safeName = trim($safeName, '-');
        $safeName = Str::limit($safeName, 50, '');

        return $safeName . '-' . Str::random(8) . '.' . strtolower($extension);
    }

    public static function validateFileExtension(UploadedFile $file, array $allowedExtensions): bool
    {
        $extension = strtolower($file->getClientOriginalExtension());
        return in_array($extension, $allowedExtensions);
    }

    public static function validateFileSize(UploadedFile $file, int $maxSizeInMB): bool
    {
        return $file->getSize() <= ($maxSizeInMB * 1024 * 1024);
    }

    public static function getFileMimeType(UploadedFile $file): string
    {
        return $file->getMimeType();
    }

    public static function isSafeFileType(UploadedFile $file): bool
    {
        $allowedMimes = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'application/pdf',
        ];

        return in_array($file->getMimeType(), $allowedMimes);
    }

    public static function generateCsrfToken(): string
    {
        return session()->token();
    }

    public static function validateCsrfToken(?string $token): bool
    {
        return $token === session()->token();
    }

    public static function sanitizeFilename(string $filename): string
    {
        $filename = preg_replace('/[^\p{L}\p{N}\s\-\_\.]/u', '', $filename);
        $filename = preg_replace('/[\s]+/', '-', $filename);
        return trim($filename, '-.');
    }
}
