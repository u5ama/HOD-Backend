<?php

namespace Modules\User\Models;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Modules\Business\Models\Business;
use Modules\CRM\Models\Recipient;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'password', 'account_status', 'leaving_subject', 'leaving_note','user_trial'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function userRole()
    {
        return $this->belongsToMany(UserRoles::class, 'user_role_xref', 'user_id', 'role_id');
    }

    public function business()
    {
        return $this->hasMany(Business::class, 'user_id', 'id');
    }

    public function singleBusiness()
    {
        return $this->hasOne(Business::class)->select(['name', 'business_id', 'website', 'user_id']);
    }

    public function recipients()
    {
        return $this->hasMany(Recipient::class);
    }

    public function taskStatus()
    {
        return $this->belongsToMany(Task::class, 'business_task', 'user_id', 'task_id');
    }
}
