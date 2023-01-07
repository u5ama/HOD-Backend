<?php

namespace Modules\Business\Models;

use Illuminate\Database\Eloquent\Model;

class Website extends Model{

    protected $table = 'website_master';

 protected $fillable = [
     'website', 'business_id', 'google_analytics', 'google_analytics_deleted', 'ga_connected', 'google_adwords_deleted', 'gad_connected'
 ];
    protected $primaryKey = 'website_id';

}
