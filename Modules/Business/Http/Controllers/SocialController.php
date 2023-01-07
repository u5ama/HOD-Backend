<?php

namespace Modules\Business\Http\Controllers;

use App\Services\SessionService;
use App\Traits\ApiServer;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Business\Entities\BusinessEntity;
use Modules\Business\Entities\WebsiteEntity;
use Modules\Business\Models\Website;
use Modules\ThirdParty\Entities\FacebookEntity;
use Modules\ThirdParty\Entities\SocialEntity;
use Modules\ThirdParty\Entities\ThirdPartyEntity;
use Modules\ThirdParty\Entities\TripAdvisorEntity;
use Exception;
use Modules\ThirdParty\Models\SocialMediaMaster;
use Redirect;
use Log;
//use Storage;
//use File;
use Illuminate\Support\Str;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\File;


class SocialController extends Controller
{
    use ApiServer;

    protected $data;

    protected $businessEntity;

    protected $websiteEntity;

    protected $tripPartyEntity;

    protected $thirdPartyEntity;

    protected $socialEntity;

    protected $facebookEntity;

    protected $sessionService;

    public function __construct()
    {
        $this->businessEntity = new BusinessEntity();
        $this->websiteEntity = new WebsiteEntity();
        $this->tripPartyEntity = new TripAdvisorEntity();
        $this->thirdPartyEntity = new ThirdPartyEntity();
        $this->sessionService = new SessionService();
        $this->socialEntity = new SocialEntity();
        $this->facebookEntity = new FacebookEntity();
    }

    public function socialPosts(Request $request)
    {
        $authResponse = '';
        if ($request->has('accessToken') && $request->get('type') == 'facebook') {
            // set analytics token in session to make request.
            $this->sessionService->setOAuthToken(
                [
                    'businessAccessToken' => $request->get('accessToken'),
                    'accessTokenType' => $request->get('type'),
                ]
            );
            // redirecting to url because we don't want to show query string parameter in url
            return redirect()->to($request->url());
        }
        else if ($request->has('accessToken') && $request->get('type') != '') {
            $authResponse = $request->get('accessToken');
            $this->data['authType'] = $request->get('type');
            $this->data['authCode'] = ( !empty($request->get('code')) ) ? $request->get('code') : '';
            $this->data['authMessage'] = ( !empty($request->get('message')) ) ? $request->get('message') : '';
        }

        $socialToken = $this->sessionService->getOAuthToken();

        $this->data['authResponse'] = $authResponse;
        $this->data['socialToken'] = '';
        if(!empty($socialToken['accessTokenType']) && $socialToken['accessTokenType'] == 'facebook')
        {
            $this->data['socialToken'] = !empty($socialToken['businessAccessToken']) ? 1 : 0;
        }

        $this->data['accessTokenType'] = !empty($socialToken['accessTokenType']) ? $socialToken['accessTokenType'] : '';


        $userData = $this->sessionService->getAuthUserSession();
        $this->data['userData'] = $userData;

        $businessResult = $this->businessEntity->userSelectedBusiness($request);

        try {

            $status=array("schedule","published");


            $data = [
                'status' => $status,
                'businessResult'=> $businessResult
            ];

            $socialMediaPostsData = $this->socialEntity->getSocialMediaPosts($data);

            $this->data['socialMediaPostsData'] = [];

            if ($socialMediaPostsData['_metadata']['outcomeCode'] == 200) {
                $this->data['socialMediaPostsData'] = $socialMediaPostsData['records'];
            }

            return view('layouts.posts', $this->data);
        }
        catch(Exception $e)
        {
            Log::info("socialPosts -> " . $e->getMessage());

            $this->data['message'] = 'Problem in retrieving Posts Page. Please try again later';
            return Redirect::route('home')->withMessage('Problem in accessing Posts Page. Please try again.');
        }
    }


