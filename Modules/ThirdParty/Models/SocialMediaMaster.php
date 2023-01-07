<?php

namespace Modules\ThirdParty\Models;

use Illuminate\Database\Eloquent\Model;
use DB;

class SocialMediaMaster extends Model
{
    protected $table = 'social_media_master';

    protected $fillable = [
        'business_id', 'followers', 'type','access_token','page_access_token', 'page_id', 'name', 'page_url','add_review_url', 'average_rating', 'page_reviews_count', 'page_likes_count', 'website', 'phone', 'street', 'city','zipcode','country','cover_photo', 'profile_photo', 'is_manual_deleted', 'is_manual_connected'
    ];

    // protected $hidden = ['access_token', 'page_access_token','business_id'];

    /**
     * Get data from user issue and third party apis
     *
     * @param $id
     * @param string $type
     * @return mixed
     */
    public function SocialApiResponse($id, $type = '')
    {
        /**
         * union
         * union two queries, use union to append last record
         *
         * $first query get data from user issue where third party id is null like
         * where third party api data not received and we affect user issues.
         * this will append data at last.
         */

        $first = DB::table('user_issues As us')
            ->leftJoin('sys_issue As si', 'us.issue_id', 'si.issue_id')
            ->leftJoin('social_media_master AS tpm', 'us.social_media_id', 'tpm.id')
            ->select('si.site', 'tpm.name', 'us.id', 'us.business_id', 'user_id', 'us.issue_id', 'us.social_media_id', 'si.title', 'tpm.phone', 'tpm.website', 'tpm.street', 'tpm.city','tpm.zipcode','tpm.country','tpm.page_url', 'tpm.page_reviews_count', 'tpm.page_likes_count')
            ->where('us.business_id', $id)
            ->where('si.module', 'Social Media')
            ->whereNull('us.third_party_id');

        $second = DB::table('social_media_master AS tpm')
            ->leftJoin('user_issues As us', 'tpm.id', 'us.social_media_id')
            ->leftJoin('sys_issue As si', 'us.issue_id', 'si.issue_id')
            ->select('tpm.type', 'tpm.name', 'us.id', 'tpm.business_id', 'us.user_id', 'us.issue_id', 'tpm.id as social_media_id', 'si.title', 'tpm.phone', 'tpm.website', 'tpm.street', 'tpm.city','tpm.zipcode','tpm.country', 'tpm.page_url','tpm.page_reviews_count', 'tpm.page_likes_count')
            ->where('tpm.business_id', $id);

        if($type != '') {
            $first->Where('si.site', $type);
            $second->Where('tpm.type', $type);
        }

        $result = $second->union($first)->orderby('issue_id')
            ->get();

        return $result;
    }
}
