<?php
/**
 * (c) Lunaweb Ltd. - Josias Montag
 * Date: 14.03.17
 * Time: 12:41
 */

namespace Lunaweb\EmailVerification\Traits;

use Illuminate\Foundation\Auth\RedirectsUsers;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Lunaweb\EmailVerification\EmailVerification;

trait VerifiesEmail
{

    /**
     * Validate the request params
     *
     * @param \Illuminate\Http\Request|Request $request
     * @return void
     */
    public function validateVerificationRequest(Request $request)
    {
        $this->validate($request, [
            'token' => 'required',
            'email' => 'required|email',
            'expiration' => 'required|date_format:U'
        ], []);
        
        );
    };

    /**
     * Verifies the given user's email.
     *
     * @param \Illuminate\Http\Request|Request $request
     * @param EmailVerification $emailVerification
     * @return \Illuminate\Http\RedirectResponse
     */
    public function verify(Request $request, EmailVerification $emailVerification)
    {
        $this->validateVerificationRequest($request);
        
        // Here we will attempt to verify the user. If it is successful we
        // will update the verified on an actual user model and persist it to the
        // database. Otherwise we will parse the error and return the response.
        $response = $emailVerification->verify(
            $request->only(
                'email', 'expiration', 'token'
            ), function ($user) {
            return $this->verifiedEmail($user);
        }
        );


        // If the user was successfully verified, we will redirect the user back to
        // the application's home authenticated view. If there is an error we can
        // redirect them back to where they came from with their error message.
        return $response == EmailVerification::VERIFIED
            ? $this->sendVerificationResponse($response)
            : $this->sendVerificationFailedResponse($request, $response);
    }


    /**
     * Show form to the user which allows resending the verification mail
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function showResendVerificationEmailForm()
    {
        $user = Auth::user();
        return view('emailverification::resend', ['verified' => $user->verified, 'email' => $user->email]);
    }

    /**
     * Resend the verification mail
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function resendVerificationEmail(Request $request)
    {
        $user = Auth::user();

        $this->validate($request, [
            'email' => 'required|email|max:255|unique:users,email,' . $user->id
        ]);
        $user->email = $request->email;
        $user->save();

        $sent = resolve('Lunaweb\EmailVerification\EmailVerification')->sendVerifyLink($user);
        Session::flash($sent == EmailVerification::VERIFY_LINK_SENT ? 'success' : 'error', trans($sent));

        return redirect($this->redirectPath());
    }

    /**
     * Store the user's verification
     *
     * @param  \Illuminate\Contracts\Auth\CanResetPassword $user
     * @return boolean
     */
    protected function verifiedEmail($user)
    {
        $this->guard()->login($user);

        if(!$user->verified) {
            $user->forceFill([
                'verified' => true
            ])->save();
            return true;
        }

        return false;
    }

    /**
     * Get the response for a successful user verification.
     *
     * @param  string $response
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendVerificationResponse($response)
    {
        return redirect($this->redirectPath())->with('success', trans($response));
    }

    /**
     * Get the response for a failed user verification.
     *
     * @param  \Illuminate\Http\Request
     * @param  string $response
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function sendVerificationFailedResponse(Request $request, $response)
    {
        return redirect($this->redirectPath())->with('error', trans($response));
    }


}
