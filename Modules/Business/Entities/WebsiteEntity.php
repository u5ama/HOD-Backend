<?php

namespace Modules\Business\Entities;

use App\Entities\AbstractEntity;
use App\Traits\UserAccess;
use GuzzleHttp\Client;
use Modules\Business\Models\Business;
use Modules\Business\Models\Domains;
use Modules\Business\Models\Website;
use Exception;
use Illuminate\Http\Request;
use App\Mail\CreateWebsiteEmail;
use DB;
use Log;
use Config;
use Mail;
use Carbon\Carbon;
use Modules\ThirdParty\Models\UserIssues;
use Modules\ThirdParty\Entities\ThirdPartyEntity;

class WebsiteEntity extends AbstractEntity
{
    use UserAccess;

    protected $businessEntity;

    public function __construct()
    {
        $this->businessEntity = new BusinessEntity();
    }

    /**
     * Get Website Page Speed Result
     *
     * @param $url
     * @return mixed
     */
    public function pageSpeedResult($url)
    {
        try {
            $client = new Client([]);

            $apiKey = config::get('apikeys.googleApi');

            $response = $client->request(
                'GET',
                'https://www.googleapis.com/pagespeedonline/v1/runPagespeed',
                [
                    'query' => [
                        'key' => $apiKey,
                        'url' => $url,
                        'strategy' => 'desktop'
                    ],
                    'verify' => false,
                ]
            );

            if($response->getStatusCode() == 200)
            {
                $responseData = json_decode($response->getBody()->getContents(), true);

                $responseData = array_filter($responseData);

                if($responseData) {
                    return $this->helpReturn("Google Page Speed Response.", $responseData);
                }
            }

            return $this->helpError(404, 'Page Speed Result not found.');
        }
        catch(Exception $e)
        {
            Log::info("pageSpeedResult > " . $e->getMessage());
            return $this->helpError(404, 'Some Problem happened to get result. please try again.');
        }
    }

    /**
     * get Website Mobile Friendly Api Scan Results
     * @param $url
     * @return mixed
     */
    public function mobileFriendlyResult($url)
    {
        try {
            $client = new Client([]);

            $apiKey = config::get('apikeys.googleApi');

            $response = $client->request(
                'GET',
                'https://www.googleapis.com/pagespeedonline/v3beta1/mobileReady',
                [
                    'query' => [
                        'key' => $apiKey,
                        'url' => $url,
                        'strategy' => 'mobile'
                    ],
                    'verify' => false,
                ]
            );

            if($response->getStatusCode() == 200)
            {
                $responseData = json_decode($response->getBody()->getContents(), true);

                $responseData = array_filter($responseData);

                if($responseData) {
                    return $this->helpReturn("Google mobile Response.", $responseData);
                }
            }

            return $this->helpError(404, 'mobile Result not found.');
        }
        catch(Exception $e)
        {
            Log::info(" MobileFriendlyResults >>> " . $e->getMessage());
            return $this->helpError(404, 'Some Problem happened to get result. please try again.');
        }
    }
    /**
     * get Website Google Analytics Scan Results
     *
     * @param $result
     * @return mixed
     */
    public function websiteRecord($result)
    {
        $url = $result['records']['website'];
        $business_id = $result['records']['business_id'];
        $googleAnalyticResult = $this->getGoogleAnalyticResponse($url);
        if ($googleAnalyticResult['_metadata']['outcomeCode'] == 200)
        {
            $google_analytics = 1;
        }
        else
        {
            $google_analytics = 0;
        }
       Website::insert([
            'website' => $url,
            'google_analytics' => $google_analytics,
            'business_id' => $business_id,
        ]);
            return $this->helpReturn("website Record Response.");
    }

