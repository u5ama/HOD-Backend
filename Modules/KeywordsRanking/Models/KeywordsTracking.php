<?php

namespace Modules\KeywordsRanking\Models;

use Illuminate\Database\Eloquent\Model;

class KeywordsTracking extends Model
{
    protected $table = 'keywords_stats_tracking';
    protected $fillable = ['user_id', 'keyword_id', 'rank', 'change', 'date'];
}
