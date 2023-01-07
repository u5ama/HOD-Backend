<?php

namespace Modules\Business\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Business\Entities\BusinessEntity;
use Modules\Business\Entities\WebsiteEntity;
use Modules\Business\Models\Business;
use Modules\CRM\Entities\CRMEntity;
use Modules\CRM\Entities\GetReviewsEntity;
use Modules\GoogleAdwords\Entities\GoogleAdwordEntitiy;
use Modules\GoogleAnalytics\Entities\GoogleAnalyticsEntity;
use Modules\ThirdParty\Entities\ContentDiscoveryEntity;
use Modules\ThirdParty\Entities\SocialEntity;
use Modules\ThirdParty\Entities\ThirdPartyEntity;
use Modules\ThirdParty\Entities\TripAdvisorEntity;
use Modules\ThirdParty\Models\ThirdPartyMaster;
use Modules\User\Entities\UserEntity;
use Tymon\JWTAuth\Facades\JWTAuth;
use Log;
class CommonController extends Controller
{
    protected $data;

    protected $thirdPartyObj;

    protected $socialEntity;

    protected $contentEntity;

    protected $userEntity;

    protected $businessEntity;

    protected $reviewEntity;

    protected $campaignEntity;

    protected $adminCampaignEntity;

    protected $adminTaskEntity;

    protected $adminPromotionEntity;

    protected $adminBusinessEntity;

    protected $promotionEntity;

    protected $crmEntity;

    protected $taskEntity;

    protected $googleAnalyticsEntity;

    protected $googleAdwordsEntity;

    public function __construct()
    {
        $this->thirdPartyObj = new ThirdPartyEntity();
        $this->socialEntity = new SocialEntity();
        $this->contentEntity = new ContentDiscoveryEntity();
        $this->userEntity = new UserEntity();
        $this->businessEntity = new BusinessEntity();
        $this->reviewEntity = new GetReviewsEntity();
        $this->crmEntity = new CRMEntity();
        $this->googleAnalyticsEntity = new GoogleAnalyticsEntity();
        $this->googleAdwordsEntity = new GoogleAdwordEntitiy();
    }

    public function getConnectionsData(Request $request){

            $allData = ThirdPartyMaster::where(['business_id'=> $request->business_id])->first();
            return response()->json(compact('allData'),200);
    }

