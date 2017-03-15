<?php
/**
 * (c) Lunaweb Ltd. - Josias Montag
 * Date: 15.03.17
 * Time: 17:24
 */

namespace Lunaweb\EmailVerification\Events;

use Illuminate\Queue\SerializesModels;

class EmailVerificationSent
{

    use SerializesModels;
    /**
     * The authenticated user.
     *
     * @var \Illuminate\Contracts\Auth\Authenticatable
     */
    public $user;
    /**
     * Create a new event instance.
     *
     * @param  \Illuminate\Contracts\Auth\Authenticatable  $user
     * @return void
     */
    public function __construct($user)
    {
        $this->user = $user;
    }

}