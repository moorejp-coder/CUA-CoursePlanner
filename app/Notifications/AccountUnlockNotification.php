<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;

class AccountUnlockNotification extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $unlockUrl,
        private readonly string $ip,
        private readonly Carbon $lockedAt,
    ) {}

    /** @return array<int, string> */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $time = $this->lockedAt->format('F j, Y \a\t g:i A T');
        $location = $this->resolveLocation();

        return (new MailMessage)
            ->subject('Security Alert: Failed Login Attempts — Busch School Course Planner')
            ->greeting('Hello,')
            ->line('Someone tried to log into your account multiple times. **If this wasn\'t you, please change your password immediately.**')
            ->line('**Time of attempts:** '.$time)
            ->line('**Approximate location:** '.$location)
            ->line('Your account has been temporarily locked. It will unlock automatically after 30 minutes.')
            ->action('Change My Password', route('password.request'))
            ->line('You can also unlock your account immediately without changing your password: [Unlock My Account]('.$this->unlockUrl.')')
            ->line('If you did this yourself (e.g. forgot your password), you can safely ignore this email and your account will unlock shortly.');
    }

    private function resolveLocation(): string
    {
        if (in_array($this->ip, ['127.0.0.1', '::1'], true)) {
            return 'Local network';
        }

        try {
            $response = Http::timeout(3)->get("https://ipwho.is/{$this->ip}");
            $data = $response->json();

            if (($data['success'] ?? false) === true) {
                $parts = array_filter([
                    $data['city'] ?? '',
                    $data['region'] ?? '',
                    $data['country'] ?? '',
                ]);

                return implode(', ', $parts) ?: 'Unknown';
            }
        } catch (\Throwable) {
            // Fall through to unknown on timeout or network error
        }

        return 'Unknown';
    }
}
