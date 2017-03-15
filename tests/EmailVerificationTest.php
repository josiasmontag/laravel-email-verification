<?php
/**
 * (c) Lunaweb Ltd. - Josias Montag
 * Date: 14.03.17
 * Time: 12:55
 */

namespace Tests;

use Mockery as m;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;
use Lunaweb\EmailVerification\EmailVerification;


class EmailVerificationTest extends TestCase
{

    public function tearDown()
    {
        m::close();
    }

    public function testIfUserIsNotFoundErrorRedirectIsReturned()
    {
        $mocks = $this->getMocks();
        $emailVerification = $this->getMockBuilder('Lunaweb\EmailVerification\EmailVerification')->setMethods(['getUser', 'makeErrorRedirect'])->setConstructorArgs(array_values($mocks))->getMock();

        $this->assertEquals(EmailVerification::INVALID_USER, $emailVerification->sendVerifyLink(null));
    }

    /**
     * @expectedException UnexpectedValueException
     */
    public function testGetUserThrowsExceptionIfUserDoesntImplementCanVerifyEmail()
    {
        $creds = ['email' => 'josias@montag.info'];
        $emailVerification = $this->getEmailVerification($mocks = $this->getMocks());
        $mocks['users']->shouldReceive('retrieveByCredentials')->once()->with($creds)->andReturn('foo');

        $emailVerification->getUser($creds);
    }

    public function testUserIsRetrievedByCredentials()
    {
        $creds = ['email' => 'josias@montag.info'];
        $emailVerification = $this->getEmailVerification($mocks = $this->getMocks());
        $mocks['users']->shouldReceive('retrieveByCredentials')->once()->with($creds)->andReturn($user = m::mock('Lunaweb\EmailVerification\Contracts\CanVerifyEmail'));

        $this->assertEquals($user, $emailVerification->getUser($creds));
    }

    public function testEmailVerificationCreatesTokenAndRedirectsWithoutError()
    {
        $creds = ['email' => 'josias@montag.info'];
        $mocks = $this->getMocks();
        $emailVerification = $this->getMockBuilder('Lunaweb\EmailVerification\EmailVerification')->setMethods(['getKey'])->setConstructorArgs(array_values($mocks))->getMock();
        $user = m::mock('Lunaweb\EmailVerification\Contracts\CanVerifyEmail');
        $user->password = 'foo';
        $user->updated_at = Carbon::now();
        $user->shouldReceive('getEmailForEmailVerification')->once();

        $user->shouldReceive('sendEmailVerificationNotification');
        $this->assertEquals(EmailVerification::VERIFY_LINK_SENT, $emailVerification->sendVerifyLink($user));
    }



    public function testVerifiedIsReturnedByVerifyWhenCredsAreValid()
    {
        $creds = ['email' => 'josias@montag.info', 'expiration' => time() + 1000];
        $user = m::mock('Lunaweb\EmailVerification\Contracts\CanVerifyEmail');
        $user->shouldReceive('getEmailForEmailVerification')->andReturn("josias@montag.info");
        $user->password = 'foo';
        $user->updated_at = Carbon::now();

        $emailVerification = $this->getEmailVerification($mocks = $this->getMocks());
        $creds['token'] = $emailVerification->createToken($user, $creds['expiration']);
        $mocks['users']->shouldReceive('retrieveByCredentials')->once()->with(['email' => 'josias@montag.info'])->andReturn($user);

        $this->assertEquals(EmailVerification::VERIFIED, $emailVerification->verify($creds, function () {
            //
        }));
    }


    public function testInvalidTokenIsReturnedByVerifyWhenTokenInvalid()
    {
        $creds = ['email' => 'josias@montag.info', 'expiration' => time() + 1000];
        $user = m::mock('Lunaweb\EmailVerification\Contracts\CanVerifyEmail');
        $user->shouldReceive('getEmailForEmailVerification')->andReturn("josias@montag.info");
        $user->password = 'foo';
        $user->updated_at = Carbon::now();

        $emailVerification = $this->getEmailVerification($mocks = $this->getMocks());
        $creds['token'] = "invalid";
        $mocks['users']->shouldReceive('retrieveByCredentials')->once()->with(['email' => 'josias@montag.info'])->andReturn($user);

        $this->assertEquals(EmailVerification::INVALID_TOKEN, $emailVerification->verify($creds, function () {
            //
        }));
    }


    public function testInvalidUserIsReturnedByVerifyWhenEmailInvalid()
    {
        $creds = ['email' => 'invalid@email.de', 'expiration' => time() + 1000];
        $user = m::mock('Lunaweb\EmailVerification\Contracts\CanVerifyEmail');
        $user->shouldReceive('getEmailForEmailVerification')->andReturn("josias@montag.info");
        $user->password = 'foo';
        $user->updated_at = Carbon::now();

        $emailVerification = $this->getEmailVerification($mocks = $this->getMocks());
        $creds['token'] = $emailVerification->createToken($user, $creds['expiration']);
        $mocks['users']->shouldReceive('retrieveByCredentials')->once()->with(['email' => 'invalid@email.de'])->andReturn(null);

        $this->assertEquals(EmailVerification::INVALID_USER, $emailVerification->verify($creds, function () {
            //
        }));
    }


    public function testInvalidTokenIsReturnedByVerifyWhenTokenExpired()
    {
        $creds = ['email' => 'josias@montag.info', 'expiration' => time() - 1000];
        $user = m::mock('Lunaweb\EmailVerification\Contracts\CanVerifyEmail');
        $user->shouldReceive('getEmailForEmailVerification')->andReturn("josias@montag.info");
        $user->password = 'foo';
        $user->updated_at = Carbon::now();

        $emailVerification = $this->getEmailVerification($mocks = $this->getMocks());
        $creds['token'] = $emailVerification->createToken($user, $creds['expiration']);
        $mocks['users']->shouldReceive('retrieveByCredentials')->once()->with(['email' => 'josias@montag.info'])->andReturn($user);

        $this->assertEquals(EmailVerification::EXPIRED_TOKEN, $emailVerification->verify($creds, function () {
            //
        }));
    }


    protected function getEmailVerification($mocks)
    {
        return new EmailVerification($mocks['users'], $mocks['key'], $mocks['expiration']);
    }

    protected function getMocks()
    {
        $mocks = [
            'users' => m::mock('Illuminate\Contracts\Auth\UserProvider'),
            'key' => 'secret',
            'expiration' => 10,
        ];
        return $mocks;
    }

}