    public function socialMediaSettings(Request $request)
    {
        $authResponse = '';
        $this->data['moduleView'] = 'social_media';
        if ($request->has('accessToken') && $request->get('type') == 'facebook') {
            // set analytics token in session to make request.
            $this->sessionService->setOAuthToken(
                [
                    'businessAccessToken' => $request->get('accessToken'),
                    'accessTokenType' => $request->get('type'),
                ]
            );
            // redirecting to url because we don't want to show query string parameter in url
            return redirect()->to($request->url());
        }
        else if ($request->has('accessToken') && $request->get('type') != '') {
            $authResponse = $request->get('accessToken');
            $this->data['authType'] = $request->get('type');
            $this->data['authCode'] = ( !empty($request->get('code')) ) ? $request->get('code') : '';
            $this->data['authMessage'] = ( !empty($request->get('message')) ) ? $request->get('message') : '';
        }

        $socialToken = $this->sessionService->getOAuthToken();

        $this->data['authResponse'] = $authResponse;
        $this->data['socialToken'] = '';
        if(!empty($socialToken['accessTokenType']) && $socialToken['accessTokenType'] == 'facebook')
        {
            $this->data['socialToken'] = !empty($socialToken['businessAccessToken']) ? 1 : 0;
        }

        $this->data['accessTokenType'] = !empty($socialToken['accessTokenType']) ? $socialToken['accessTokenType'] : '';

        try {
            $userData = $this->sessionService->getAuthUserSession();
            $this->data['userData'] = $userData;

            $businessResult = $this->businessEntity->userSelectedBusiness();

            $socialRequestData = [
                'businessResult'=> $businessResult,
                'social_module_list' => 'all'
            ];
            $socialMediaPostsDataResponseData = $this->socialEntity->getSocialMediaPosts($socialRequestData);

//            print_r($socialMediaPostsDataResponseData);
//            exit;

            $this->data['socialMediaPostsData'] = [];

            if ($socialMediaPostsDataResponseData['_metadata']['outcomeCode'] == 200) {
                $this->data['socialMediaPostsData'] = $socialMediaPostsDataResponseData['records'];
            }
            else
            {
                // All not connected
                $this->data['socialMediaPostsData'] = $socialMediaPostsDataResponseData['errors'];
            }

//            print_r($this->data['socialMediaPostsData']);
//            exit;

            return view('layouts.social-media-settings', $this->data);
        }
        catch(Exception $e)
        {
            print_r($e->getMessage());
            exit;
            $this->data['flag'] = 0;
            $this->data['message'] = 'Problem in retrieving Posts Page. Please try again later';
            return Redirect::route('home')->withInput()->withMessage('Problem in accessing Posts Page. Please try again.');
        }

    }


    public function shareContent(Request $request)
    {
        $userData = $this->sessionService->getAuthUserSession();
        $this->data['userData'] = $userData;
        $this->data['moduleView'] = 'search_content';
        $businessResult = $this->businessEntity->userSelectedBusiness();

        $status = [];


        $data = [
            'status' => $status,
            'businessResult'=> $businessResult
        ];

        $socialMediaPostsData = $this->socialEntity->getSocialMediaPosts($data);

//            print_r($socialMediaPostsData);
//            exit;
        $this->data['socialMediaPostsData'] = [];

        if ($socialMediaPostsData['_metadata']['outcomeCode'] == 200) {
            $this->data['socialMediaPostsData'] = $socialMediaPostsData['records'];
        }

        return view('layouts.search-content', $this->data);
    }



    /**
     * Get Page List
     * @param Request $request
     * @param string $token
     * @return string
     */
    public function socialPageList(Request $request, $token = '')
    {
        $responseCode = 3;
        $statusData = [];

        try {
            if($token == '')
            {
                $token = $this->sessionService->getAuthTokenSession();
            }

            if($request->get('accessToken'))
            {
                $socialToken = $this->sessionService->getOAuthToken();
                $data['access_token'] = $socialToken['businessAccessToken'];


                Log::info(" ss " . $data['access_token']);

//                $data['token'] = $token;
                $data['business_id'] = $businessId = $request->get('business_id');
                $data['page_id'] = '';
                $data['type'] = $type = $request->get('type');


                if(empty($data['access_token']) && $request->has('allowSpecial'))
                {
                    $socialData = SocialMediaMaster::where([
                        'business_id' => $businessId,
                        'type' => $type
                    ])->first();

                    if(!empty($socialData['access_token']))
                    {
                        Log::info("in > " . $socialData['access_token']);
                        $data['access_token'] = $socialData['access_token'];
                    }
                }


                if( $data['type'] == 'facebook' )
                {
                    $actionType = 'Get';
                    $urlAction = '/page-detail';
                }

                $request->merge($data);
                $responseData = $this->facebookEntity->getPageList($request);

                $responseCode = $responseData['_metadata']['outcomeCode'];
                $responseMessage = $responseData['_metadata']['message'];
                $data = $responseData['records'];

                if($responseCode == 404)
                {
                    $this->sessionService->setOAuthToken(['businessAccessToken' => '']);
                }

                $statusData = [
                    'status_code' => $responseCode,
                    'status_message' => $responseMessage,
                    'data' => $data,
                ];
            }
            else
            {
                $statusData = [
                    'status_code' => '3',
                    'status_message' => 'No access for this action. Your access token is missing. Please try again to login with your social app.',
                ];
            }
        }
        catch(Exception $e)
        {
            Log::info(" socialPageList > " . $e->getMessage());
            $statusData = [
                'status_code' => $responseCode,
                'status_message' => 'Some Problem happened. Please try again.'
            ];
        }

        $result = json_encode($statusData);

        return $result;
    }

