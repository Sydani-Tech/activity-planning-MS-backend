<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WeeklyReport extends Mailable
{
    use Queueable, SerializesModels;

    public $reportData;
    public $weekNumber;

    public function __construct($reportData, $weekNumber)
    {
        $this->reportData = $reportData;
        $this->weekNumber = $weekNumber;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Weekly Activity Report — Week {$this->weekNumber}",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.weekly-report',
        );
    }
}
