<?php

namespace Modules\GoogleAdwords\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\GoogleAdwords\Entities\GoogleAdwordEntitiy;
use Redirect;

class GoogleAdwordsController extends Controller
{
    protected $googleAdsEntity;

    public function __construct()
    {
        $this->googleAdsEntity = new GoogleAdwordEntitiy();
    }

    public function getLogin(Request $request)
    {
        $url =  $this->googleAdsEntity->getLogin($request);
        return response()->json(compact('url'));
    }

    public function callback(Request $request)
    {
        $url = $this->googleAdsEntity->getAccessToken($request);
        return Redirect::to($url);
    }

    public function AdsAccounts(Request $request)
    {
        $accounts =  $this->googleAdsEntity->getAccounts($request);
        return response()->json(compact('accounts'));
    }

    public function getAdsWebProperties(Request $request)
    {
        $accounts =  $this->googleAdsEntity->getWebProperties($request);
        return response()->json(compact('accounts'));
    }

    public function CampaignService(Request $request)
    {
        $data = $this->googleAdsEntity->CampaignService($request);
        return response()->json(compact('data'));
    }

    public function getAllAdsData(Request $request){
        $data = $this->googleAdsEntity->getAllAdsData($request);
        return response()->json(compact('data'));
    }

    public function getAdsAllData(Request $request){
        $data = $this->googleAdsEntity->getAdsAllData($request);
        return response()->json(compact('data'));
    }

    public function getAdsStatData(Request $request){
        $data = $this->googleAdsEntity->getStatsData($request);
        return response()->json(compact('data'));
    }

    public function adsSpendWidget(Request $request){
        $data = $this->googleAdsEntity->widgetsGraphs($request);
        return response()->json(compact('data'));
    }
}
