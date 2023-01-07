<?php

namespace Modules\ThirdParty\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\ThirdParty\Entities\DashboardEntity;
use Modules\ThirdParty\Entities\ThirdPartyEntity;

class ThirdPartyController extends Controller
{
    public function thirdPartyReviewsStats(Request $request){
        $dash = new DashboardEntity();
        $myData = $dash->getGraphStatsCount($request);
        return response()->json(compact('myData'));
    }
}
