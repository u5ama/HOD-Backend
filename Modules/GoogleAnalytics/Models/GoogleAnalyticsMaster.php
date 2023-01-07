<?php

namespace Modules\GoogleAnalytics\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleAnalyticsMaster extends Model
{
    protected $fillable = [
        'business_id','profile_id','access_token' ,'name','website','type'
    ];

    protected $table = 'google_analytics_master';
}
