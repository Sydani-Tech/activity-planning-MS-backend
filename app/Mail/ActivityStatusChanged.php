<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ActivityStatusChanged extends Mailable
{
    use Queueable, SerializesModels;

    public $activity;
    public $oldStatus;
    public $updatedBy;

    public function __construct($activity, $oldStatus, $updatedBy)
    {
        $this->activity = $activity;
        $this->oldStatus = $oldStatus;
        $this->updatedBy = $updatedBy;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Activity Status Updated: {$this->activity->title}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.activity-status-changed',
        );
    }
}
