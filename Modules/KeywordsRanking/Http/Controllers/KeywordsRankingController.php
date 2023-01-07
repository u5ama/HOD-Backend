<?php

namespace Modules\KeywordsRanking\Http\Controllers;

use App\Services\SessionService;
use App\Traits\GlobalErrorHandlingTrait;
use App\Traits\GlobalResponseTrait;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Business\Models\Business;
use Modules\KeywordsRanking\Models\KeywordsTracking;
use Modules\KeywordsRanking\Models\LocalKeyword;
use Modules\KeywordsRanking\Models\SeProjects;
use Modules\User\Models\User;
use Log;
use Exception;
use Tymon\JWTAuth\Facades\JWTAuth;

class KeywordsRankingController extends Controller
{
    use GlobalErrorHandlingTrait,GlobalResponseTrait;
    protected $data = []; // the information we send to the view
    protected $sessionService = '';

    public function __construct()
    {
        $this->sessionService = new SessionService();
    }

    public function keywordsHome(){
        $userData = $this->sessionService->getAdminUserSession();
        $this->data['userData'] = $userData;

        $records = SeProjects::select('user_id', 'project_name', 'project_url', 'project_id')->with(['users' => function ($v) {
            $v->select('id', 'first_name', 'last_name');
        }])->with(['localKeywords' => function ($q) {
            $q->select('user_id', 'keyword');
        }])->get()->toArray();

        $this->data['records'] = $records;
        return view('admin.keywords.allkeywords', $this->data);
    }

    public function addKeywords(){
        $userData = $this->sessionService->getAdminUserSession();
        $this->data['userData'] = $userData;

        $users = User::where('id','!=',1)->whereNull('deleted_at')->get();
        $this->data['users'] = $users;
        return view('admin.keywords.addkeywords', $this->data);
    }


    public function editKeywords($id){
        $userData = $this->sessionService->getAdminUserSession();
        $this->data['userData'] = $userData;

        $records = LocalKeyword::where('user_id', $id)->get()->toArray();
        $this->data['records'] = $records;
        return view('admin.keywords.editKeywords', $this->data);
    }

    public function getKeywordsData(Request $request){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();

        $id = $userData['id'];
        $project = SeProjects::where('user_id', $id)->first();

        if (!empty($project)){
            /*Get Keywords Rank*/
            $url = "https://api4.seranking.com/sites/$project->project_id/positions";
            $token = "3808ea4b51ccbf71b60ffb0e395f4340bda05975";

            $headers = ["Authorization: Token ".$token, "Content-Type: application/json"];
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT, 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "GET",
                CURLOPT_HTTPHEADER => $headers
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $contents = json_decode($response);
        }

        $id = $userData['id'];
        if (count($contents)>0){
            foreach ($contents as $content){
                foreach($content->keywords as $keyword){

                    foreach($keyword->positions as $position){

                        $key = LocalKeyword::where(['user_id' => $id, 'keyword_id' => $keyword->id])->first();
                        if (empty($key->date)){
                            LocalKeyword::where(['user_id' => $id, 'keyword_id' => $keyword->id])->update([
                                'rank' => $position->pos,
                                'date' => $position->date,
                                'volume' => $position->change
                            ]);
                        }else{
                            $crdate = $key->date;
                            $date = Carbon::createFromFormat('Y-m-d H:i:s', $crdate);
                            $daysToAdd = 3;
                            $date = $date->addDays($daysToAdd);

                            $positionDate = Carbon::createFromFormat('Y-m-d', $position->date)->format('Y-m-d');
                            $formattedDate = Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('Y-m-d');

                            if ($formattedDate == $positionDate){
                                LocalKeyword::where(['user_id' => $id, 'keyword_id' => $keyword->id])->update([
                                    'rank' => $position->pos,
                                    'date' => $position->date,
                                    'volume' => $position->change
                                ]);
                            }
                        }
                    }
                }
            }
            $record =   LocalKeyword::where(['user_id' => $id])->get();
            return response()->json(['success' => 'true', 'record' => $record],200);

        }else{
            return response()->json(['success' => 'false', 'error' => 'No record Found'],200);
        }
    }