    public function ajaxRequestManager(Request $request)
    {
        $businessObj = new BusinessEntity();
        $userObj = new UserEntity();
        $webObj = new WebsiteEntity();
        $tripObj = new TripAdvisorEntity();

        if ($request->input('token')){
            JWTAuth::setToken($request->input('token'));
            $userData = JWTAuth::toUser();
        }
        if ($request->get('send') == 'status-generate') {
            $result = $userObj->updateSession($request);
        } else if ($request->get('send') == 'add-patient-customer') {
            $result = $this->crmEntity->addPatientCustomer($request);
        } else if ($request->get('send') == 'edit-patient-customer') {
            $result = $this->crmEntity->editPatientCustomer($request);
        } elseif ($request->get('send') == 'business-process') {
            $result = $businessObj->collectBusinessData($request);
        } elseif ($request->get('send') == 'web-process') {
            $result = $webObj->trackWebsiteStatus($request);

            if ($result['_metadata']['outcomeCode'] == 200) {
                if (!empty($userData)) {
                    $businessData = Business::where('user_id', $userData['id'])->first();

                    if (!empty($businessData)) {
                        $businessData->update(
                            [
                                'discovery_status' => 6
                            ]
                        );
                    }
                }
            }
        } elseif ($request->get('send') == 'reviews-process') {
            $result = $tripObj->SaveHistoricalReviews($request);

            if ($result['_metadata']['outcomeCode'] == 200) {
                if (!empty($userData)) {
                    $businessData = Business::where('user_id', $userData['id'])->first();

                    if (!empty($businessData)) {
                        $businessData->update(
                            [
                                'discovery_status' => 1
                            ]
                        );
                    }
                }
            }
        } elseif ($request->get('send') == 'manual-connect-business') {
            if (!empty($request->type)) {
                if ($request->type == 'facebook') {
                   // $socialToken = $request->get('accessToken');
                    $data = [];
                    $data['access_token'] = $request->get('accessToken');

                    $request->merge($data);
                    $result = $this->socialEntity->manageSocialBusinessPages($request, 'facebook');
                } elseif ($request->type == 'vitals') {
                    $result = [];
                } else {
                    $result = $businessObj->thirdPartyConnect($request);
                    Log::info("res" . json_encode($result));
                }
            } else {
                $result = [];
            }
        } elseif ($request->get('send') == 'unlink-app') {
            if (!empty($request->type)) {
                if ($request->type == 'Google Analytics') {
                    $result = $this->googleAnalyticsEntity->removeGoogleAnalytics($request);
                }else if ($request->type == 'Google Adwords') {
                    $result = $this->googleAdwordsEntity->removeGoogleAds($request);
                } else {
                    $result = $this->thirdPartyObj->removeThirdPartyBusiness($request);
                }
            } else {
                $result = [];
            }
        } elseif ($request->get('send') == 'deactivate-account') {
            $request->merge(['email' => $userData['email']]);
            $result = $this->userEntity->deactivateUserAccount($request);
        }elseif($request->get('send') == 'delete-account') {
            $result = $this->userEntity->deleteUserAccount($request);
        } elseif($request->get('send') == 'admin-change-user-account-status')
        {
            if($request->get('status') == 'deleted')
            {
                $request->merge(
                    [
                        'delete_by' => 1,
                    ]);
            }
            $result = $this->userEntity->changeUserAccountStatus($request);
        }elseif($request->get('send') == 'super-login') {
            $result = $this->userEntity->superLogin($request);
        }
        elseif ($request->get('send') == 'content-research') {
            $result = $this->contentEntity->getSocialViralContent($request);
        } elseif ($request->get('send') == 'save-feedback') {
            $result = $this->reviewEntity->saveFeedback($request);
        } elseif ($request->get('send') == 'user-profile') {
            $request->merge(['email' => $userData['email']]);
            $result = $this->userEntity->userProfileUpdate($request);

            if ($result['_metadata']['outcomeCode'] == 200) {
                $userData['first_name'] = $request->first_name;
                $userData['last_name'] = $request->last_name;
                $userData['business'][0]['phone'] = $request->phone;
            }
        } elseif ($request->get('send') == 'update-business-profile') {
            $result = $this->businessEntity->businessProfileUpdate($request);
            if ($result['_metadata']['outcomeCode'] == 200) {
                $userData['business'] = $result['records'];
            }
        } elseif ($request->get('send') == 'social-profile') {
            $result = $this->businessEntity->socialProfileUpdate($request);

            if ($result['_metadata']['outcomeCode'] == 200) {
                $userData['business'][0]['phone'] = $request->phone;
            }
        } elseif ($request->get('send') == 'web-report') {
            $data = '';
            try {
                $apiENV = config::get('apikeys.APP_ENV');

                Log::info("apiENV $apiENV");

                if ($apiENV != 'local') {
                    $baseUriHost = 'https';
                } else {
                    $baseUriHost = 'http';
                }

                $baseUriHost = 'http';

                Log::info("url host " . $baseUriHost);

                $client = new Client();
                $websiteUrl = $request->website;

                $response = $client->get($baseUriHost . '://reviewer.nichepractice.com/domains&getImage&site=' . $websiteUrl, [])->getBody()->getContents();

                if (!empty($response)) {
                    $code = 200;
                    $data = $response;
                } else {
                    $code = 404;
                }
            } catch (Exception $e) {
                $code = $e->getCode();
                Log::info("exception web report " . $e->getMessage());
            }

            $statusData = [
                'status_code' => $code,
                'status_message' => "",
                'data' => $data,
                'errors' => ''
            ];

            return json_encode($statusData);
        } elseif ($request->get('send') == 'retrieve-tabs-task') {
            $result = $this->taskEntity->list($request, $userData['id']);
        } elseif ($request->get('send') == 'task-detail') {
            $result = $this->taskEntity->taskDetail($request);
        } elseif ($request->get('send') == 'update-task-status') {
            $result = $this->taskEntity->updateTaskStatus($request);
        }

        if (!empty($result)) {
            $statusData = [
                'status_code' => $result['_metadata']['outcomeCode'],
                'status_message' => $result['_metadata']['message'],
                'data' => $result['records'],
                'errors' => $result['errors']
            ];
        } else {
            $statusData = [
                'status_code' => 1,
                'status_message' => 'Problem in connecting your app',
                'data' => [],
                'errors' => []
            ];
        }

        return json_encode($statusData);
    }
}
