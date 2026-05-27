<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class AccountDeletedNotification extends Notification
{
    use Queueable;

    /**
     * @param  array{name: string, email: string, deleted_at: Carbon, records: array<string, int>}  $manifest
     */
    public function __construct(private readonly array $manifest) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $m = $this->manifest;
        $time = $m['deleted_at']->format('F j, Y \a\t g:i A T');
        $records = $m['records'];

        return (new MailMessage)
            ->subject('Your Busch Course Planner account has been permanently deleted')
            ->greeting('Hello '.$m['name'].',')
            ->line('As requested, your account and all associated data have been **permanently deleted**. This action cannot be undone.')
            ->line('**Deletion confirmed:** '.$time)
            ->line('---')
            ->line('**Records removed:**')
            ->line('- Account & login credentials: 1')
            ->line('- Academic profile: '.$records['academic_profile'])
            ->line('- Course history records: '.$records['courses'])
            ->line('- Active sessions: '.$records['sessions'])
            ->line('- Pending password reset tokens: '.$records['password_reset_tokens'])
            ->line('---')
            ->line('**About security logs:** Your activity may appear in server security logs (IP address and account ID only) for up to 14 days as required for abuse prevention. These logs contain no academic data and are automatically purged.')
            ->line('If you did not request this deletion, your account is already gone — contact your academic advisor immediately and consider changing any passwords you reused on this service.')
            ->salutation('— Busch School Course Planner');
    }
}
