<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens, SoftDeletes;

    public function findForPassport($username)
    {
        if(filter_var($username, FILTER_VALIDATE_EMAIL)){
            return $this->where('email', $username)->first();
        }
        return $this->where('username', $username)->first();
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    protected $dates = ['deleted_at'];


    protected $fillable = [
        'name', 'username', 'email', 'email_verified_at', 'password', 'type', 'blocked', 'photo_url', 'last_shift_start', 'last_shift_end', 'shift_active', 'activation_token',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'activation_token'
    ];

    public function isManager(){
        return $this->type == 'manager';
    }
    public function isCook(){
        return $this->type == 'cook';
    }
    public function isCashier(){
        return $this->type == 'cashier';
    }
    public function isWaiter(){
        return $this->type == 'waiter';
    }
    public function meals()
    {
        return $this->hasMany(Meal::class, 'responsible_waiter_id', 'id')
            ->orderByRaw("FIELD(state, \"active\", \"terminated\", \"paid\", \"not paid\")")
            ->orderBy('start', 'desc');
    }
}
