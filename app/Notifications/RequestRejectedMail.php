<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RequestRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $reason;

    public function __construct($user, $reason, $status)
    {
        $this->user = $user;
        $this->reason = $reason;
        $this->status = $status;
    }

    public function build()
    {
        return $this->subject('Votre demande a été rejetée')
                    ->view('emails.request_rejected', [
                        'user' => $this->user,
                        'reason' => $this->reason,
                        "status" => $this->status
                    ]);
    }
}
