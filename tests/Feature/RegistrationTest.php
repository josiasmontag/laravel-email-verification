<?php
/**
 * Created by Josias Montag
 * Date: 06.01.18 15:42
 * Mail: josias@montag.info
 */

namespace Lunaweb\EmailVerification\Tests\Feature;



use Illuminate\Support\Facades\Notification;
use Lunaweb\EmailVerification\Notifications\EmailVerification;

class RegistrationTest extends TestCase
{




    public function setUp()
    {
        parent::setUp();


        Notification::fake();


    }


    public function testRegistration() {

        $this->withoutExceptionHandling();


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

        $this->assertFalse((boolean)$user->verified);

        $notification = Notification::sent($user, EmailVerification::class)->first();


        $activationUrl = $notification->toMail($user)->actionUrl;


        $response = $this->get($activationUrl);

        $response->assertRedirect('/home');
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue((boolean)$user->verified);


    }



}