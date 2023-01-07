<?php

namespace Modules\ThirdParty\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\ThirdParty\Entities\GooglePlaceEntity;

class GooglePlaceController extends Controller
{
    protected $googleEntity;
    public function __construct()
    {
        $this->googleEntity = new GooglePlaceEntity();
    }

    /**
     * @param Request $request
     * @return mixed
     * @api {post} /google-place/get-first-place-id [ RF-04-01 ] Google PlaceID
     * @apiVersion 1.0.0
     * @apiName googlePlace Id
     * @apiGroup Google-Place
     * @apiParam {String} name
     * @apiPermission Secured
     * @apiDescription Get the google place id based on the keyword
     */

    public function getFirstPlaceID(Request $request)
    {
        return $this->googleEntity->getFirstPlaceID($request);
    }

    /**
     * @param Request $request
     * @return mixed
     * @api {post} /google-place/get-place-result [ RF-04-02 ] Google Place Business Details
     * @apiVersion 1.0.0
     * @apiName googlePlace details
     * @apiGroup Google-Place
     * @apiParam {String} placeid
     * @apiPermission Secured
     * @apiDescription Get the google place results against the placeid
     */

    public function getPlaceResult(Request $request)
    {
        return $this->googleEntity->getPlaceResult($request);
    }

    public function getBusinessReviews(Request $request)
    {
        return $this->googleEntity->getBusinessReviews($request);
    }

}
