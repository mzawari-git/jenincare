<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class FixHeroSlideImages extends Command
{
    protected $signature = 'fix:hero-slides';
    protected $description = 'Fix hero slide image storage and symlink issues';

    public function handle(): int
    {
        $this->info('=== Fixing Hero Slide Image Issues ===');

        $this->fixStorageSymlink();
        $this->setFilesystemDisk();
        $this->clearCache();
        $this->verifyImages();

        $this->newLine();
        $this->info('Done! Hero slide images should now work.');
        $this->info('If images still dont show, run: php artisan serve');

        return Command::SUCCESS;
    }

    private function fixStorageSymlink(): void
    {
        $this->info('1. Fixing storage symlink...');

        $publicStorage = public_path('storage');
        $isLink = is_link($publicStorage);
        $isDir = is_dir($publicStorage);

        if ($isLink) {
            $this->line('   Symlink exists, removing...');
            if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                rmdir($publicStorage);
            } else {
                unlink($publicStorage);
            }
        } elseif ($isDir) {
            $this->line('   Directory exists, backing up...');
            File::move($publicStorage, public_path('storage_backup_' . time()));
        }

        try {
            Artisan::call('storage:link', ['--force' => true]);
            $this->line('   ' . trim(Artisan::output()));
        } catch (\Exception $e) {
            $this->warn('   Could not create symlink: ' . $e->getMessage());
            $this->info('   Creating manual symlink...');
            $this->createManualSymlink();
        }
    }

    private function createManualSymlink(): void
    {
        $target = storage_path('app/public');
        $link = public_path('storage');

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            $cmd = "mklink /J \"" . str_replace('/', '\\', $link) . "\" \"" . str_replace('/', '\\', $target) . "\"";
            $result = exec($cmd . " 2>&1");
            if ($result && !str_contains($result, 'Access is denied')) {
                $this->line("   Created junction");
            } else {
                $this->warn('   Failed to create junction. On Windows, run as administrator.');
            }
        } else {
            symlink($target, $link);
            $this->line('   Created symlink');
        }
    }

    private function setFilesystemDisk(): void
    {
        $this->info('2. Setting FILESYSTEM_DISK to public...');

        $envFile = base_path('.env');
        $envContent = File::get($envFile);
        $lines = explode("\n", $envContent);
        $found = false;

        foreach ($lines as $i => $line) {
            if (str_starts_with(trim($line), 'FILESYSTEM_DISK=')) {
                $lines[$i] = 'FILESYSTEM_DISK=public';
                $found = true;
                $this->line('   Updated FILESYSTEM_DISK=public');
            }
        }

        if (!$found) {
            $lines[] = 'FILESYSTEM_DISK=public';
            $this->line('   Added FILESYSTEM_DISK=public');
        }

        File::put($envFile, implode("\n", $lines));
        $this->info('   Updated .env file');
    }

    private function clearCache(): void
    {
        $this->info('3. Clearing caches...');

        Artisan::call('config:clear');
        $this->line('   Config cleared');

        Artisan::call('cache:clear');
        $this->line('   Cache cleared');

        Artisan::call('view:clear');
        $this->line('   Views cleared');
    }

    private function verifyImages(): void
    {
        $this->info('4. Verifying hero slide images...');

        $slides = \App\Models\HeroSlide::whereNotNull('image')->get();
        $this->line("   Found {$slides->count()} slides with images");

        foreach ($slides as $slide) {
            $path = storage_path('app/public/' . $slide->image);
            if (File::exists($path)) {
                $this->line("   [OK] {$slide->image}");
            } else {
                $this->warn("   [MISSING] {$slide->image}");
            }
        }
    }
}
