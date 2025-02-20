<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendNewsletterEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $email;
    protected string $subject;
    protected string $message;

    public function __construct(string $email, string $subject, string $message)
    {
        $this->email = $email;
        $this->subject = $subject;
        $this->message = $message;
    }

    public function handle()
    {
        Mail::send('emails.newsletter', ['messageContent' => $this->message], function ($mail) {
            $mail->to($this->email)
                 ->subject($this->subject)
                 ->attach(public_path('images/logo.png'), [
                    'as' => 'logo.png',
                    'mime' => 'image/png',
                ]);
        });
    }
}
