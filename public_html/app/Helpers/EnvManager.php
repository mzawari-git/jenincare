<?php

namespace App\Helpers;

class EnvManager
{
    public static function set(string $key, string $value, ?string $path = null): bool
    {
        $path = $path ?? base_path('.env');
        $pattern = '/^' . preg_quote($key, '/') . '=.*$/m';

        if (!file_exists($path)) {
            return false;
        }

        $content = file_get_contents($path);

        if (preg_match($pattern, $content)) {
            $content = preg_replace($pattern, "{$key}={$value}", $content);
        } else {
            $content .= PHP_EOL . "{$key}={$value}";
        }

        return file_put_contents($path, $content) !== false;
    }

    public static function setMultiple(array $pairs, ?string $path = null): bool
    {
        $success = true;
        foreach ($pairs as $key => $value) {
            if (!self::set($key, $value, $path)) {
                $success = false;
            }
        }
        return $success;
    }

    public static function get(string $key, ?string $path = null): ?string
    {
        $path = $path ?? base_path('.env');
        $pattern = '/^' . preg_quote($key, '/') . '=(.*)$/m';

        if (!file_exists($path)) {
            return null;
        }

        if (preg_match($pattern, file_get_contents($path), $matches)) {
            return trim($matches[1]);
        }

        return null;
    }
}
