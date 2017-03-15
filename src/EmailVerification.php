<?php
/**
 * (c) Lunaweb Ltd. - Josias Montag
 * Date: 14.03.17
 * Time: 12:04
 */

namespace Lunaweb\EmailVerification;


use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Lunaweb\EmailVerification\Events\UserVerified;
use Lunaweb\EmailVerification\Events\EmailVerificationSent;
use UnexpectedValueException;
use Lunaweb\EmailVerification\Contracts\CanVerifyEmail as CanVerifyEmailContract;

class EmailVerification
{

    /**
     * Constant representing a successfully sent reminder.
     *
     * @var string
     */
    const VERIFY_LINK_SENT = 'emailverification::messages.sent';

    /**
     * Constant representing a successfully reset password.
     *
     * @var string
     */
    const VERIFIED = 'emailverification::messages.done';

    /**
     * Constant representing the user not found response.
     *
     * @var string
     */
    const INVALID_USER = 'emailverification::messages.user';

    /**
     * Constant representing an invalid token.
     *
     * @var string
     */
    const INVALID_TOKEN = 'emailverification::messages.token';

    /**
     * Constant representing an expired token.
     *
     * @var string
     */
    const EXPIRED_TOKEN = 'emailverification::messages.token';

    /**
     * The application key.
     *
     * @var string
     */
    protected $key;

    /**
     * The user provider implementation.
     *
     * @var \Illuminate\Contracts\Auth\UserProvider
     */
    protected $users;

    /**
     * The number of minutes that the reset token should be considered valid.
     *
     * @var int
     */
    protected $expiration;


    /**
     * Create a new email verification instance.
     *
     * @param  \Illuminate\Contracts\Auth\UserProvider  $users
     * @param  string  $key
     * @param  int  $expiration
     * @return void
     */
    public function __construct(UserProvider $users, $key, $expiration)
    {
        $this->key = $key;
        $this->users = $users;
        $this->expiration = $expiration;
    }

    /**
     * Send a email verification link to a user.
     *
     * @param $user
     * @return string
     * @internal param array $credentials
     */
    public function sendVerifyLink($user)
    {

        if (is_null($user)) {
            return static::INVALID_USER;
        }

        $expiration = Carbon::now()->addMinutes($this->expiration)->timestamp;

        // Once we have the reset token, we are ready to send the message out to this
        // user with a link to reset their password. We will then redirect back to
        // the current URI having nothing set in the session to indicate errors.
        $user->sendEmailVerificationNotification(
            $this->createToken($user, $expiration),
            $expiration
        );
        event(new EmailVerificationSent($user));

        return static::VERIFY_LINK_SENT;
    }

    /**
     * Verify the user for the given token.
     *
     * @param  array  $credentials
     * @param  \Closure  $callback
     * @return mixed
     */
    public function verify(array $credentials, Closure $callback)
    {
        // If the responses from the validate method is not a user instance, we will
        // assume that it is a redirect and simply return it from this method and
        // the user is properly redirected having an error message on the post.
        $user = $this->validateEmailVerification($credentials);

        if (! $user instanceof CanVerifyEmailContract) {
            return $user;
        }

        $callback($user);

        event(new UserVerified($user));

        return static::VERIFIED;
    }

    /**
     * Validate a email verification for the given credentials.
     *
     * @param  array  $credentials
     * @return \Lunaweb\User\Auth\CanVerifyEmailContract
     */
    protected function validateEmailVerification(array $credentials)
    {
        if (is_null($user = $this->getUser($credentials))) {
            return static::INVALID_USER;
        }

        if (! $this->validateToken($user, $credentials)) {
            return static::INVALID_TOKEN;
        }

        if (! $this->validateTimestamp($credentials['expiration'])) {
            return static::EXPIRED_TOKEN;
        }

        return $user;
    }



    /**
     * Get the user for the given credentials.
     *
     * @param  array  $credentials
     * @return \Lunaweb\User\Auth\CanVerifyEmailContract
     *
     * @throws \UnexpectedValueException
     */
    public function getUser(array $credentials)
    {
        $user = $this->users->retrieveByCredentials(Arr::only($credentials, ['email']));

        if ($user && ! $user instanceof CanVerifyEmailContract) {
            throw new UnexpectedValueException('User must implement CanVerifyEmailContract interface.');
        }

        return $user;
    }

    /**
     * Create a new password reset token for the given user.
     *
     * @param  CanVerifyEmailContract $user
     * @param  int $expiration
     * @return string
     */
    public function createToken(CanVerifyEmailContract $user, $expiration)
    {
        $payload = $this->buildPayload($user, $user->getEmailForEmailVerification(), $expiration);

        return hash_hmac('sha256', $payload, $this->getKey());
    }

    /**
     * Validate the given password reset token.
     *
     * @param  CanVerifyEmailContract $user
     * @param  array $credentials
     * @return bool
     */
    public function validateToken(CanVerifyEmailContract $user, array $credentials)
    {
        $payload = $this->buildPayload($user, $credentials['email'], $credentials['expiration']);

        return hash_equals($credentials['token'], hash_hmac('sha256', $payload, $this->getKey()));
    }

    /**
     * Validate the given expiration timestamp.
     *
     * @param  int $expiration
     * @return bool
     */
    public function validateTimestamp($expiration)
    {
        return Carbon::createFromTimestamp($expiration)->isFuture();
    }

    /**
     * Return the application key.
     *
     * @return string
     */
    public function getKey()
    {
        if (Str::startsWith($this->key, 'base64:')) {
            return base64_decode(substr($this->key, 7));
        }

        return $this->key;
    }

    /**
     * Returns the payload string containing.
     *
     * @param  CanVerifyEmailContract  $user
     * @param  string  $email
     * @param  int  $expiration
     * @return string
     */
    protected function buildPayload(CanVerifyEmailContract $user, $email, $expiration)
    {
        return implode(';', [
            $email,
            $expiration,
            $user->password,
        ]);
    }
}