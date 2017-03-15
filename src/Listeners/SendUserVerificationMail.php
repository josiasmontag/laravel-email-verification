<?php
/**
 * (c) Lunaweb Ltd. - Josias Montag
 * Date: 27.02.17
 * Time: 18:26
 */

namespace Lunaweb\EmailVerification\Listeners;


use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Lunaweb\EmailVerification\EmailVerification;


class SendUserVerificationMail
{


    /**
     * Create a new listener instance.
     *
     * @param  NotificationRepository  $notifications
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Handle the event.
     *
     * @param  UserRegistered  $event
     * @return void
     */
    public function handle(Registered $event)
    {

        if(config('emailverification.listen_registered_event', true)) {

            $sent = resolve('Lunaweb\EmailVerification\EmailVerification')->sendVerifyLink($event->user);
            Session::flash($sent == EmailVerification::VERIFY_LINK_SENT ? 'success' : 'error', trans($sent));

        }

    }

}