    /**
     * get Website Google Analytics Scan Results
     *
     * @param $url
     * @return null
     */
    public function getGoogleAnalyticResponse($url)
    {
        try {
            $client = new Client([]);
            $response = $client->request(
                'GET',
                $url,
                [
                    'verify' => false,
                ]
            );
            if ($response->getStatusCode() == 200) {

                $content = $response->getBody()->getContents();
                $U1_code = false; //FLag for the phrase '_trackPageview'
                $script_regex = "/<script\b[^>]*>([\s\S]*?)<\/script>/i";
                $ua_regex = "/^ua-\d{4,9}-\d{1,4}$/i";

                // Preg Match for Script
                if (!empty($content)) {
                    //3> Extract all the script tags of the content
                    preg_match_all($script_regex, $content, $inside_script);

                    //4> Check for ga.gs and _trackPageview in all <script> tag
                    for ($i = 0; $i < count($inside_script[0]); $i++) {
                        if (stristr($inside_script[0][$i], "UA-")) {
                            $U1_code = TRUE;
                        }
                    }
                    if ($U1_code) {
                        return $this->helpReturn("Google Analytics Installed");
                    }
                }
            }

            return $this->helpError(404, 'Google analytics is not found in requested website.');
        }
        catch (Exception $e) {
            Log::info(" getGoogleAnalyticResponse >>> " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened to run script.');
        }
    }

    /**
     * this method performs some action which generate website issues by communicating with third party Api's
     * to get desired result. 1-3
     *
     * 1) Get Page Speed Result
     * 2) Get Mobile Friendly Result
     * 3) Get Website google analytics result
     *
     * @param Request $request (token)
     * @param compare
     * @return mixed
     */
    public function getWebsiteDetails(Request $request, $reCompare = 0)
    {
        Log::info("re");
        try {
            $businessDetail = $this->businessEntity->userSelectedBusiness();

            if($businessDetail['_metadata']['outcomeCode'] != 200)
            {
                return $businessDetail;
            }

            $businessDetail = $businessDetail['records'];
            $businessId = $businessDetail['business_id'];

            /**
             * Get record from website_master of given business id
             */
            $businessWebsiteRecord = Website::where([
                'business_id' => $businessId
            ])->first();

            return DB::transaction(function () use($request, $reCompare, $businessDetail, $businessWebsiteRecord, $businessId)
            {
                $thirdObj = new ThirdPartyEntity();

                $userId = $businessDetail['user_id'];
                $url = $businessDetail['website'];

                Log::info("user >> $userId");
                if ($url != '') {
                    $data = [];

                    /**
                     * Don't need to re-communicate with Api
                     *if compare flag is 1 because we have already generated data for this.
                     */
                    $compare = ($reCompare == 1) ? 0 : 1;

                    /**
                     * if business record not found in website_master table
                     * which keep track
                     */
                    if( empty($businessWebsiteRecord['business_id']) )
                    {
                        $compare = 0;
                    }
                    else
                    {
                        // $url is business_master url, it should be same in website_master
                        if( getUrlDomain($url) != getUrlDomain($businessWebsiteRecord['website']) )
                        {
                            $compare = 0;
                        }
                    }

                    if($compare == 1)
                    {
                        return $this->helpReturn('Website data already updated.');
                    }
                    else
                    {
                        $pageSpeedScore = '';
                        $mobileReadyScore = '';
                        $data['website'] = $url;


                        $url = getUrlDomain($url);

                        $url = 'http://'.$url;
                        $pageSpeedResult = $this->pageSpeedResult($url);

                        if( $pageSpeedResult['_metadata']['outcomeCode'] == 200 )
                        {
                            $pageSpeedData = $pageSpeedResult['records'];

                            $speedResult = json_encode($pageSpeedData['formattedResults']['ruleResults']);

                            $data['title_tag'] = $pageSpeedData['title'];
                            $data['page_speed_score'] = $pageSpeedData['score'];
                            $data['page_speed_suggestion'] = $speedResult;

                            $pageSpeedScore = $pageSpeedData['score'];
                        }
                        else
                        {
                            $speedResult = NULL;

                            $data['title_tag'] = NULL;
                            $data['page_speed_score'] = NULL;
                            $data['page_speed_suggestion'] = NULL;
                        }

                        $mobileFriendlyResult = NULL;
                        $data['mobile_ready_score'] = 0;
                        $data['mobile_ready'] = 0;
                        $data['mobile_ready_suggestion'] = NULL;
                        $data['google_analytics'] = 0;
                        Website::updateorCreate(
                            ['business_id' => $businessId],
                            $data
                        );

                        $issueData = [
                            [
                                'key' => 'title_tags',
                                'value' => $data['title_tag'],
                                'issue' => 36,
                            ],
                            [
                                'key' => 'page_speed',
                                'value' => $pageSpeedScore,
                                'issue' => 38,
                            ]
                        ];

                        $thirdObj->globalIssueGenerator($userId, $businessId, '', $issueData, 'website', 'website');

                        $delIssueTask = '';

                        if($pageSpeedScore == 100)
                        {
                            $delIssueTask = 38;
                        }

                        if(!empty($delIssueTask))
                        {
                            UserIssues::where(
                                [
                                    'user_id' => $userId,
                                    'issue_id' => $delIssueTask,
                                    'business_id' => $businessId,
                                ]
                            )->delete();
                        }
                    }

                    return $this->helpReturn('Website data saved & issues are generated in System');
                }
                else {
                    // delete business from website_master
                    if( !empty($businessWebsiteRecord['business_id']) )
                    {
                        $businessWebsiteRecord->delete();
                    }
                        //  delete issues of website module.
                        $issueData = [
                            [
                                'key' => 'name',
                                'value' => '',
                                'issue' => 1,
                            ]
                        ];

                    $thirdObj->globalIssueGenerator($userId, $businessId, '', $issueData, 'website', 'website');

                    return $this->helpReturn('Website url not found.');
                }
            });

        } catch (Exception $e) {
            Log::info(" getWebsiteDetails >> " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened to run script');
        }
    }

    public function WebsiteScanResults(Request $request){

        $websiteScanObj = new WebsiteEntity();

        $result = $websiteScanObj->getWebsiteDetails($request);
    }

    public function updateWebsiteAnalysisScore(Request $request)
    {

        try
        {
            $allBusiness = Business::select('business_id')->get();

            $businessWebsiteRecord = DB::table('website_master as wm')
                ->join('business_master as bm', 'wm.business_id', '=', 'bm.business_id')
                ->join('user_master as usm', 'bm.user_id', '=', 'usm.id')
                ->where('wm.business_id', 348)
                ->where('bm.business_profile_status', 'completed')
                ->select('bm.user_id', 'wm.business_id', 'wm.website', 'wm.page_speed_score', 'wm.mobile_ready_score', 'wm.page_speed_suggestion', 'wm.mobile_ready_suggestion', 'wm.mobile_ready', 'wm.google_analytics')
                ->get()->toArray();

          //  dd($businessWebsiteRecord);
            if (!empty ($businessWebsiteRecord))
            {
                foreach ($businessWebsiteRecord as $row)
                {
                    $thirdObj = new ThirdPartyEntity();

                    $url = $row->website;
                    $url = 'http://' . $url;

                    $pageSpeedResult = $this->pageSpeedResult($url);

                    if ($pageSpeedResult['_metadata']['outcomeCode'] == 200)
                    {
                        $pageSpeedData = $pageSpeedResult['records'];

                        $speedResult = json_encode($pageSpeedData['formattedResults']['ruleResults']);

                        $data['title_tag'] = $pageSpeedData['title'];
                        $data['page_speed_score'] = $pageSpeedData['score'];
                        $data['page_speed_suggestion'] = $speedResult;

                        $pageSpeedScore = $pageSpeedData['score'];
                    }
                    else
                    {
                        $speedResult = NULL;

                        $data['title_tag'] = NULL;
                        $data['page_speed_score'] = NULL;
                        $data['page_speed_suggestion'] = NULL;
                    }

                    $mobileResult = $this->mobileFriendlyResult($url);

                    if ($mobileResult['_metadata']['outcomeCode'] == 200)
                    {
                        $mobileData = $mobileResult['records'];
                        $mobileFriendlyResult = json_encode($mobileData['formattedResults']['ruleResults']);

                        $data['mobile_ready_score'] = $mobileData['ruleGroups']['USABILITY']['score'];
                        $data['mobile_ready'] = $mobileData['ruleGroups']['USABILITY']['pass'];
                        $data['mobile_ready_suggestion'] = $mobileFriendlyResult;

                        $mobileReadyScore = $data['mobile_ready_score'];
                    }
                    else
                    {
                        $mobileFriendlyResult = NULL;

                        $data['mobile_ready_score'] = 0;
                        $data['mobile_ready'] = 0;
                        $data['mobile_ready_suggestion'] = NULL;
                    }

                    $googleAnalyticResult = $this->getGoogleAnalyticResponse($url);

                    if ($googleAnalyticResult['_metadata']['outcomeCode'] == 200)
                    {
                        $data['google_analytics'] = 1;
                    }
                    else
                    {
                        $data['google_analytics'] = 0;
                    }

                    Website::where('business_id', $row->business_id)
                        ->where('website', $row->website)
                        ->update(['page_speed_score' => $data['page_speed_score'],
                            'title_tag' => $data['title_tag'],
                            'mobile_ready_score' => $data['mobile_ready_score'],
                            'page_speed_suggestion' => $data['page_speed_suggestion'],
                            'mobile_ready_suggestion' => $data['mobile_ready_suggestion'],
                            'mobile_ready' => $data['mobile_ready'],
                            'google_analytics' => $data['google_analytics'],

                        ]);

                    $issueData = [
                        [
                            'key' => 'title_tags',
                            'value' => $data['title_tag'],
                            'issue' => 36,
                        ],
                        [
                            'key' => 'page_speed',
                            'value' => $data['page_speed_score'],
                            'issue' => 38,
                        ],
                        [
                            'key' => 'mobile_speed',
                            'value' => $data['mobile_ready_score'],
                            'issue' => 37,
                        ],
                        [
                            'key' => 'google_analytics',
                            'value' => $data['google_analytics'],
                            'issue' => 39,
                        ]
                    ];

                    $thirdObj->globalIssueGenerator($row->user_id, $row->business_id, '', $issueData, 'website', 'website');

                    $delIssueTask = '';

                    if ($data['page_speed_score'] == 100)
                    {
                        $delIssueTask = 38;
                    }
                    if ($data['mobile_ready_score'] == 100)
                    {
                        $delIssueTask = 37;
                    }

                    if (!empty($delIssueTask))
                    {
                        UserIssues::where(
                            [
                                'user_id' => $row->user_id,
                                'issue_id' => $delIssueTask,
                                'business_id' => $row->business_id,
                            ]
                        )->delete();
                    }
                }
            }
            return $this->helpReturn("Website results Updated");

        } catch (Exception $e) {
            Log::info(" updateWebsiteAnalysisScore >> " . $e->getMessage());
            return $this->helpError(1, 'Some Problem happened to run script.');
        }
    }

}