    public function removeAccessToken()
    {
        $this->sessionService->setOAuthToken(['businessAccessToken' => '']);
    }

    public function deletePost(Request $request)
    {
        // get session data of logged in user.
        $userData = $this->sessionService->getAuthUserSession();

        $response = $this->serveApiRequest()->request('DELETE', 'social-media/delete-social-media-post',
            [
                'query' => [
                    'token' => $userData['token'],
                    'post_id' => $request->get('post_id')
                ]
            ]
        );
        $responseData = json_decode($response->getBody()->getContents(), true);
        return $responseData;
    }

    public function deleteFacebookPost(Request $request)
    {
        $responseData = $this->facebookEntity->deleteSinglePost($request);

        return $responseData;
    }

    public function addSocialMediaPost(Request $request)
    {
        try
        {
            $data=[];

            // get session data of logged in user.
//        $userData = $this->sessionService->getAuthUserSession();

            $fileAttachments = (!empty($_FILES['attach_file']))? $_FILES['attach_file']:'';

//        Log::info("attachents " . json_encode($fileAttachments));
//        Log::info("attachents 01" . json_encode($request->file('attach_file')));
//        Log::info("attachents 02");
//        Log::info($request->file);



            $imageArray = [];
            if ($request->file('attach_file')) {
                Log::info("yes");
                foreach ($request->file('attach_file') as $index => $files) {
                    Log::info("index $index");
                    Log::info("$files $files");
                    $imageArray[] = [
                        'name' => "file[$index]",
                        'contents' => file_get_contents($files),
                        'filename' => $files->getClientOriginalName()
                    ];
                }
            }

//            Log::info("imageArray");
//            Log::info($imageArray);

            $details = [];

//        if ($request->details) {
//            foreach ($request->details as $index => $type) {
//                $details['type'][$index] = $type;
//            }
//        }
//        Log::info("details " . json_encode($details));

//        $post_id = [];
//        if ($request->post_id) {
//            $post_id[] = [
//                'name' => "post_id",
//                'contents' => $request->post_id
//            ];
//        }

            $schedule_date = [];
            if ($request->schedule_date) {
                $schedule_date[] = [
                    'name' => "schedule_date",
                    'contents' => $request->schedule_date
                ];
            }

            $deleted_files = [];
            if ($request->deleted_files) {
                foreach ($request->deleted_files as $index => $value) {
                    $deleted_files[] = [
                        'name' => "deleted_files[$index]",
                        'contents' => $value
                    ];
                }
            }

//        $multiPart =
//            [
//                [
//                    'name' => 'status',
//                    'contents' => $request->status
//                ],
//                [
//                    'name' => 'message',
//                    'contents' => $request->message
//                ],
//                [
//                    'name' => 'token',
//                    'contents' => $this->sessionService->getAuthTokenSession()
//                ]
//            ];

//        $finalArray = array_merge($multiPart,$details);
//        $finalizeArray = array_merge($finalArray, $imageArray);
//        $finalizeArray2 = array_merge($finalizeArray, $post_id);
//        $finalizeArray3 = array_merge($finalizeArray2, $schedule_date);
//        $finalizeArray4 = array_merge($finalizeArray3, $deleted_files);

//        $response = $this->serveApiRequest()->request('post', 'api/add-post',
//            [
//                'multipart' => $finalizeArray4
//            ]
//        );

            Log::info("response");

            $data = [
                'status' => $request->status,
                'message' => $request->message,
                $details
            ];
//        $request->merge($imageArray);
            return $this->socialEntity->addPost($request);
//        $responseData = json_decode($response->getBody()->getContents(), true);

//        return $responseData;
        }
        catch(Exception $e)
        {
            Log::info(" addSocialMediaPost > " . $e->getMessage() . ' > ' . $e->getLine());
        }
    }

    public function addPromotionPost(Request $request)
    {
        try
        {
            $data = [
                'status' => $request->status,
                'message' => $request->message,
            ];

            return $this->socialEntity->addPromotionPost($request);
        }
        catch(Exception $e)
        {
            Log::info(" addPromotionPost > " . $e->getMessage() . ' > ' . $e->getLine());
        }
    }

    public function updateFacebookPost(Request $request)
    {
        return $this->facebookEntity->updateSinglePost($request);
    }

    public function getFacebookPostDetail(Request $request)
    {
        return $this->facebookEntity->getSinglePost($request);
    }


//    public function addPost(Request $request)
//    {
//        Log::info("yes");
//        return $this->socialEntity->addPost($request);
//    }
}
