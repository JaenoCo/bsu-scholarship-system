<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GrantSlipMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $scholar;
    public $scholarship;
    public $details;
    protected $pdf;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($scholar, $scholarship, $pdf, $details)
    {
        $this->scholar = $scholar;
        $this->scholarship = $scholarship;
        $this->pdf = $pdf;
        $this->details = $details;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $mail = $this->markdown('emails.grant-slip')
                    ->subject('Grant Release Notification - ' . $this->scholarship->scholarship_name);
        
        // Only attach PDF if it exists
        if ($this->pdf) {
            $mail->attachData($this->pdf, 'Grant_Slip_' . $this->scholarship->scholarship_name . '.pdf', [
                'mime' => 'application/pdf',
            ]);
        }
        
        return $mail;
    }
}
