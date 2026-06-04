<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\HeroSlide;

class TestStorageAccess extends Command
{
    protected $signature = 'test:storage';
    protected $description = 'Test storage file access';

    public function handle(): int
    {
        $path = public_path('storage/hero-slides/gd8EWkX4bmiQQWij6yY6T23XsmRX4mcAT7EwLSRn.jpg');
        $this->info("Testing path: $path");
        $this->info("Exists: " . (file_exists($path) ? 'YES' : 'NO'));
        $this->info("Is file: " . (is_file($path) ? 'YES' : 'NO'));

        $slide = HeroSlide::first();
        if ($slide) {
            $this->info("Slide image_url: " . $slide->image_url);
        }

        return Command::SUCCESS;
    }
}
