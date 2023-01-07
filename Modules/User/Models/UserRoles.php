<?php

namespace Modules\User\Models;

use Illuminate\Database\Eloquent\Model;

class UserRoles extends Model
{
    protected $table = 'user_roles';

    protected $guarded = [];

    public function roleUsers()
    {
        return $this->belongsToMany(Users::class, 'user_role_xref', 'role_id', 'user_id');
    }
}
