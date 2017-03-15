<?php
/**
 * (c) Lunaweb Ltd. - Josias Montag
 * Date: 14.03.17
 * Time: 12:41
 */

namespace Lunaweb\EmailVerification\Traits;

use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Lunaweb\EmailVerification\EmailVerification;

trait VerifiesEmail
{




    /**
     * Verifies the given user's email.
     *
     * @param \Illuminate\Http\Request|Request $request
     * @param EmailVerification $emailVerification
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(Request $request, EmailVerification $emailVerification)
    {
        $this->validate($request, $this->rules(), $this->validationErrorMessages());
        // Here we will attempt to reset the user's password. If it is successful we
        // will update the password on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $response = $emailVerification->verify(
            $this->credentials($request), function ($user) {
            $this->verifiedEmail($user);
        }
        );


        // If the password was successfully reset, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $response == EmailVerification::VERIFIED
            ? $this->sendVerificationResponse($response)
            : $this->sendVerificationFailedResponse($request, $response);
    }
    /**
     * Get the password reset validation rules.
     *
     * @return array
     */
    protected function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'expiration' => 'required|date_format:U'
        ];
    }
    /**
     * Get the password reset validation error messages.
     *
     * @return array
     */
    protected function validationErrorMessages()
    {
        return [];
    }
    /**
     * Get the email verification credentials from the request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    protected function credentials(Request $request)
    {
        return $request->only(
            'email', 'expiration', 'token'
        );
    }
    /**
     * Store the user's verification
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword  $user
     * @return void
     */
    protected function verifiedEmail($user)
    {
        $user->forceFill([
            'verified' => true
        ])->save();
        $this->guard()->login($user);
    }
    /**
     * Get the response for a successful password reset.
     *
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendVerificationResponse($response)
    {
        return redirect($this->redirectPath())->with('success', trans($response));
    }
    /**
     * Get the response for a failed password reset.
     *
     * @param  \Illuminate\Http\Request
     * @param  string  $response
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendVerificationFailedResponse(Request $request, $response)
    {
        return redirect($this->redirectPath())->with('error', trans($response));
    }



}