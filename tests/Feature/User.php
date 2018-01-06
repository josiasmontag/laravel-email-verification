<?php
/**
 * Created by Josias Montag
 * Date: 06.01.18 15:43
 * Mail: josias@montag.info
 */

namespace Lunaweb\EmailVerification\Tests\Feature;


use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Notifications\Notifiable;
use Lunaweb\EmailVerification\Contracts\CanVerifyEmail as CanVerifyEmailContract;
use Lunaweb\EmailVerification\Traits\CanVerifyEmail;


class User extends Model implements AuthorizableContract, AuthenticatableContract, CanVerifyEmailContract
{
    use CanVerifyEmail, Authorizable, Authenticatable, Notifiable;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['email', 'verified'];

    public $timestamps = false;
    protected $table = 'users';



}