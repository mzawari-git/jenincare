<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Frontend\ServeStorageController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DebugStorageRoute extends Command
{
    protected $signature = 'debug:storage';
    protected $description = 'Debug storage route';

    public function handle(): int
    {
        $this->info("Testing ServeStorageController directly...");

        $controller = new ServeStorageController();

        try {
            $response = $controller->show('hero-slides/gd8EWkX4bmiQQWij6yY6T23XsmRX4mcAT7EwLSRn.jpg');
            $this->info("Response type: " . get_class($response));
            $this->info("Status: " . $response->getStatusCode());
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            $this->error("File: " . $e->getFile() . ":" . $e->getLine());
        }

        return Command::SUCCESS;
    }
}
