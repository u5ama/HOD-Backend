<?php

namespace Modules\ThirdParty\Models;

use Illuminate\Database\Eloquent\Model;

class UserIssues extends Model
{
    protected $table = 'user_issues';

    protected $fillable = [
        'user_id', 'issue_id', 'business_id', 'third_party_id', 'social_media_id', 'module_type'
    ];

    public function deleteUserIssues($id, $user)
    {
        return DB::table('user_issues')
            ->where('id', $id)
            ->where('user_id', $user)
            ->update(
                [
                    'is_deleted' => 1
                ]
            );
    }

    public function userSpecificIssue($userId, $businessId, $issue)
    {
        $result = UserIssues::where(
            [
                'user_id' => $userId,
                'issue_id' => $issue,
                'business_id' => $businessId,
            ]
        )->first();

        return $result;
    }

    public function businessStatsIssue($userId){

        $userStats = DB::table('user_issues As us')
            ->leftJoin('sys_issue As si', 'us.issue_id', 'si.issue_id')
            ->leftJoin('third_party_master AS tpm', 'us.third_party_id', 'tpm.third_party_id')
            ->select('si.site','tpm.name','us.issue_id','si.title')
            ->where('us.user_id', $userId['id'])
            ->get();

        return $userStats;

        ;

    }


}
