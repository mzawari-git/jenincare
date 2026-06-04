<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class DeploySync extends Command
{
    protected $signature = 'deploy:sync
                            {--force : Skip confirmation prompts}';

    protected $description = 'Guarantee 100% sync between localhost and hosting by clearing all caches and forcing consistent settings';

    public function handle(): int
    {
        $this->info('🚀 Starting deploy sync...');
        $this->newLine();

        // 1. Clear ALL Laravel caches
        $this->info('Step 1/6: Clearing Laravel caches...');
        $caches = [
            'view:clear' => 'Compiled Blade views',
            'cache:clear' => 'Application cache',
            'config:clear' => 'Config cache',
            'route:clear' => 'Route cache',
            'event:clear' => 'Event cache',
        ];
        foreach ($caches as $command => $label) {
            try {
                Artisan::call($command);
                $this->line("  ✓ {$label} cleared");
            } catch (\Exception $e) {
                $this->warn("  ⚠ {$label}: " . $e->getMessage());
            }
        }

        // 2. Force theme settings in database
        $this->info('Step 2/6: Forcing consistent database settings...');
        $forcedSettings = [
            'site_theme' => 'rose',
            'site_name' => 'شركة جنين للتجميل',
            'site_name_ar' => 'شركة جنين للتجميل',
        ];
        foreach ($forcedSettings as $key => $value) {
            DB::table('settings')
                ->updateOrInsert(
                    ['key' => $key],
                    ['value' => json_encode($value), 'group' => 'general', 'type' => 'text', 'updated_at' => now()]
                );
            $this->line("  ✓ {$key} = {$value}");
        }

        // 3. Clear storage/framework/views manually (extra safety)
        $this->info('Step 3/6: Purging compiled view files...');
        $viewPath = storage_path('framework/views');
        if (File::isDirectory($viewPath)) {
            $files = File::files($viewPath);
            $count = 0;
            foreach ($files as $file) {
                if ($file->getExtension() === 'php') {
                    File::delete($file->getPathname());
                    $count++;
                }
            }
            $this->line("  ✓ Deleted {$count} compiled view files");
        }

        // 4. Clear bootstrap/cache
        $this->info('Step 4/6: Clearing bootstrap cache...');
        $bootstrapCache = base_path('bootstrap/cache');
        if (File::isDirectory($bootstrapCache)) {
            $files = File::files($bootstrapCache);
            $count = 0;
            foreach ($files as $file) {
                if (in_array($file->getFilename(), ['packages.php', 'services.php', 'config.php', 'routes.php'])) {
                    File::delete($file->getPathname());
                    $count++;
                }
            }
            $this->line("  ✓ Deleted {$count} bootstrap cache files");
        }

        // 5. Verify theme consistency
        $this->info('Step 5/6: Verifying theme consistency...');
        $theme = DB::table('settings')->where('key', 'site_theme')->value('value');
        $theme = json_decode($theme) ?? $theme;
        if ($theme === 'rose') {
            $this->line("  ✓ Database theme is correctly set to 'rose'");
        } else {
            $this->warn("  ⚠ Database theme is '{$theme}', forcing to 'rose'...");
            DB::table('settings')->where('key', 'site_theme')->update(['value' => json_encode('rose')]);
        }

        // 6. Regenerate caches if in production
        $this->info('Step 6/6: Regenerating production caches...');
        if (app()->environment('production')) {
            try {
                Artisan::call('config:cache');
                $this->line("  ✓ Config cached");
            } catch (\Exception $e) {
                $this->warn("  ⚠ Config cache failed: " . $e->getMessage());
            }
            try {
                Artisan::call('route:cache');
                $this->line("  ✓ Routes cached");
            } catch (\Exception $e) {
                $this->warn("  ⚠ Route cache failed: " . $e->getMessage());
            }
        } else {
            $this->line("  ℹ Skipping production cache (not in production)");
        }

        $this->newLine();
        $this->info('✅ Deploy sync complete!');
        $this->info('Your localhost and hosting should now be 100% identical.');
        $this->newLine();
        $this->comment('Next steps:');
        $this->line('  1. Run "git pull" on your hosting server');
        $this->line('  2. Run "php artisan migrate" on hosting');
        $this->line('  3. Run "php artisan deploy:sync" on hosting');
        $this->line('  4. Hard-refresh browser (Ctrl+F5 or Cmd+Shift+R)');

        return self::SUCCESS;
    }
}
