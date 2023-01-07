<?php

namespace Modules\GoogleAdwords\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleAdwordsMaster extends Model
{
    protected $table = 'google_adwords_master';

    protected $fillable = ['business_id', 'access_token', 'profile_id', 'name', 'website', 'type'];
}
