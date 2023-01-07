<?php

namespace Modules\Business\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\User\Models\User;

class Business extends Model
{
    protected $table = 'business_master';

    protected $primaryKey = 'business_id';

    protected $fillable = ['user_id', 'business_name', 'business_location', 'phone', 'website', 'address', 'business_status', 'discovery_status', 'country_id', 'state', 'city', 'zip_code', 'user_agent', 'targetUrl'];

    public function user()
    {
        return $this->belongsTo(User::class,'user_id','id');
    }

    public function webBusiness()
    {
        return $this->hasOne(Website::class, 'business_id', 'business_id');
    }

    public function country()
    {
        return $this->belongsTo(Countries::class,'country_id','id');
    }

    public function industry()
    {
        return $this->hasOne(Industry::class, 'id', 'industry');
    }

    public function niche()
    {
        return $this->hasOne(Niches::class, 'id', 'niche_id');
    }
}
