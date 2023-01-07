<?php
/**
 * Created by Wahab
 * Date: 10/30/2017
 * Time: 2:51 PM
 */

namespace Modules\CRM\Entities;

use App\Entities\AbstractEntity;
use App\Mail\CreateAddReciepentsEmail;
use App\Mail\CreateNagetiveReviewFeedBackEmail;
use Illuminate\Support\Facades\Mail;
use Modules\CRM\Entities\CRMEntity;
use Modules\CRM\Models\CrmSettings;
use Modules\CRM\Services\Validations\Reviews\AddReviewValidator;
use Modules\CRM\Services\Validations\Reviews\FilesReviewValidator;
use App\Traits\UserAccess;
use Illuminate\Http\Request;
use Modules\Business\Models\Business;
use Modules\ThirdParty\Models\TripadvisorMaster;
use Modules\User\Models\User;
use GuzzleHttp\Client;
use Exception;
use Log;
use JWTAuth;
use DB;
use Config;
use Modules\Business\Entities\BusinessEntity;
use Carbon\Carbon;
use Modules\CRM\Models\Recipient;
use Modules\CRM\Models\ReviewRequest;
use Modules\ThirdParty\Models\SocialMediaMaster;
use Modules\ThirdParty\Models\ThirdPartyMaster;
use Storage;
use File;
use Nexmo\Laravel\Facade\Nexmo;
use Bitly;
use Illuminate\Support\Facades\Crypt;

class GetReviewsEntity extends AbstractEntity
{
    use UserAccess;

    protected $addReviewValidator;
    protected $fileReviewValidator;

    protected $businessEntity;
    protected $crmEntity;

    public function __construct()
    {
        $this->businessEntity = new BusinessEntity();
        $this->crmEntity = new CRMEntity();
        $this->addReviewValidator = new AddReviewValidator(resolve('validator'));
        $this->fileReviewValidator = new FilesReviewValidator(resolve('validator'));
    }

