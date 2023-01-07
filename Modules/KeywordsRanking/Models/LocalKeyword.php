<?php

namespace Modules\KeywordsRanking\Models;

use Illuminate\Database\Eloquent\Model;

class LocalKeyword extends Model
{
    protected $table = 'local_keywords';
    protected $fillable = [ 'user_id','keyword','keyword_id','volume','rank','rank_status','search_engine','date','created_at','updated_at'];
}