    public function trackKeywords(Request $request){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();

        $id = $userData['id'];

        $createdDate = $userData['created_at'];
        $createdDate = Carbon::createFromFormat('Y-m-d H:i:s', $createdDate)->format('Y-m-d');

        $project = SeProjects::where('user_id', $id)->first();

        /*Get Keywords Rank*/
        $url = "https://api4.seranking.com/sites/$project->project_id/positions/?date_from=$createdDate";
        $token = "3808ea4b51ccbf71b60ffb0e395f4340bda05975";

        $headers = ["Authorization: Token ".$token, "Content-Type: application/json"];
        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT, 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => $headers
        ));

        $response = curl_exec($curl);
        curl_close($curl);
        $contents = json_decode($response);

        if (count($contents)>0){
            foreach ($contents as $content) {
                foreach ($content->keywords as $keyword) {
                    foreach ($keyword->positions as $position) {
                        $stat = KeywordsTracking::where(['user_id' => $userData['id'], 'keyword_id' => $keyword->id, 'date' => $position->date])->first();
                        if (!empty($stat)){
                            KeywordsTracking::where(['user_id' => $userData['id'], 'keyword_id' => $keyword->id, 'date' => $position->date])->update([
                                'rank' => $position->pos,
                                'change' => $position->change
                            ]);
                        }
                        else{
                            KeywordsTracking::create([
                                'user_id' => $userData['id'],
                                'keyword_id' => $keyword->id,
                                'rank' => $position->pos,
                                'date' => $position->date,
                                'change' => $position->change
                            ]);
                        }
                    }
                }
            }
            return response()->json(['success' => 'true'],200);
        }else{
            return response()->json(['success' => 'false', 'error' => 'No record Found'],200);
        }
    }

    public function deleteKeyword(Request $request){
        $keywordId = $request->get('id');

       // $keywordId = explode(",", $keywordId);
        $user_id = $request->get('user_id');

        $project = SeProjects::where('user_id', $user_id)->first();
        $project_id = $project->project_id;
        try{
            $url = "https://api4.seranking.com/sites/$project_id/keywords?keywords_ids[]=$keywordId";
            $token = "3808ea4b51ccbf71b60ffb0e395f4340bda05975";

            $headers = ["Authorization: Token ".$token, "Content-Type: application/json"];
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT, 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "DELETE",
                CURLOPT_HTTPHEADER => $headers
            ));
            $response = curl_exec($curl);
            curl_close($curl);
            $content = json_decode($response);

            LocalKeyword::where('keyword_id', $keywordId)->delete();
            return response()->json(['success' => 'true', 'project' => $content],200);
        }
        catch (Exception $e) {
            Log::info("SE ranking Project " . $e->getMessage());
            return $this->helpError(2, 'Invalid Parameters');
        }
    }
    /**  Get business data
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function businessData(Request $request){
        $businessData = Business::where('user_id', $request->user_id)->first();
        return response()->json(compact('businessData'));
    }

    /**  add se ranking project
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|mixed
     */

    public function addProject(Request $request){

        try {
            $business_name = $request->get('business_name');
            $business_url = $request->get('business_url');

            /*Add Project*/
            $url = "https://api4.seranking.com/sites";
            $token = "3808ea4b51ccbf71b60ffb0e395f4340bda05975";
            $curl = curl_init($url);
            curl_setopt_array($curl, [
                CURLOPT_HTTPHEADER => ["Authorization: Token ".$token],
                CURLOPT_HEADER => 0,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_POST =>true,
                CURLOPT_POSTFIELDS=>json_encode([
                    "url" => $business_url,
                    "title" => $business_name
                ])
            ]);

            $response = curl_exec($curl);
            curl_close($curl);
            $content = json_decode($response);

            $project = SeProjects::create([
                'user_id' => $request->user_id,
                'project_name' => $business_name,
                'project_url' => $business_url,
                'project_id' => $content->site_id,
            ]);

            /*Add Project*/

            /*Add Search engine*/
            $url = "https://api4.seranking.com/sites/$content->site_id/search-engines";
            $token = "3808ea4b51ccbf71b60ffb0e395f4340bda05975";
            $curl = curl_init($url);
            curl_setopt_array($curl, [
                CURLOPT_HTTPHEADER => ["Authorization: Token ".$token],
                CURLOPT_HEADER => 0,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_POST =>true,
                CURLOPT_POSTFIELDS=>json_encode([
                    "search_engine_id" =>  330,
                ])
            ]);

            $response = curl_exec($curl);
            curl_close($curl);
            $engine = json_decode($response);

            /*Add Search engine*/

            return response()->json(['success' => 'true', 'project' => $project],200);
        }
        catch (Exception $e) {
            Log::info("SE ranking Project " . $e->getMessage());
            return $this->helpError(2, 'Invalid Parameters');
        }
    }

    /**  add se ranking keywords
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|mixed
     */

    public function addUserKeywords(Request $request){

        try {
            $keywords = $request->get('keywords');
            $user_id = $request->get('u_id');

            $project = SeProjects::where('user_id', $user_id)->first();
            $project_id = $project->project_id;

            $url = "https://api4.seranking.com/sites/$project_id/keywords";
            $keywords = explode(",", $keywords);

            foreach ($keywords as $keyword => $key){
                $n[] =  ['keyword' => $key];
            }
            $token = "3808ea4b51ccbf71b60ffb0e395f4340bda05975";
            $curl = curl_init($url);
            curl_setopt_array($curl, [
                CURLOPT_HTTPHEADER => ["Authorization: Token ".$token, "Content-Type: application/json"],
                CURLOPT_HEADER => 0,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_POST =>true,
                CURLOPT_POSTFIELDS=>json_encode($n)
            ]);

            $response = curl_exec($curl);
            curl_close($curl);
            $content = json_decode($response);
            $ids = $content->ids;
            if(count($ids)>0){
                $desired = [];
                foreach ($ids as $key => $k){
                    $desired[$k] = $keywords[$key];
                }
                foreach ($desired as $d => $s){
                  $all = LocalKeyword::create([
                        'user_id' => $user_id,
                        'keyword' => $s,
                        'keyword_id' => $d
                    ]);
                }
            }
            return response()->json(['success' => 'true', 'data' =>  $all],200);
        }
        catch (Exception $e) {
            Log::info("SE ranking Project " . $e->getMessage());
            return $this->helpError(2, 'Invalid Parameters');
        }
    }

    /***
     *
     * deleteProject
     *
     * *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|mixed
     */
    public function deleteProject(Request $request){

        $user_id = $request->get('id');
        $project = SeProjects::where('user_id', $user_id)->first();
        $project_id = $project->project_id;
        try{
            $url = "https://api4.seranking.com/sites/$project_id";
            $token = "3808ea4b51ccbf71b60ffb0e395f4340bda05975";

            $headers = ["Authorization: Token ".$token, "Content-Type: application/json"];
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT, 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "DELETE",
                CURLOPT_HTTPHEADER => $headers
            ));

            $response = curl_exec($curl);
            curl_close($curl);
            $content = json_decode($response);
             return response()->json(['success' => 'true', 'project' => $content],200);
        }
        catch (Exception $e) {
            Log::info("SE ranking Project " . $e->getMessage());
            return $this->helpError(2, 'Invalid Parameters');
        }
    }

    public function keywordImprovements(Request $request){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();

        $id = $userData['id'];

        $keywordsTotal = LocalKeyword::where('user_id', $id)->sum('volume');

        $statusData['total'] = $keywordsTotal;

        $graphStatsQueryCurrent = LocalKeyword::where('user_id', $id);

        $currentMonth = date('m');
        $graphStatsQueryCurrent->where(function ($query) use ($currentMonth) {
            $query->whereRaw('MONTH(date) = ?',[$currentMonth]);
        });

        $graphStatsCurrentMonth = $graphStatsQueryCurrent->select('rank', 'volume')->count();

        $graphStatsQueryLast = LocalKeyword::where('user_id', $id);

        $lastMonth = date('m', strtotime("-1 month"));
        $graphStatsQueryLast->where(function ($query) use ($lastMonth) {
            $query->whereRaw('MONTH(date) = ?',[$lastMonth]);
        });

        $graphStatsLastMonth = $graphStatsQueryLast->select('rank', 'volume')->count();

        $graphStatsQueryTotal = LocalKeyword::where('user_id', $id)->count();

        /*Total Calculations*/
        $total = ($graphStatsCurrentMonth - $graphStatsLastMonth)/$graphStatsQueryTotal;
        $totalPercent = $total*100;

        $statusData['percent'] = $totalPercent;

        return response()->json($statusData, 200);
    }
}
