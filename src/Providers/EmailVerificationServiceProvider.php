<?php
/**
 * (c) Lunaweb Ltd. - Josias Montag
 * Date: 27.02.17
 * Time: 18:22
 */

namespace Lunaweb\EmailVerification\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Lunaweb\EmailVerification\EmailVerification;

class EmailVerificationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {

        /*
         * Routes
         */
        $this->loadRoutesFrom(__DIR__ . '/../Http/routes.php');


        /*
         * Views
         */
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'emailverification');
        $this->publishes([
            __DIR__ . '/../../resources/views' => resource_path('views/vendor/emailverification'),
        ], 'views');


        /*
         * Migrations
         */
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->publishes([
            __DIR__ . '/../../database/migrations' => database_path('migrations')
        ], 'migrations');


        /*
         * Translations
         */
        $this->loadTranslationsFrom(__DIR__ . '/../../resources/lang', 'emailverification');
        $this->publishes([
            __DIR__ . '/../../resources/lang' => resource_path('lang/vendor/emailverification'),
        ], 'translations');

        /*
         * Config
         */
        $this->publishes([
            __DIR__ . '/../../config/emailverification.php' => config_path('emailverification.php'),
        ]);

    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->register(\Lunaweb\EmailVerification\Providers\EmailVerificationEventServiceProvider::class);
        $this->app->singleton(\Lunaweb\EmailVerification\EmailVerification::class, function ($app) {
            return new EmailVerification(
                Auth::getProvider(),
                Auth::getDispatcher(),
                config('app.key'),
                config('emailverification.expire', 1440)
            );
        });

    }


}
