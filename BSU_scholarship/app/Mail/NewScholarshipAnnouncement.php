<?php

namespace App\Mail;

use App\Models\Scholarship;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewScholarshipAnnouncement extends Mailable
{
    use Queueable, SerializesModels;

    public $scholarship;

    /**
     * Create a new message instance.
     */
    public function __construct(Scholarship $scholarship)
    {
        $this->scholarship = $scholarship;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New Scholarship Available: ' . $this->scholarship->scholarship_name,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.new-scholarship',
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
