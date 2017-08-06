<?php
/**
 * (c) Lunaweb Ltd. - Josias Montag
 * Date: 14.03.17
 * Time: 12:30
 */

namespace Lunaweb\EmailVerification\Notifications;

use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class EmailVerification extends Notification
{

    /**
     * The verification mail reset token.
     *
     * @var string
     */
    public $token;
    /**
     * The verification mail expiration date.
     *
     * @var int
     */
    public $expiration;

    /**
     * Create a notification instance.
     *
     * @param  string $token
     * @param  int $expiration
     * @return void
     */
    public function __construct($token, $expiration)
    {
        $this->token = $token;
        $this->expiration = $expiration;
    }

    /**
     * Get the notification's channels.
     *
     * @param  mixed $notifiable
     * @return array|string
     */
    public function via($notifiable)
    {
        return ['mail'];
    }

    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $email = $notifiable->getEmailForEmailVerification();
        $link = route('verifyEmailLink', ['email' => $email, 'expiration' => $this->expiration, 'token' => $this->token]);
        return (new MailMessage)
            ->line(trans('emailverification::messages.email.welcome'))
            ->line(trans('emailverification::messages.email.instructions'))
            ->action(trans('emailverification::messages.email.action'), $link);
    }

}