<?php

namespace Acelle\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class MailListSubscriptionNotificationMailer extends Mailable
{
    use Queueable, SerializesModels;

    protected $subscriber;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($subscriber)
    {
        $this->subscriber = $subscriber;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject($this->subject)
                    ->from(config('mail.from')['address'], config('mail.from')['name'])
                    ->markdown('emails.mail_list_subscription_notification_email')
                    ->with(['subscriber' => $this->subscriber]);
    }
}
