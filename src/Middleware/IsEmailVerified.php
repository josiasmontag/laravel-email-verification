<?php
/**
 * (c) Lunaweb Ltd. - Josias Montag
 * Date: 14.03.17
 * Time: 14:24
 */

namespace Lunaweb\EmailVerification\Middleware;


use Lunaweb\EmailVerification\Exceptions\UserNotVerifiedException;

class IsEmailVerified
{

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     *
     * @throws Lunaweb\User\Exceptions\UserNotVerifiedException
     */
    public function handle($request, Closure $next)
    {
        if( !is_null($request->user()) && !$request->user()->verified){
            throw new UserNotVerifiedException;
        }

        return $next($request);
    }

}