<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'username',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function isPending()
    {
        return is_null($this->password);
    }

    public function role()
    {
        return $this->belongsTo('App\Role');
    }

    public function organisations()
    {
        return $this->belongsToMany('App\Organisation');
    }

    public static function findByToken($token)
    {
        return self::where('token', $token)->where('token_generated_at', '>=', Carbon::now()->subHour(24))->firstOrFail();
    }
}