<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class ForgotMail extends Mailable
{
    use Queueable, SerializesModels;
    public $forgot;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($forgot)
    {
        //
        $this->forgot = $forgot;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('Forgot Password')->view('emails.forgot');
    }
}
