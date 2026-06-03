<?php

namespace App\Notifications;

use App\Models\SkinAnalysis;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewScanNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly SkinAnalysis $scan,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = url("/admin/scans/{$this->scan->id}");

        return (new MailMessage)
            ->subject('New Skin Analysis Scan Received')
            ->greeting('New Scan Alert')
            ->line('A new skin analysis scan has been submitted and requires your review.')
            ->line("User: {$this->scan->user?->name} ({$this->scan->user?->email})")
            ->line("Scan ID: #{$this->scan->id}")
            ->line("Submitted: {$this->scan->created_at->format('M d, Y H:i')}")
            ->action('Review Scan', $url)
            ->line('Please review and approve or reject this scan.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'scan_created',
            'scan_id' => $this->scan->id,
            'user_name' => $this->scan->user?->name,
            'user_email' => $this->scan->user?->email,
            'message' => "New scan #{$this->scan->id} from {$this->scan->user?->name}",
            'url' => "/admin/scans/{$this->scan->id}",
        ];
    }
}
