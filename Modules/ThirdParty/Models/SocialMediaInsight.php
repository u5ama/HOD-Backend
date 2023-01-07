<?php
/**
 * Created by PhpStorm.
 * User: Wahab
 * Date: 12/26/2017
 * Time: 1:04 PM
 */

namespace Modules\ThirdParty\Models;
use Illuminate\Database\Eloquent\Model;

class SocialMediaInsight extends Model
{

    protected $table = 'social_media_insight';

    protected $fillable = [
        'social_media_id','type', 'count','activity_date'
    ];
}