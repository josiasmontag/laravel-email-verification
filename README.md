
<p align="center">
<a href="https://travis-ci.org/josiasmontag/laravel-email-verification"><img src="https://travis-ci.org/josiasmontag/laravel-email-verification.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/josiasmontag/laravel-email-verification"><img src="https://poser.pugx.org/josiasmontag/laravel-email-verification/d/total.svg" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/josiasmontag/laravel-email-verification"><img src="https://poser.pugx.org/josiasmontag/laravel-email-verification/v/stable.svg" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/josiasmontag/laravel-email-verification"><img src="https://poser.pugx.org/josiasmontag/laravel-email-verification/license.svg" alt="License"></a>
</p>

## Introduction

The Laravel Email Verification package is built for Laravel 5.4/5.5/5.6 to easily handle a user verification and validate the e-mail. It is inspired by [crypto-based password resets](https://github.com/laravel/framework/pull/17499) and the [email verification package by jrean](https://github.com/jrean/laravel-user-verification).

- [x] Crypto-based email verification. No need to store a temporary token in the database!
- [x] Event based: No need to override your `register()` method.
- [x] Using the Laravel 5.3 notification system.
- [x] Allow certain routes for verified users only using the `IsEmailVerified` middleware.
- [x] Let the users resend the verification email at anytime.
- [x] Ready for Localization.
      

## Configuration


To get started, use Composer to add the package to your project's dependencies:

    composer require josiasmontag/laravel-email-verification


In Laravel 5.5 the service provider will automatically get registered. In older versions of the framework just register the `Lunaweb\EmailVerification\Providers\EmailVerificationServiceProvider` in your `config/app.php` configuration file:

```php
'providers' => [
    // Other service providers...

    Lunaweb\EmailVerification\Providers\EmailVerificationServiceProvider::class,
],
```

### Migration

The table representing the user must be updated with a `verified` column.
This update will be performed by the migrations included with this package.

To run the migrations from this package use the following command:

```
php artisan migrate --path="/vendor/josiasmontag/laravel-email-verification/database/migrations"
```

The package tries to guess your `user` table by checking what is set in the auth providers users settings.
If this key is not found, the default `App\User` will be used to get the table name.

To customize the migration, publish it with the following command:

```
php artisan vendor:publish --provider="Lunaweb\EmailVerification\Providers\EmailVerificationServiceProvider" --tag="migrations"
```

### User Model

The model representing the `User` must implement the `CanVerifyEmail` interface. The package comes with a `CanVerifyEmail` trait for a quick implementation. You can customize this trait in order to change the activation email.


```php
use Illuminate\Foundation\Auth\User as Authenticatable;
use Lunaweb\EmailVerification\Traits\CanVerifyEmail;
use Lunaweb\EmailVerification\Contracts\CanVerifyEmail as CanVerifyEmailContract;

class User extends Authenticatable implements CanVerifyEmailContract
{

    use CanVerifyEmail;

    // ...
}
```

### Register Controller

The package offers a `VerifiesEmail` trait for your `RegisterController`. You must update the middleware exception to allow `verify` routes to be access by authenticated users.

```php

use Lunaweb\EmailVerification\Traits\VerifiesEmail;

class RegisterController extends Controller
{

    use RegistersUsers, VerifiesEmail;


    public function __construct()
    {
          $this->middleware('guest', ['except' => ['verify', 'showResendVerificationEmailForm', 'resendVerificationEmail']]);
          $this->middleware('auth', ['only' => ['showResendVerificationEmailForm', 'resendVerificationEmail']]);
    }

    // ...

}

```

There is no need to override `register()`. As default, the package listens for the `Illuminate\Auth\Events\Registered` event and sends the verification mail. You can disable this behavior using the `listen_registered_event` setting.

### Routes

The package adds the following routes.

```php
Route::get('register/verify', 'App\Http\Controllers\Auth\RegisterController@verify')->name('verifyEmailLink');
Route::get('register/verify/resend', 'App\Http\Controllers\Auth\RegisterController@showResendVerificationEmailForm')->name('showResendVerificationEmailForm');
Route::post('register/verify/resend', 'App\Http\Controllers\Auth\RegisterController@resendVerificationEmail')->name('resendVerificationEmail');

```

### Middleware


To register the IsEmailVerified middleware add the following to the `$routeMiddleware` array within the `app/Http/Kernel.php` file:

```php
protected $routeMiddleware = [
    // …
    'isEmailVerified' => \Lunaweb\EmailVerification\Middleware\IsEmailVerified::class,
```

Apply the middleware on your routes:

```php
Route::group(['middleware' => ['web', 'auth', 'isEmailVerified']], function () {
    …
```

### Events

The package emits 2 events:

* ``Lunaweb\EmailVerification\Events\EmailVerificationSent``
* ``Lunaweb\EmailVerification\Events\UserVerified``



### Resend the verification mail

Using the `isEmailVerified` Middleware, the following form is shown to the user. It allows the user to correct his email address and resend the verification mail.

![Screenshot](https://user-images.githubusercontent.com/1945577/27735164-7b316630-5d9e-11e7-86f6-8922a2488cfb.png)

You can manually point the user to this form using the `showResendVerificationEmailForm` route (Default: `register/verify/resend`).

To programmatically resend the verification mail:
```php
$this->app->make('Lunaweb\EmailVerification\EmailVerification')->sendVerifyLink($user);
```


### Customize the verification mail

Therefore, override `sendEmailVerificationNotification()` of your User model. Example:

```php
class User implements CanVerifyEmailContract
{

    use CanVerifyEmail;

    /**
     * Send the email verification notification.
     *
     * @param  string  $token   The verification mail reset token.
     * @param  int  $expiration The verification mail expiration date.
     * @return void
     */
    public function sendEmailVerificationNotification($token, $expiration)
    {
        $this->notify(new MyEmailVerificationNotification($token, $expiration));
    }
}
```

### Customize the resend form
```
php artisan vendor:publish --provider="Lunaweb\EmailVerification\Providers\EmailVerificationServiceProvider" --tag="views"
```
The template can be found in `resources/views/vendor/emailverification/resend.blade.php`

### Customize the messages / localization
```
php artisan vendor:publish --provider="Lunaweb\EmailVerification\Providers\EmailVerificationServiceProvider" --tag="translations"
```
The localization files can be found in `resources/lang/vendor/emailverification`
