<?php

return [

    /**
     * Time in minutes, until the verification email link expires.
     * Defaults to 24h.
     *
     */
    "expire" => 1440,

    /**
     * Whether to listen to the \Illuminate\Auth\Events\Registered event to automatically
     * send a verification email.
     * Disable it to trigger the verification manually.
     */

    "listen_registered_event" => true

];