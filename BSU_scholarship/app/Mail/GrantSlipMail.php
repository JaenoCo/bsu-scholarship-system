<?php

namespace App\Mail;

use App\Models\GrantRelease;
use App\Models\Scholar;
use App\Models\Scholarship;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class GrantSlipMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Scholar $scholar,
        public Scholarship $scholarship,
        public GrantRelease $grantRelease
    )
    {
    }

    public function build()
    {
        return $this->markdown('emails.grant-slip')
            ->subject('Scholarship Grant Released – ' . $this->scholarship->scholarship_name);
    }
}
