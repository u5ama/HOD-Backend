<?php

namespace Modules\ThirdParty\Models;

use Illuminate\Database\Eloquent\Model;

class ThirdPartyMaster extends Model
{
    protected $table = 'third_party_master';

    protected $primaryKey = 'third_party_id';

    protected $fillable = [
        'business_id', 'type','name','location_id', 'page_url','add_review_url','review_count','average_rating','website','phone','fax','street','city','zipcode','state','country', 'is_manual_connected', 'is_manual_deleted'
    ];

    public function delThirdPartyBusiness($businessId, $type)
    {
        ThirdPartyMaster::where
        (
            [
                'business_id' => $businessId,
                'type' => $type
            ]
        )->delete();

    }
}
