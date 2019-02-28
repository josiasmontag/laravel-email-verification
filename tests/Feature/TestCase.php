<?php
/**
 * Created by Josias Montag
 * Date: 06.01.18 15:42
 * Mail: josias@montag.info
 */

namespace Lunaweb\EmailVerification\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Lunaweb\EmailVerification\Middleware\IsEmailVerified;
use Lunaweb\EmailVerification\Providers\EmailVerificationServiceProvider;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Orchestra\Testbench\Traits\CreatesApplication;

abstract class TestCase extends BaseTestCase
{

    use CreatesApplication;

    public function setUp(): void
    {
        parent::setUp();

        $this->setUpDatabase();
        $this->createRoutes();

    }


    protected function getEnvironmentSetUp($app)
    {

        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);


        // Use test User model for users provider
        $app['config']->set('auth.providers.users.model', User::class);

    }


    /**
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [
            EmailVerificationServiceProvider::class
        ];
    }


    protected function setUpDatabase()
    {
        app('db')->connection()->getSchemaBuilder()->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->boolean('verified')->default(false);
            $table->softDeletes();
        });
    }


    protected function createRoutes()
    {

        Route::post('register', 'Lunaweb\EmailVerification\Tests\Feature\RegisterController@register')->name('register');
        Route::get('register/verify', 'Lunaweb\EmailVerification\Tests\Feature\RegisterController@verify')->name('verifyEmailLink');
        Route::get('register/verify/resend', 'Lunaweb\EmailVerification\Tests\Feature\RegisterController@showResendVerificationEmailForm')->name('showResendVerificationEmailForm');
        Route::post('register/verify/resend', 'Lunaweb\EmailVerification\Tests\Feature\RegisterController@resendVerificationEmail')->name('resendVerificationEmail');


        Route::get('/verified', function () {
            return 'ok!';
        })->middleware(IsEmailVerified::class);


        app('router')->getRoutes()->refreshNameLookups();
        app('router')->getRoutes()->refreshActionLookups();

    }

}
