<?php
/**
 * (c) Lunaweb Ltd. - Josias Montag
 * Date: 14.03.17
 * Time: 13:11
 */

namespace Lunaweb\EmailVerification\Contracts;


interface CanVerifyEmail
{

    /**
     * Get the e-mail address where verification links are sent.
     *
     * @return string
     */
    public function getEmailForEmailVerification();

    /**
     * Send the email verification notification.
     *
     * @param  string  $token
     * @param  int  $expiration
     * @return void
     */
    public function sendEmailVerificationNotification($token, $expiration);

}