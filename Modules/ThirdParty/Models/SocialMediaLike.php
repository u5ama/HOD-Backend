<?php
/**
 * Created by PhpStorm.
 * User: Wahab
 * Date: 12/26/2017
 * Time: 1:04 PM
 */

namespace Modules\ThirdParty\Models;
use Illuminate\Database\Eloquent\Model;

class SocialMediaLike extends Model
{

    protected $table = 'social_media_like';

    protected $fillable = [
        'like_date', 'count', 'social_media_id'
    ];
}