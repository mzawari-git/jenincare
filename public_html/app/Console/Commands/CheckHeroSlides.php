<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use App\Models\HeroSlide;

class CheckHeroSlides extends Command
{
    protected $signature = 'check:hero-slides';
    protected $description = 'Check and fix hero slide image visibility';

    public function handle(): int
    {
        $this->info('=== Hero Slides Check ===');
        $this->newLine();

        $slides = HeroSlide::with('product')->get();
        $this->info("Found {$slides->count()} hero slides");
        $this->newLine();

        foreach ($slides as $slide) {
            $this->table(
                ['Property', 'Value'],
                [
                    ['ID', $slide->id],
                    ['Title (ar)', $slide->title_ar],
                    ['Image Path', $slide->image ?? 'NULL'],
                    ['Image Position', $slide->image_position ?? 'right (default)'],
                    ['Is Active', $slide->is_active ? 'Yes' : 'No'],
                    ['image_url', $slide->image_url ?? 'NULL'],
                ]
            );

            $this->line("   Image URL: " . ($slide->image_url ?? 'none'));
            $this->line('');
        }

        $this->info('=== Storage Symlink Status ===');
        $this->checkStorageSymlink();
        $this->newLine();

        $this->info('=== Testing Image URLs ===');
        $this->testImageUrls();
        $this->newLine();

        $this->info('All Checks Complete!');
        return Command::SUCCESS;
    }

    private function checkStorageSymlink(): void
    {
        $storagePath = public_path('storage');

        if (is_link($storagePath)) {
            $target = readlink($storagePath);
            $this->info("   [OK] Symlink exists -> $target");
        } elseif (is_dir($storagePath)) {
            $this->warn("   [WARN] storage/ is a directory (not a symlink)");
            $this->info("   Files in storage/app/public:");
            $files = File::files(storage_path('app/public'));
            foreach ($files as $file) {
                $this->line("      - " . $file->getFilename());
            }
        } else {
            $this->error("   [ERROR] storage/ does not exist!");
        }
    }

    private function testImageUrls(): void
    {
        $slides = HeroSlide::whereNotNull('image')->get();

        foreach ($slides as $slide) {
            $url = $slide->image_url;
            $this->line("   Testing: $url");

            if (!$url) {
                $this->warn("      -> No URL generated");
                continue;
            }

            $context = stream_context_create([
                'http' => [
                    'method' => 'HEAD',
                    'ignore_errors' => true,
                ]
            ]);

            $headers = @get_headers($url, 1, $context);

            if ($headers && str_contains($headers[0], '200')) {
                $this->info("      -> [OK] HTTP 200");
            } else {
                $this->warn("      -> [WARN] HTTP " . ($headers[0] ?? 'no response'));
            }
        }
    }
}