    public function addRecipients($request)
    {

        try {
            if (!$this->addReviewValidator->with($request->all())->passes()) {

                return $this->helpError(2, 'Fill required field.', $this->addReviewValidator->errors());
            }

            $businessObj = new BusinessEntity();
            $checkPoint = $this->setCurrentUser($request->get('token'))->userAllow();
            $currentDate = Carbon::now();
            $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('Y-m-d');

            if ($checkPoint['_metadata']['outcomeCode'] != 200) {
                return $checkPoint;
            }

            $user = $checkPoint['records'];
            $Useremail = $user['email'];
            $businessResult = $businessObj->userSelectedBusiness($user);

            if ($businessResult['_metadata']['outcomeCode'] != 200) {
                return $this->helpError(1, 'Problem in selection of user business.');
            }
            $businessId = $businessResult['records']['business_id'];
            $businessName = $businessResult['records']['name'];

            $thirdPartyMaster = TripadvisorMaster::select('name', 'page_url')->where('name', '!=', null)->where('page_url', '!=', null)->where('business_id', $businessId)->get()->toArray();
            $socialMediaMaster = SocialMediaMaster::select('name', 'page_url')->where('name', '!=', null)->where('page_url', '!=', null)->where('business_id', $businessId)->get()->toArray();
            $mergeArray = array_merge($thirdPartyMaster, $socialMediaMaster); //merge both array
            // DB::beginTransaction();
            if (!empty($mergeArray)) { //check third party exist or not
                $record = Recipient::where('email', $request->email)->where('user_id', $user->id)->get();
                $filterNumber = filterPhoneNumber($request->phone_number);
                $phoneRecords = Recipient::where('phone_number', $filterNumber)->where('user_id', $user->id)->get();

                /***********************EMAIL & SMS Both Start******************/
                if (isset($request->type[0]) && $request->type[0] == 'email' && isset($request->type[1]) && $request->type[1] == 'sms') {
                    Log::info('both');

                    if (count($record) != 3) { //section one
                        Log::info('internal email');
                        if ($request->type[0] == 'email') {
                            $response = $this->emailSending($record, $request, $user, $businessId, $businessName, $Useremail, $formatedDate);
                            if ($response['_metadata']['outcomeCode'] == 404) {
                                $internalErr[0] = 'email';
                            }
                        }

                    } else {
                        $email = 'Email';
                    }

                    if (count($phoneRecords) != 3) { //section 2
                        Log::info('yes in');
                        if ($request->type[1] == 'sms') {
                            Log::info('internal sms');

                            $response = $this->sendSms($phoneRecords, $request, $user, $businessId, $businessName, $Useremail, $formatedDate, $flag = 'both');
                            if ($response['_metadata']['outcomeCode'] == 404) {
                                $internalErr[1] = 'sms';
                            }
                        }
                    } else {
                        $sms = 'SMS';
                    }

                    if (isset($sms) && isset($email)) {
                        // $message = 'SMS Or Email';
                        $flagArr[] = [
                            'email' => 0,
                            'sms' => 0
                        ];
                        return $this->helpError(70, 'One of the review requests failed. To avoid spamming activities, we only allow 3 review requests to each customer for every communication type (e.g. Email, SMS). See details below:', $flagArr);

                    } else if (isset($sms)) {
                        $flagArr[] = [
                            'email' => 1,
                            'sms' => 0
                        ];
                        return $this->helpError(70, 'One of the review requests failed. To avoid spamming activities, we only allow 3 review requests to each customer for every communication type (e.g. Email, SMS). See details below:', $flagArr);

                    } else if (isset($email)) {
                        $flagArr[] = [
                            'email' => 0,
                            'sms' => 1
                        ];
                        return $this->helpError(70, 'One of the review requests failed. To avoid spamming activities, we only allow 3 review requests to each customer for every communication type (e.g. Email, SMS). See details below:', $flagArr);

                    } elseif (isset($internalErr[0]) && isset($internalErr[1])) {

                        return $this->helpError(404, 'Sorry Record Not Exist. Please try again later.');

                    } else {
                        return $this->helpReturn("Email & SMS sent Successfully.");
                    }
                }

                /***********************EMAIL & SMS Both Ending*****************/

                /*********************************For Email Start *************************************/
                if ($request->type[0] == 'email' && !isset($request->type[1])) { //email
                    Log::info('email');
                    if (count($record) == 3) {
                        return $this->helpError(3, 'You are only allowed to invite recipient 3 times of this email.');
                    } else {
                        $result = $this->emailSending($record, $request, $user, $businessId, $businessName, $Useremail, $formatedDate);

                        if ($result['_metadata']['outcomeCode'] == 404) {
                            return $result;
                        }
                        return $this->helpReturn("Email sent to recipient.");
                    }
                }
                /*********************************For Email End *************************************/

                /***For SMS **********/
                if ($request->type[0] == 'sms') { //SMS
                    Log::info('sms');
                    if (count($phoneRecords) == 3) {
                        return $this->helpError(3, 'You are only allowed to invite recipient 3 times of this phone number.');
                    } else {

                        $result = $this->sendSms($phoneRecords, $request, $user, $businessId, $businessName, $Useremail, $formatedDate, $flag = 'one');
                        if ($result['_metadata']['outcomeCode'] == 404) {
                            return $result;
                        }
                        return $this->helpReturn("SMS sent to recipient.");
                    }
                }
                /***********************SMS ENDING******************/
                // DB::commit();


            } else {
                return $this->helpError(70, 'No access for this action, Your third party business account has been unlinked');
            }


        } catch (Exception $exception) {

            //DB::rollback();
            Log::info(" addRecipients phone " . $exception->getCode());
            $err[] = [
                'map' => 'phone_number',
                'message' => 'Please Enter a Valid Phone Number'
            ];
            if ($exception->getCode() == 6) {
                return $this->helpError(2, 'Please Fix the Error', $err);

            }
            if ($exception->getCode() == 15) {
                return $this->helpError(2, 'Please Fix the Error', $err);
            }

            if ($exception->getCode() == 530) {
                return $this->helpError(503, 'Email not sent. Please try again.');
            }

            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }

    public function smartRouting($businessId, $smartRouting, $reciepentId, $allSites = [], $flag)
    {


        try {
            //feedback case
            if ($smartRouting == 'enable' && !empty($allSites)) { //case when user add recipient
                $typeArray = [];
                foreach ($allSites as $value) {
                    $typeArray[] = ['type' => getThirdPartyTypeShortToLongForm($value['site'])];
                }

                $thirdPartyMaster = TripadvisorMaster::select('third_party_id', 'business_id', 'type', 'average_rating', 'add_review_url', 'review_count')->whereNotIn('type', $typeArray)->where('business_id', $businessId)->whereNotNull('name')->whereNotNull('average_rating')->get()->toArray();
                $socialMediaMaster = SocialMediaMaster::select('id as third_party_id', 'type', 'add_review_url', 'id', 'average_rating', 'page_reviews_count as review_count')->whereNotIn('type', $typeArray)->where('business_id', $businessId)->whereNotNull('name')->whereNotNull('average_rating')->get()->toArray();
                $mergeArray = array_merge($thirdPartyMaster, $socialMediaMaster); //merge both array

            } else if ($flag == true) { //case for when user pass feedback
                $site = ReviewRequest::select('site')->where('recipient_id', $reciepentId)->first()->toArray();

                $type = getThirdPartyTypeShortToLongForm($site['site']);

                $thirdPartyMaster = TripadvisorMaster::select('third_party_id', 'business_id', 'type', 'average_rating', 'add_review_url', 'review_count')->where('type', $type)->where('business_id', $businessId)->whereNotNull('name')->whereNotNull('average_rating')->get()->toArray();
                $socialMediaMaster = SocialMediaMaster::select('id as third_party_id', 'type', 'add_review_url', 'id', 'average_rating', 'page_reviews_count as review_count')->where('type', $type)->where('business_id', $businessId)->whereNotNull('name')->whereNotNull('average_rating')->get()->toArray();
                $mergeArray = array_merge($thirdPartyMaster, $socialMediaMaster); //merge both array

            } else { //default case

                $thirdPartyMaster = TripadvisorMaster::select('third_party_id', 'business_id', 'type', 'average_rating', 'add_review_url', 'review_count')->where('business_id', $businessId)->whereNotNull('name')->whereNotNull('average_rating')->get()->toArray();

                $socialMediaMaster = SocialMediaMaster::select('id as third_party_id', 'type', 'add_review_url', 'id', 'average_rating', 'page_reviews_count as review_count')->where('business_id', $businessId)->whereNotNull('name')->whereNotNull('average_rating')->get()->toArray();
                $mergeArray = array_merge($thirdPartyMaster, $socialMediaMaster); //merge both array

            }

            if (!empty($mergeArray)) {

                if ($smartRouting == 'enable') { //check smart routing

                    $minimumRatingValue = min(array_column($mergeArray, 'average_rating')); //find minimum value of average array

                    $minimumRatingValueFound = array_where($mergeArray, function ($value, $key) use ($minimumRatingValue) {
                        return $value['average_rating'] == $minimumRatingValue;
                    });

                    $minimumRatingValueFound = array_values($minimumRatingValueFound);

                    if (count($minimumRatingValueFound) == 1) {  // if all values are equel then we use review count in else part
                        $finalRedirectUrlArray = $minimumRatingValueFound[0];


                    } else {
                        Log::info('review section');

                        $minimumReviewValue = min(array_column($mergeArray, 'review_count')); //again as above we find minimum value of Review Count instead of Rating
                        //$minimumReviewValue = min(array_filter(array_column($mergeArray, 'review_count'))); //again as above we find minimum value of Review Count instead of Rating

                        $minimumReviewValueFound = array_where($mergeArray, function ($value, $key) use ($minimumReviewValue) {
                            return $value['review_count'] == $minimumReviewValue;
                        });

                        $minimumReviewValueFound = array_values($minimumReviewValueFound);
                        if (count($minimumReviewValueFound) == 1) {  //if single record find
                            $finalRedirectUrlArray = $minimumReviewValueFound[0];


                        } else {
                            if (isset($minimumReviewValueFound[0])) {
                                $finalRedirectUrlArray = $minimumReviewValueFound[0];

                            }
                        }

                    }

                } else { //if smart routing disable

                    $finalRedirectUrlArray = $mergeArray;
                }

                return $this->helpReturn("site listing.", $finalRedirectUrlArray);
            } else {
                Log::info('error');
                return $this->helpError(404, 'Sorry Record Not Exist. Please try again later.');
            }
        } catch (Exception $exception) {
            Log::info("smartRouting " . $exception->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }

    /**
     * @api {get} /get-reviews/get-recipients-list [ RF-14-01 ] Get Recipients List
     * @apiVersion 1.0.0
     * @apiName Get Recipients List
     * @apiGroup Get Review
     * @apiParam {String} token
     * @apiPermission Secured
     * @apiDescription Get Recipients List.
     */

    public function getRecipientsList($request)
    {

        try {
            $businessObj = new BusinessEntity();
            $businessResult = $businessObj->userSelectedBusiness($request);

            if ($businessResult['_metadata']['outcomeCode'] != 200) {
                return $this->helpError(1, 'Problem in selection of your business.');
            }

            $businessResult = $businessResult['records'];
            $businessId = $businessResult['business_id'];
            $userID = $businessResult['user_id'];

            $recipients = DB::table('recipients')
                ->join('reviews_requests', 'recipients.id', '=', 'reviews_requests.recipient_id')
                ->select('recipients.first_name', 'recipients.email', 'recipients.last_name', 'reviews_requests.recipient_id',DB::raw('DATE_FORMAT(reviews_requests.date_sent, "%m-%d-%Y") as date_sent'), 'reviews_requests.site', 'reviews_requests.type','reviews_requests.review_status', 'recipients.smart_routing', 'recipients.phone_number')
                ->where('reviews_requests.message', '=', null)
                ->where('recipients.user_id', $userID)
                ->orderBy('recipients.id','DESC')
                ->get()->toArray();

            if (empty($recipients)) {
                return $this->helpError(404, 'Recipient not found.');
            }

            $appendArray=[];
            foreach($recipients as $recipient){

                $appendArray[] = [
                    'first_name' =>  strlen($recipient->first_name) > 40 ? Crypt::decrypt($recipient->first_name) : $recipient->first_name,
                    'email' => strlen($recipient->email) > 40 ? Crypt::decrypt($recipient->email) : $recipient->email,
                    'last_name' => strlen($recipient->last_name) > 40 ? Crypt::decrypt($recipient->last_name) : $recipient->last_name,
                    'recipient_id' => $recipient->recipient_id,
                    'date_sent' => $recipient->date_sent,
                    'site' => $recipient->site,
                    'type' => $recipient->type,
                    'smart_routing' => $recipient->smart_routing,
                    'review_status' => $recipient->review_status,
                    'phone_number' => strlen($recipient->phone_number) > 40 ? Crypt::decrypt($recipient->phone_number) : $recipient->phone_number,
                ];
            }

            return $this->helpReturn('All Recipients List', $appendArray);
        } catch (Exception $exception) {
            Log::info(" getRecipientsList " . $exception->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }


    public function checkThirdParties(Request $request) //save negative and positive feedback
    {
        try {
            $businessObj = new BusinessEntity();
            $businessResult = $businessObj->userSelectedBusiness($request);

            if ($businessResult['_metadata']['outcomeCode'] != 200) {
                return $this->helpError(1, 'Problem in selection of your business.');
            }

            $businessResult = $businessResult['records'];
            $businessId = $businessResult['business_id'];
            $userID = $businessResult['user_id'];

            $thirdPartyMaster = ThirdPartyMaster::select('name', 'page_url')->where('name', '!=', null)->where('page_url', '!=', null)->where('business_id', $businessId)->get()->toArray();

            $socialMediaMaster = SocialMediaMaster::select('name', 'page_url')->where('name', '!=', null)->where('page_url', '!=', null)->where('business_id', $businessId)->get()->toArray();
            $mergeArray = array_merge($thirdPartyMaster, $socialMediaMaster); //merge both array

            if (!empty($mergeArray)) {
                $flag = ['flag' => 1];
            } else {
                $flag = ['flag' => 0];
            }

            return $this->helpReturn("Third Party Record Flag.", $flag);
        } catch (Exception $exception) {
            Log::info(" checkThirdParties " . $exception->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }

    }

    /**
     * Update site column in review table after click on link in email
     * @param Request $request
     * @return mixed
     */
    public function updateSites(Request $request)
    {

        try {
            //check authenticated user
            if (filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
                Log::info('email');
                $recipient = Recipient::where('email', $request->email)->where('verification_code', $request->secret)->first(); //check autheticated user
                $Recordtype = 'email'; //for example SMS , Email
            } else if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
                $recipient = Recipient::where('phone_number', $request->email)->where('verification_code', $request->secret)->first(); //check autheticated user
                $Recordtype = 'sms'; //for example SMS , Email
                Log::info('sms');
            }

            if (!empty($recipient)) {
                $reviewRequest = new ReviewRequest();
                if ($recipient->smart_routing == 'disable') {
                    $record = ReviewRequest::where('recipient_id', $recipient->id)->where('site', '=', null)->where('message', '=', null)->get(); //no include negative feedback row
                    if (count($record) == 1) { //check one record exist then update it
                        $reviewRequest->where('recipient_id', $recipient->id)->where('id', $record[0]['id'])->update(['site' => $request->site]);
                    } else { //if more then one row then create again and again
                        $currentDate = Carbon::now();
                        $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('Y-m-d');
                        $checkRecord = ReviewRequest::where('recipient_id', $recipient->id)->where(['site' => $request->site])->get()->toArray();
                        if (empty($checkRecord)) {  //check not duplicate entry exist in database
                            ReviewRequest::create(['recipient_id' => $recipient->id, 'site' => $request->site, 'date_sent' => $formatedDate, 'type' => $Recordtype]);
                        } else {
                            return $this->helpError(3, 'You are already done this actions');
                        }
                    }
                } else {
                    $record = ReviewRequest::where('recipient_id', $recipient->id)->where('message', '=', null)->first(); //no include negative feedback row

                    if (!empty($record)) {
                        $reviewRequest
                            ->where('recipient_id', $recipient->id)
                            ->where('id', $record['id'])
                            ->update(['site' => $request->site]);
                    }
                }

            } else {
                return $this->helpError(3, 'You are not authorize for this action please try again');
            }

            return $this->helpReturn("Site Update Successfully.");
        } catch (Exception $exception) {
            Log::info(" updateSites " . $exception->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }

    }

    // save negative positive feedback & just get different thirdparty url depend on smart routing on or off
    public function saveFeedback($request)
    {
        Log::info('print request');
        Log::info($request->all());
        Log::info('check thums value');
        Log::info($request->tumb_status);

        try {
            Log::info('check coming id');
            Log::info($request->id);

            $recipient = '';
           if(!empty($request->id)){
               $business = Business::select('business_id','business_name')
                   ->where('business_id','=',$request->id)->first();

               $arr = ['business_name' => $business['business_name']];
           }

            if (filter_var($request->email, FILTER_VALIDATE_EMAIL)) {
                $emailAll = Recipient::where('verification_code', $request->secret)->first();

                if($emailAll['email'] == $request['email']){ // use check due to encrypetion
                    $recipient = $emailAll;
                    $checkParam = 'email';
                }

            }
            else if (!filter_var($request->email, FILTER_VALIDATE_EMAIL)) {

                $mobileAll = Recipient::where('verification_code', $request->secret)->first();
                if($mobileAll['phone_number'] == $request['email']){ // use check due to encrypetion
                    $recipient = $mobileAll;
                    $checkParam = 'sms';
                }
            }


            Log::info('in save feedback section get recipient details');
            Log::info($recipient);
            Log::info('check thums value');
            Log::info($request->tumb_status);

            if (!empty($recipient)) {
                Log::info('inside');
                $user = User::where('id', $recipient->user_id)->first();

                $business = Business::select('business_id','business_name')
                    ->where('user_id', $recipient->user_id)
                    ->first();

                Log::info('check business details');
                Log::info($business);
                Log::info('check thums value');
                Log::info($request->tumb_status);


                $reviewRequest = ReviewRequest::where('recipient_id','=',$recipient->id)
                    ->where('id', $request->review_id)
                    ->first();

                $Reviewtype = getThirdPartyTypeShortToLongForm($reviewRequest->site);

                Log::info('check type');
                Log::info($Reviewtype);

                if($Reviewtype == 'Google')
                {
                    $Reviewtype = 'Google Places';
                }

                $thirdPartyMaster = ThirdPartyMaster::select('third_party_id', 'business_id', 'type', 'average_rating', 'add_review_url', 'review_count')
                    ->where('type', $Reviewtype)
                    ->where('business_id', $business['business_id'])
                    ->whereNotNull('name')
                    ->get()
                    ->toArray();

                $socialMediaMaster = SocialMediaMaster::select('id as third_party_id', 'type', 'add_review_url', 'id', 'average_rating', 'page_reviews_count as review_count')
                        ->where('type', $Reviewtype)
                        ->where('business_id', $business['business_id'])
                        ->whereNotNull('name')
                        ->get()
                        ->toArray();

                $mergeArray = array_merge($thirdPartyMaster, $socialMediaMaster); //merge both array

                $settings = CrmSettings::where('user_id',$user->id)->first();

                if($settings->smart_routing == 'disable' || $settings->smart_routing == 'Disable' && empty($mergeArray))
                {
                    Log::info('not auth 01');
                    return $this->helpError(3, 'You are not authorize for this action please click this link from your email.',$arr);
                }

                if (empty($request->tumb_status)) {
                    Log::info('inside thumn status if empty');
                    Log::info('check array');
                    Log::info('check business name');
                    Log::info($business['business_name']);

                    $arr = ['business_name' => $business['business_name']];

                    Log::info('check array values');
                    Log::info($arr);
                    //return $this->helpError(2, 'Your flag is missing.');

                    return $this->helpReturn("successfully checked business.",$arr);
                }

                $currentDate = Carbon::now();
                $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('Y-m-d');

                if ($request->tumb_status == 'down')
                { //if thumbs down then simply we save messagen , first name and date sent
                    Log::info('thumb down processing');

                    $redirectUrl = $mergeArray;
                    Log::info($mergeArray);

                    if(empty($redirectUrl)){
                        Log::info('not auth 02');
                        return $this->helpError(3, 'You are not authorize for this action please click this link from your email.');
                    }

                    if (empty($request->message)) {
                        return $this->helpError(2, 'message is required');
                    }

                    $request->request->add(['recipient_id' => $recipient->id]);
                    $request->request->add(['date_sent' => $formatedDate]);


                    ReviewRequest::create($request->toArray());

                    try
                    {
                        Mail::to($user->email)->send(new CreateNagetiveReviewFeedBackEmail($user->first_name, $request->message, $request->date_sent, $request->email));
                    }
                    catch(Exception $e)
                    {
                        Log::info("internal saveFeedback");
                    }


                    return $this->helpReturn("Thank you for your feedback.");

                }
                else if ($request->tumb_status == 'up') {
                    //if thumbs up
                    Log::info('inside thums up');
                    $flag = false;
                    $settings = CrmSettings::where('user_id',$user->id)->first();

                    if ($settings->smart_routing == 'enable' || $settings->smart_routing == 'Enable') {
                        Log::info('inside enable section');
                        $flag = true;
                        Log::info('all details before enter in smart Routing');
                        Log::info($business->business_id);
                        Log::info($recipient->smart_routing);
                        Log::info($recipient->id);
                        // here 616
                        $redirectUrl = $this->crmEntity->smartRouting($business->business_id, $recipient->smart_routing, $recipient->id, [], $flag);

                    }
                    else if($settings->smart_routing == 'disable' || $settings->smart_routing == 'Disable'){
                        Log::info('inside disable section');
                        $flag = true;

                        $redirectUrl = $mergeArray;
                        if(empty($redirectUrl)){
                            return $this->helpError(3, 'You are not authorize for this action please click this link from your email.');
                        }

                        $reviewRequest = new  ReviewRequest();
                        Log::info('check response of redirect urls');
                        Log::info($redirectUrl);
                        Log::info('check response of redirect urls');
                        $reviewRequest->where('recipient_id',$recipient->id)->where('review_status','false')->where('message','=',null)->where('type',$checkParam)->update(['review_status' => 'true']);
                        return $this->helpReturn("Site listing",$redirectUrl);
                    }

                    $reviewRequest = new  ReviewRequest();
                    Log::info('check response of redirect urls');
                    Log::info($redirectUrl);
                    Log::info('check response of redirect urls');

                    $reviewRequest->where('recipient_id',$recipient->id)
                        ->where('review_status','false')
                        ->where('message','=',null)
                        ->where('type',$checkParam)
                        ->update
                        (
                            [
                                'review_status' => 'true'
                            ]
                        );

                    return $redirectUrl;
                }

            }

            Log::info('outside receipient');
            return $this->helpError(3, 'You are not authorize for this action please click this link from your email.',$arr);

        } catch (Exception $exception) {
            Log::info(" saveFeedback > " . $exception->getMessage() . ' > line is > ' . $exception->getLine());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }

    }


    public function addRecipientsUsingFile(Request $request) //Update site column in review table after click on link in email
    {
        try {
            if (!$this->fileReviewValidator->with($request->all())->passes()) {

                return $this->helpError(2, 'Fill required field.', $this->fileReviewValidator->errors());
            }


            $businessObj = new BusinessEntity();
            $checkPoint = $this->setCurrentUser($request->get('token'))->userAllow();
            $currentDate = Carbon::now();
            $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('Y-m-d');

            if ($checkPoint['_metadata']['outcomeCode'] != 200) {
                return $checkPoint;
            }

            $user = $checkPoint['records'];
            $Useremail = $user['email'];
            $businessResult = $businessObj->userSelectedBusiness($user);

            if ($businessResult['_metadata']['outcomeCode'] != 200) {
                return $this->helpError(1, 'Problem in selection of user business.');
            }
            $businessId = $businessResult['records']['business_id'];
            $businessName = $businessResult['records']['name'];

            /******Save File****/

            if ($request->hasFile('file')) {
                $file = $request->file('file');

                $extension = $file->getClientOriginalExtension();
                $fileName = $file->getFilename() . '.' . $extension;
                Storage::disk('local')->put($fileName, File::get($file));
            }

            /****Get Saved File***/

            $file = fopen(storage_path('app/' . $fileName), "r");
            $flag = true;
            $appendArray = [];

            while (($data = fgetcsv($file, 1000, ",")) !== FALSE) {  //read the csv file

                if ($flag != true) { //not include titles in excell sheets
                    if (!empty($data[1])) {
                        if (!empty($data[0]) || !empty($data[3])) {
                            $appendArray[] = [
                                'email' => !empty($data[0]) ? $data[0] : '',
                                'first_name' => !empty($data[1]) ? $data[1] : '',
                                'last_name' => !empty($data[2]) ? $data[2] : '',
                                'phone_number' => !empty($data[3]) ? $data[3] : '',
                            ];
                        }

                    } else {
                        return $this->helpError(3, 'Invalid format. Make sure that the CSV file includes the first name, phone number OR email.');
                    }

                } else {
                    //validation of csv file titles
                    if (!empty($data['0']) && $data['0'] == 'email_address' && !empty($data['1']) && $data['1'] == 'first_name' && !empty($data['2']) && $data['2'] == 'last_name' && !empty($data['3']) && $data['3'] == 'phone_number') {

                    } else {

                        return $this->helpError(3, 'Invalid format. Make sure that the CSV file includes the first name, phone number OR email.');
                    }
                }

                $flag = false;

            }
            fclose($file);

            /***Get File Data Row by Row****/

            if (empty($appendArray)) {
                return $this->helpError(3, 'Record Not Found Please Check Your File.');
            }


            foreach ($appendArray as $row) {

                Log::info($row);
                $filterPhoneNumber = filterPhoneNumber($row['phone_number']);


                $record = Recipient::where('email', $row['email'])->where('user_id', $user->id)->get();
                $phoneRecords = Recipient::where('phone_number', $filterPhoneNumber)->where('user_id', $user->id)->get();

                $allSites = [];

                if (!empty($record->toArray())) { //check record for first time

                    $recipient = $record->where('smart_routing', '=', 'enable');
                    if (!empty($recipient)) {
                        $recipientIdsArray = [];
                        if (!empty($recipient)) {
                            foreach ($recipient as $row) {
                                $recipientIdsArray[] = ['id' => $row['id']];
                            }

                            $allSites = ReviewRequest::whereIn('recipient_id', $recipientIdsArray)->get()->toArray();
                        }
                    }

                }

                /*************************My New Working Area*************************/
                $request->request->add(['email' => $row['email'], 'first_name' => $row['first_name'], 'last_name' => $row['last_name'], 'phone_number' => $filterPhoneNumber, 'smart_routing' => $request->smart_routing]);

                Log::info('both');

                if (count($record) != 3) { //section one
                    Log::info('internal email');
                    if (!empty($row['email'])) {

                        $response = $this->emailSending($record, $request, $user, $businessId, $businessName, $Useremail, $formatedDate);

                    }

                }

                if (count($phoneRecords) != 3) { //section 2
                    Log::info('internal sms');
                    if (!empty($filterPhoneNumber)) {
                        Log::info('internal sms');

                        $response = $this->sendSms($phoneRecords, $request, $user, $businessId, $businessName, $Useremail, $formatedDate, $flag = 'both');

                    }
                }

            }


            return $this->helpReturn("Email & SMS sent to recipients.");
        } catch (Exception $exception) {
            Log::info("addRecipientsUsingFile " . $exception->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }

    }

    public function emailSending($record, $request, $user, $businessId, $businessName, $Useremail, $formatedDate)
    {

        Log::info('Email Sending Function');
        $allSites = [];
        if (!empty($record->toArray())) { //check record for first time

            $recipient = $record->where('smart_routing', '=', 'enable')->toArray();

            if (!empty($recipient)) {
                foreach ($recipient as $row) {
                    $recipientIdsArray[] = ['id' => $row['id']];
                }

                $allSites = ReviewRequest::whereIn('recipient_id', $recipientIdsArray)->get()->toArray();
            }

        }

        $data = $request->toArray();
        $data['user_id'] = $user['id'];
        $data['verification_code'] = randomString();

        if ($request->smart_routing == 'enable') {

            unset($data['phone_number']);
            // DB::beginTransaction();

            $recipient = Recipient::create($data);

            $finalRedirectUrlArray = $this->smartRouting($businessId, $request->smart_routing, $recipient->id, $allSites, $flag = false);


            if ($finalRedirectUrlArray['_metadata']['outcomeCode'] == 200) {

                try {
                    Log::info('Email Sending Block If Enable');
                    Mail::to($request->email)->send(new CreateAddReciepentsEmail($request->first_name, $businessName, $data['verification_code'], $request->email, $Useremail));
                    if (!empty($request->type[0])) {
                        $requestType = $request->type[0];
                    } else {
                        $requestType = 'email';
                    }
                    $type = getThirdPartyTypeLongToShortForm($finalRedirectUrlArray['records']['type']);
                    ReviewRequest::create(['date_sent' => $formatedDate, 'recipient_id' => $recipient['id'], 'site' => $type, 'type' => $requestType]);
                    //   DB::commit();

                } catch (\Exception $e) {
                    //   DB::rollback();
                }
            } else {
                Recipient::where('id', $recipient->id)->delete();
                return $finalRedirectUrlArray;
            }


        } else {
            unset($data['phone_number']);
            $recipient = Recipient::create($data);
            Mail::to($request->email)->send(new CreateAddReciepentsEmail($request->first_name, $businessName, $data['verification_code'], $request->email, $Useremail));
            if (!empty($request->type[0])) {
                $requestType = $request->type[0];
            } else {
                $requestType = 'email';
            }
            ReviewRequest::create(['date_sent' => $formatedDate, 'recipient_id' => $recipient['id'], 'type' => $requestType]);
        }

    }


    public function sendSms($phoneRecords, $request, $user, $businessId, $businessName, $Useremail, $formatedDate, $typeFlag)
    {

        Log::info('Inside sms section');
        Log::info('Send Sms');

        $allSites = [];
        if (!empty($phoneRecords->toArray())) { //check record for first time

            $recipient = $phoneRecords->where('smart_routing', '=', 'enable')->toArray();

            if (!empty($recipient)) {
                foreach ($recipient as $row) {
                    $recipientIdsArray[] = ['id' => $row['id']];
                }

                $allSites = ReviewRequest::whereIn('recipient_id', $recipientIdsArray)->get()->toArray();
            }

        }
        Log::info('phone number');


        $filterPhoneNumber = filterPhoneNumber($request->phone_number);

        $data = $request->toArray();
        $data['phone_number'] = $filterPhoneNumber;
        $data['user_id'] = $user['id'];
        $data['verification_code'] = randomString();
        //$url = Bitly::getUrl('http://dev-app.netblaze.com//business-review/'.$request->phone_number.'/'.$data['varification_code'].'/'.$businessName);
        $encodedurl = getDomain();
        $url = Bitly::getUrl($encodedurl . '/business-review/' . $filterPhoneNumber . '/' . $data['verification_code'] . '/' . $businessName);

        $msg = "Thanks for choosing " . $businessName . ".I'd like to invite you to tell us about your experience. Any feedback is appreciated - " . $url;

        if ($request->smart_routing == 'enable') {
            unset($data['email']);
            // DB::beginTransaction();

            Log::info('before submit');
            Log::info($data);


            $recipient = Recipient::create($data);

            $finalRedirectUrlArray = $this->smartRouting($businessId, $request->smart_routing, $recipient->id, $allSites, $flag = false);

            if ($finalRedirectUrlArray['_metadata']['outcomeCode'] == 200) {
                Log::info('Send Sms');
                Log::info($url);

                try {
                    Log::info($data['phone_number']);

                    $type = getThirdPartyTypeLongToShortForm($finalRedirectUrlArray['records']['type']);

                    if ($typeFlag == 'both') {
                        $requestType = $request->type[1];
                    } else {
                        $requestType = $request->type[0];
                    }


                    if (!empty($requestType)) {
                        $types = $requestType;
                    } else {
                        $types = 'sms';
                    }
                    $r = ReviewRequest::create(['date_sent' => '', 'recipient_id' => $recipient['id'], 'site' => $type, 'type' => $types, 'message_body' => $msg, 'status' => 'READY_TO_SEND']);

                } catch (\Exception $e) {
                    Log::info('cztch in cahtc');
                    Log::info("addRecipientsUsingFile " . $e->getMessage());
                }
            } else {
                Recipient::where('id', $recipient->id)->delete();
                return $finalRedirectUrlArray;
            }


        } else {
            if ($typeFlag == 'both') {
                $requestType = $request->type[1];
            } else {
                $requestType = $request->type[0];
            }

            unset($data['email']);
            $recipient = Recipient::create($data);
            Log::info($url);
            if (!empty($requestType)) {
                $types = $requestType;
            } else {
                $types = 'sms';
            }
            ReviewRequest::create(['date_sent' => '', 'recipient_id' => $recipient['id'], 'type' => $types, 'message_body' => $msg, 'status' => 'READY_TO_SEND']);
        }

    }
}

