<?php

namespace Modules\ThirdParty\Http\Controllers;

use App\Traits\GlobalErrorHandlingTrait;
use App\Traits\GlobalResponseTrait;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use GuzzleHttp;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Log;
use Exception;
use App\Traits\UserAccess;
use Modules\ThirdParty\Models\CallMetrics;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\UriInterface;
use Tymon\JWTAuth\Facades\JWTAuth;

class CTMController extends Controller
{
    use GlobalErrorHandlingTrait,GlobalResponseTrait;
    protected $data = [];

    public function loginCTM(Request $request){

            $validator = Validator::make($request->all(), [
                'user' => 'required|string|email|max:255',
                'password' => 'required|string|min:6',
            ]);

            if($validator->fails()){
                return response()->json($validator->errors()->toJson(), 400);
            }

            JWTAuth::setToken($request->input('token'));
            $userData = JWTAuth::toUser();
            $userEmail = $request->get('user');
            $userPassword = $request->get('password');
            try{
                $client = new Client([]);
                $response = $client->request('POST','https://api.calltrackingmetrics.com/api/v1/authentication', [
                    'form_params' => [
                        'user' => $userEmail,
                        'password' => $userPassword,
                    ]
                ]);

                $responseData = json_decode($response->getBody()->getContents(), true);
                $records = $responseData;

                Log::info($records);
                if (!empty($records)){
                       $m = CallMetrics::create([
                            'user_id' => $userData['id'],
                            'email' => $userEmail,
                            'password' => $userPassword,
                        ]);
                    }
                if (!empty($m)){

                    $url = "https://api.calltrackingmetrics.com/api/v1/accounts.json";

                    $username = $m->email;
                    $password = $m->password;

                    $headers = array(
                        'Authorization: Basic '. base64_encode("$username:$password"),
                        'Content-Type: application/json'
                    );
                    Log::info($headers);
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
                    $accounts = json_decode($response);
                    return $this->helpReturn("Login Added Successfully.", $accounts);
                }
               // return response()->json(compact('records'));
            }
            catch (Exception $e) {
                Log::info("CTMloginfailed " . $e->getMessage());
                return $this->helpError(2, 'Invalid Parameters');
            }
    }

    public function getAccountConnection(Request $request){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();

        $accountId = $request->get('account_id');
        if (!empty($accountId)){
            CallMetrics::where('user_id', $userData['id'])->update([
                'account_id' => $accountId,
                'account_name' => $request->get('account_name'),
            ]);
        }
        return $this->helpReturn("Account Added Successfully.");
    }

    public function getCallsData(Request $request){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();

        $rec = CallMetrics:: where('user_id', $userData['id'])->first();

        if (!empty($rec)){
            Log::info("i am here");
            try {

                $account_id = $rec->account_id;
                $email = $rec->email;
                $userPassword = $rec->password;

                $url = "https://api.calltrackingmetrics.com/api/v1/accounts/$account_id/calls";

                $username = $email;
                $password = $userPassword;

                $headers = array(
                    'Authorization: Basic '. base64_encode("$username:$password"),
                    'Content-Type: application/json'
                );
                Log::info($headers);
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
                $callData = json_decode($response);
                $this->data['callData'] = $callData;
                $this->data['account_name'] = $rec->account_name;
                return response()->json(['data' => $this->data]);
            }
            catch (Exception $e) {
                Log::info("CTMResponsefailed " . $e->getMessage());
                return $this->helpError(2, 'Invalid Parameters');
            }
        }else{
            return response()->json(['data' => 'No data found']);
        }
    }

    public function deleteCTM(Request $request){
        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();

        $data = CallMetrics::where('user_id', $userData['id'])->delete();
        return response()->json(compact('data'));
    }
}
