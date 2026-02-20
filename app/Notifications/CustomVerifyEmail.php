<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;

class CustomVerifyEmail extends BaseVerifyEmail
{
    public function toMail($notifiable)
    {
        return (new MailMessage)
                    ->from('noreply@ITcompany.com', 'IT Helpdesk') // custom sender
                    ->subject('Verify Your Email Address')
                    ->line('Click the button below to verify your email address.')
                    ->action('Verify Email', $this->verificationUrl($notifiable))
                    ->line('If you did not create an account, no further action is required.');
    }
}