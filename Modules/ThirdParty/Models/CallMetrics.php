<?php

namespace Modules\ThirdParty\Models;

use Illuminate\Database\Eloquent\Model;

class CallMetrics extends Model
{
    protected $table = 'call_metrices';
    protected $fillable = ['user_id', 'email', 'password', 'account_id', 'account_name'];
}
