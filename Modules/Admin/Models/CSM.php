<?php

namespace Modules\Admin\Models;

use Illuminate\Database\Eloquent\Model;

class CSM extends Model
{
    protected $table = 'csm';
    protected $fillable = ['name', 'email', 'phone_number', 'image', 'selected_user_id'];
}
