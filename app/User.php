<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use App\Notifications\ResetPassword as ResetPasswordNotification;

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


    public function getActiveFormattedAttribute()
    {
        return $this->active ? 'Active' : 'Suspended';
    }

    public function isPending()
    {
        return is_null($this->password);
    }

    public function createToken()
    {
        $this->token = str_random(30);
        $this->token_generated_at = Carbon::now();

        return $this;
    }

    public function withPassword($password)
    {
        if ($password) {
            $this->password = bcrypt($password);
        }
        return $this;
    }

    public function withRole($role)
    {
        if ($role) {
            $role = Role::findOrFail($role);
            $this->role()->associate($role->id);
        }
        return $this;
    }

    public function addOrganisations($organisations)
    {
        if ($organisations) {
            $this->organisations()->attach($organisations);
        }
    }

    public function role()
    {
        return $this->belongsTo('App\Role');
    }

    public function hasRole($roles)
    {
        if (! is_array($roles)) $roles = [$roles];

        return $this->whereHas('role', function($query) use($roles) {
            $query->whereIn('name', $roles);
        })->count();
    }

    public function organisations()
    {
        return $this->belongsToMany('App\Organisation');
    }

    public static function findByToken($token)
    {
        return self::where('token', $token)->where('token_generated_at', '>=', Carbon::now()->subHour(24))->first();
    }

    /**
     * Send the password reset notification.
     *
     * @param  string $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }
}