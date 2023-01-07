<?php

namespace Modules\ThirdParty\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class TripadvisorMaster extends Model
{
    protected $table = 'third_party_master';

    protected $primaryKey = 'third_party_id';

    protected $fillable = [
        'business_id', 'type','name','location_id', 'page_url','add_review_url','review_count','average_rating','website','phone','fax','street','city','zipcode','state','country', 'is_manual_connected', 'is_manual_deleted'
    ];

    /**
     * Local Marketing, all functionality included except
     * get business issues which face by compare third party apis
     * @param $id  = businessId
     * @param string $type
     * @param string $module (Local Marketing, all, website)
     * @return mixed
     */
    /**
     * @param $id
     * @param string $type
     * @param string $module (Local Marketing, Website)
     * @return mixed
     */
    public function businessApiResponse($id, $type = '', $module = 'Local Marketing')
    {
        /**
         * union
         * usiion two queries, use union to append last record
         *
         * $first query get data from user issue where third party id is null like
         * where third party api data not received and we affect user issues.
         * this will append data at last.
         */
        $first = DB::table('user_issues As us')
            ->leftJoin('sys_issue As si', 'us.issue_id', 'si.issue_id')
            ->leftJoin('third_party_master AS tpm', 'us.third_party_id', 'tpm.third_party_id')
            ->select('si.site', 'tpm.name', 'us.id', 'us.business_id', 'user_id', 'us.issue_id', 'us.third_party_id', 'si.title', 'tpm.phone', 'tpm.website', 'tpm.street', 'tpm.page_url', 'tpm.average_rating', 'tpm.review_count')
            ->where('us.business_id', $id);

        $second = DB::table('third_party_master AS tpm')
            ->leftJoin('user_issues As us', 'tpm.third_party_id', 'us.third_party_id')
            ->leftJoin('sys_issue As si', 'us.issue_id', 'si.issue_id')
            ->select('tpm.type', 'tpm.name', 'us.id', 'us.business_id', 'us.user_id', 'us.issue_id', 'tpm.third_party_id', 'si.title', 'tpm.phone', 'tpm.website', 'tpm.street', 'tpm.page_url', 'tpm.average_rating', 'tpm.review_count')
            ->where('tpm.business_id', $id);

        if($module == 'all') {
            $first->where('si.module', '!=', 'Social Media');
        }
        else
        {
            $first->where('si.module', $module);
        }

        $first->whereNull('us.third_party_id');

        if($type != '') {
            $first->where('si.site', $type);
            $second->where('si.site', $type);
        }

        $result = $second->union($first)->orderby('type')->orderby('issue_id')->get();

        return $result;
    }

    public function delThirdPartyBusiness($businessId, $type)
    {
        TripadvisorMaster::where
        (
            [
                'business_id' => $businessId,
                'type' => $type
            ]
        )->delete();

    }
}
