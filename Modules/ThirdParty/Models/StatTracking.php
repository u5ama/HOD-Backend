<?php

namespace Modules\ThirdParty\Models;

use Illuminate\Database\Eloquent\Model;

class StatTracking extends Model
{
    protected $table = 'stat_tracking';

    protected $primaryKey = 'stat_id';

    protected $fillable = [
        'third_party_id', 'user_id', 'google_analytics_id', 'google_adwords_id','recipient_id','review_request_id','promo_id','type', 'site_type', 'count', 'activity_date', 'created_at', 'updated_at'
    ];
}
