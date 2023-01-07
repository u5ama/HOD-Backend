<?php

namespace Modules\GoogleAdwords\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleAdwordsStats extends Model
{
    protected $table = 'google_adwords_stats';

    protected $fillable = ['business_id', 'clicks', 'impressions', 'conversions', 'impression_share', 'adsSpend', 'cost_per_conversions', 'revenue'];
}
