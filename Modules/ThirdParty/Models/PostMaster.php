<?php


namespace Modules\ThirdParty\Models;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PostMaster extends Model
{
    protected $table = 'post_master';


    protected $fillable = [
         'business_id','post_id', 'message','status','schedule','social_media_type','created_at','updated_at'
    ];

    public function postMasterSocialMedia()
    {
        return $this->hasMany(PostMasterSocialMedia::class,'post_master_id','id')
            ->select('id','post_master_id','social_media_type');
        //->select('post_master_id','social_media_type');
    }
    public function attachment()
    {
        return $this->hasMany(PostAttachment::class, 'post_master_id', 'id');
    }

    public function getCreatedAtAttribute($value)
    {

        if(!empty($value)){
        $carbon = new \Carbon\Carbon();
        $date = $carbon->createFromTimestamp(strtotime($value),'EST');
        return $date->format('Y-m-d h:i:s');
        }
   }
    public function getUpdatedAtAttribute($value)
    {
        if(!empty($value)){
        $carbon = new \Carbon\Carbon();
        $date = $carbon->createFromTimestamp(strtotime($value),'EST');
        return $date->format('Y-m-d h:i:s');
        }
   }
}
