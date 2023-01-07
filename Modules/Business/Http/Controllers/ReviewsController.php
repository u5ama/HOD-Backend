<?php

namespace Modules\Business\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Business\Entities\BusinessEntity;
use Modules\Business\Entities\WebsiteEntity;
use Modules\Business\Models\Business;
use Modules\Business\Models\Website;
use Modules\ThirdParty\Entities\ThirdPartyEntity;
use Modules\ThirdParty\Entities\TripAdvisorEntity;
use Tymon\JWTAuth\Facades\JWTAuth;

class ReviewsController extends Controller
{
    protected $data;

    protected $businessEntity;

    protected $websiteEntity;

    protected $tripPartyEntity;

    protected $thirdPartyEntity;

    public function __construct()
    {
        $this->businessEntity = new BusinessEntity();
        $this->websiteEntity = new WebsiteEntity();
        $this->tripPartyEntity = new TripAdvisorEntity();
        $this->thirdPartyEntity = new ThirdPartyEntity();
    }

    public function getReviews(Request $request)
    {
        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $this->data['userData'] = $userData;
        $this->data['reviewsResult'] = $this->thirdPartyEntity->thirdPartyReviews($request);
        $myData  =  $this->data['reviewsResult'];

        return response()->json(compact('myData'));
    }

    public function searchReviews(Request $request){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $this->data['userData'] = $userData;
        $this->data['reviewsResult'] =  $this->thirdPartyEntity->thirdPartyReviewsSearch($request);
        $myData  =  $this->data['reviewsResult'];

        return response()->json(compact('myData'));
    }

    public function googleDetector(Request $request){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $this->data['userData'] = $userData;
        $business = Business::where('user_id', $userData['id'])->first();
        $webData = Website::where('business_id',$business['business_id'])->first();
        return response()->json(compact('webData'));
    }
}
