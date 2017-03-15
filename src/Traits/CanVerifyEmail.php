<?php
/**
 * (c) Lunaweb Ltd. - Josias Montag
 * Date: 14.03.17
 * Time: 12:25
 */

namespace Lunaweb\EmailVerification\Traits;

use Lunaweb\EmailVerification\Notifications\EmailVerification as EmailVerificationNotification;


trait CanVerifyEmail
{

    /**
     * Get the e-mail address where verification links are sent.
     *
     * @return string
     */
    public function getEmailForEmailVerification()
    {
        return $this->email;
    }
    /**
     * Send the email verification notification.
     *
     * @param  string  $token
     * @param  int  $expiration
     * @return void
     */
    public function sendEmailVerificationNotification($token, $expiration)
    {
        $this->notify(new EmailVerificationNotification($token, $expiration));
    }

}