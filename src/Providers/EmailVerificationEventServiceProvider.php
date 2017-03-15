<?php
/**
 * (c) Lunaweb Ltd. - Josias Montag
 * Date: 27.02.17
 * Time: 18:22
 */

namespace Lunaweb\EmailVerification\Providers;


use Illuminate\Foundation\Support\Providers\EventServiceProvider;

class EmailVerificationEventServiceProvider extends EventServiceProvider
{

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [

        'Illuminate\Auth\Events\Registered' => [
            'Lunaweb\EmailVerification\Listeners\SendUserVerificationMail',
        ],

    ];

    /**
     * Register any other events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }


}