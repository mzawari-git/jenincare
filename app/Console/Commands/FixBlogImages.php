<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BlogPost;
use Illuminate\Support\Facades\File;

class FixBlogImages extends Command
{
    protected $signature = 'blog:fix-images';
    protected $description = 'Migrate old blog image paths to new storage';

    public function handle()
    {
        $posts = BlogPost::withTrashed()->whereNotNull('image')->get();
        $count = 0;

        foreach ($posts as $post) {
            $oldPath = $post->image;

            if (str_starts_with($oldPath, 'uploads/')) {
                continue;
            }

            $oldFile = storage_path('app/public/' . $oldPath);
            $newName = 'uploads/blog/' . basename($oldPath);
            $newPath = public_path($newName);

            if (File::exists($newPath)) {
                $post->update(['image' => $newName]);
                $this->info("Already exists, updated DB: {$oldPath} -> {$newName}");
                $count++;
            } elseif (File::exists($oldFile)) {
                File::ensureDirectoryExists(dirname($newPath));
                File::copy($oldFile, $newPath);
                $post->update(['image' => $newName]);
                $this->info("Copied and updated: {$oldPath} -> {$newName}");
                $count++;
            } else {
                $this->warn("File not found: {$oldFile}");
                $post->update(['image' => '']);
                $this->info("Cleared empty image for post ID {$post->id}");
                $count++;
            }
        }

        $this->info("Fixed {$count} blog posts.");
    }
}
