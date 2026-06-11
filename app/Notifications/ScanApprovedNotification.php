<?php

namespace App\Notifications;

use App\Models\SkinAnalysis;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ScanApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly SkinAnalysis $scan,
    ) {}

    public function via(object $notifiable): array
    {
        return [];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $url = config('app.frontend_url') . "/scan/{$this->scan->id}";

        return (new MailMessage)
            ->subject('Your Skin Analysis Results Are Ready')
            ->greeting('Your Results Are Ready!')
            ->line('Your skin analysis has been reviewed and approved by our experts.')
            ->line("Overall Health Score: {$this->scan->overall_health_score}%")
            ->line("Approved at: {$this->scan->approved_at?->format('M d, Y H:i')}")
            ->action('View Your Results', $url)
            ->line('Thank you for using our skin analysis service.');
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type' => 'scan_approved',
            'scan_id' => $this->scan->id,
            'health_score' => $this->scan->overall_health_score,
            'message' => 'Your skin analysis results are ready!',
            'url' => "/scan/{$this->scan->id}",
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'scan_id' => $this->scan->id,
            'status' => 'approved',
            'health_score' => $this->scan->overall_health_score,
            'approved_at' => $this->scan->approved_at?->toIso8601String(),
            'pin_code' => $this->scan->accessPin?->pin_code,
        ];
    }
}
