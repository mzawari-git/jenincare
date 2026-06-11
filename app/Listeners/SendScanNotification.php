<?php

namespace App\Listeners;

use App\Events\ScanCreated;
use App\Models\User;
use App\Notifications\NewScanNotification;

class SendScanNotification
{
    public function handle(ScanCreated $event): void
    {
        $admins = User::where(function ($query) {
            $query->where('role', 'admin')
                ->orWhere('role', 'manager');
        })->get();

        foreach ($admins as $admin) {
            $admin->notify(new NewScanNotification($event->scan));
        }
    }
}
