<?php


namespace Modules\ThirdParty\Models;
use Illuminate\Database\Eloquent\Model;

class PostAttachment extends Model
{
    protected $table = 'post_attachment';

    protected $fillable = [
        'post_master_id', 'media_url', 'type','size','ext','created_at','updated_at'

    ];
}

