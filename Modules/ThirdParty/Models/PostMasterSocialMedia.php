<?php


namespace Modules\ThirdParty\Models;
use Illuminate\Database\Eloquent\Model;

class PostMasterSocialMedia extends Model
{
    protected $table = 'post_master_social_media';

    protected $fillable = [
        'id','business_id','post_master_id','social_media_type','created_at','updated_at'
    ];
}

