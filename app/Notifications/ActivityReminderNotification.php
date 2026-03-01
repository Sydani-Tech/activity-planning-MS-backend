<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ActivityReminderNotification extends Notification
{
    use Queueable;

    public $activity;
    public $type; // 'upcoming' or 'overdue'

    /**
     * Create a new notification instance.
     */
    public function __construct($activity, $type)
    {
        $this->activity = $activity;
        $this->type = $type;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $subject = $this->type === 'upcoming'
            ? "Upcoming Activity: {$this->activity->title}"
            : "Overdue Activity: {$this->activity->title}";

        $message = $this->type === 'upcoming'
            ? "Your activity '{$this->activity->title}' is scheduled to start tomorrow ({$this->activity->start_date->format('Y-m-d')})."
            : "Your activity '{$this->activity->title}' was due on {$this->activity->end_date->format('Y-m-d')} and is currently overdue.";

        return (new MailMessage)
            ->subject($subject)
            ->line($message)
            ->action('View Activity', url(env('FRONTEND_URL', 'http://localhost:5174') . '/activities'))
            ->line('Please update the activity status accordingly.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'activity_reminder',
            'activity_id' => $this->activity->id,
            'title' => $this->activity->title,
            'message' => $this->type === 'upcoming' ? 'Activity starts tomorrow' : 'Activity is overdue',
            'status' => $this->type,
        ];
    }
}
