<?php
/**
 * Created by Josias Montag
 * Date: 06.01.18 15:42
 * Mail: josias@montag.info
 */

namespace Lunaweb\EmailVerification\Tests\Feature;


use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Lunaweb\EmailVerification\Events\UserVerified;
use Lunaweb\EmailVerification\Notifications\EmailVerification;

class RegistrationTest extends TestCase
{


    public function setUp()
    {
        parent::setUp();


        Notification::fake();


    }


    public function testRegistration()
    {


        $response = $this->json('POST', '/register', [
            'email' => 'josias@montag.info',
            'password' => 'secret',
            'password_confirmation' => 'secret'
        ]);
        $response->assertRedirect('/home');

        $user = User::where('email', 'josias@montag.info')->firstOrFail();

        Notification::assertSentTo(
            [$user], EmailVerification::class
        );

        $this->assertFalse($user->verified);

        $notification = Notification::sent($user, EmailVerification::class)->first();


        $activationUrl = $notification->toMail($user)->actionUrl;


        $response = $this->get($activationUrl);

        $response->assertRedirect('/home');
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue($user->verified);


    }


    public function testEmitsUserVerifedEventOnce()
    {


        Event::fake();

        $user = User::create(['email' => 'test@user.com', 'verified' => false]);

        app(\Lunaweb\EmailVerification\EmailVerification::class)->sendVerifyLink($user);

        $notification = Notification::sent($user, EmailVerification::class)->first();
        $activationUrl = $notification->toMail($user)->actionUrl;

        // Open activation URL first time

        $this->get($activationUrl);

        Event::assertDispatched(UserVerified::class, function ($e) use ($user) {
            return $e->user->is($user);
        });

        $this->assertTrue($user->fresh()->verified);


        // Open activation URL second time

        $this->get($activationUrl);

        $this->assertCount(1, Event::dispatched(UserVerified::class));

        $this->assertTrue($user->fresh()->verified);


    }


}