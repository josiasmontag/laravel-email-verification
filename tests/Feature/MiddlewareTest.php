<?php
/**
 * Created by Josias Montag
 * Date: 06.01.18 15:42
 * Mail: josias@montag.info
 */

namespace Lunaweb\EmailVerification\Tests\Feature;



class MiddlewareTest extends TestCase
{


    public function testVerifiedUser()
    {

        $user =  User::create(['email' => 'test@user.com', 'verified' => true]);

        $response = $this->actingAs($user)->get('/verified');
        $response->assertStatus(200);
        $response->assertSee('ok');

    }


    public function testUnverifiedUser()
    {

        $user = User::create(['email' => 'test@user.com', 'verified' => false]);

        $response = $this->actingAs($user)->get('/verified');
        $response->assertRedirect(route('showResendVerificationEmailForm'));


    }




}
