<?php

namespace Modules\Business\Http\Controllers;

use App\Services\SessionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Business\Entities\BusinessEntity;
use Modules\Business\Entities\WebsiteEntity;
use Modules\Business\Models\Countries;
use Modules\CRM\Entities\CRMEntity;
use Modules\CRM\Entities\GetReviewsEntity;
use Modules\CRM\Models\Recipient;
use Modules\ThirdParty\Entities\ThirdPartyEntity;
use Tymon\JWTAuth\Facades\JWTAuth;

class PageController extends Controller
{
    protected $businessEntity;

    protected $crmEntity;

    protected $websiteEntity;

    protected $thirdPartyEntity;

    protected $sessionService;

    protected $reviewEntity;

    protected $campaignEntity;

    protected $promotionEntity;

    protected $data;

    public function __construct()
    {
        $this->businessEntity = new BusinessEntity();
        $this->websiteEntity = new WebsiteEntity();
        $this->thirdPartyEntity = new ThirdPartyEntity();
        $this->sessionService = new SessionService();
        $this->reviewEntity = new GetReviewsEntity();
        $this->crmEntity = new CRMEntity();
    }

    public function company(Request $request)
    {
        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();

        $this->data['userData'] = $userData;

        $this->data['userBusiness'] = $this->businessEntity->userSelectedBusiness($request)['records'];
        $this->data['countries'] = Countries::all();

        $this->data['showAdditionalBar'] = true;
        $data = $this->data;
        return response()->json($data, 200);
    }
}
