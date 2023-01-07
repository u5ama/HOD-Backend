<?php
/**
 * Created by Wahab
 * Date: 10/30/2017
 * Time: 2:51 PM
 */

namespace Modules\CRM\Entities;

use App\Entities\AbstractEntity;
use App\Mail\CreateSendReviewRequestEmail;
use App\Mail\newCustomerAdded;
use Illuminate\Support\Facades\Mail;
use App\Services\SessionService;
use http\Env\Request;
use Modules\CRM\Models\CustomerFormSettings;
use Modules\CRM\Models\CustomerLeads;
use Modules\CRM\Models\DeletedFields;
use Modules\CRM\Models\NewFields;
use Modules\CRM\Models\UserReviewsFiles;
use Modules\ThirdParty\Models\ThirdPartyMaster;
use Modules\ThirdParty\Models\TripadvisorReview;
use Modules\User\Models\User;
use Modules\Business\Models\Business;
use Modules\CRM\Models\CrmSettings;
use Modules\CRM\Models\Promo;
use Modules\CRM\Models\SendingHistory;
use Modules\ThirdParty\Models\StatTracking;
use Modules\CRM\Services\Validations\Reviews\AddReviewValidator;
use Modules\CRM\Services\Validations\Reviews\FilesReviewValidator;
use Modules\CRM\Services\Validations\Reviews\EditCustomerValidator;
use Modules\ThirdParty\Models\TripadvisorMaster;
use Modules\ThirdParty\Models\SocialMediaMaster;
use App\Mail\CreateAddRecipientsEmail;
use Modules\CRM\Models\ReviewRequest;
use App\Traits\UserAccess;
use Exception;
use Log;
use Shivella\Bitly\Facade\Bitly;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;
use Tymon\JWTAuth\Facades\JWTAuth;
use DB;
use Config;
use Modules\Business\Entities\BusinessEntity;
use Modules\CRM\Models\Recipient;
use Storage;
use File;
use Carbon\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Arr;
use Yajra\DataTables\Facades\DataTables;


class CRMEntity extends AbstractEntity
{
    use UserAccess;

    protected $addReviewValidator;
    protected $editCustomerValidator;
    protected $fileReviewValidator;

    protected $businessEntity;

    public function __construct()
    {
        $this->businessEntity = new BusinessEntity();
        $this->addReviewValidator = new AddReviewValidator(resolve('validator'));
        $this->editCustomerValidator = new EditCustomerValidator(resolve('validator'));
        $this->fileReviewValidator = new FilesReviewValidator(resolve('validator'));
    }

    public function addCustomers($request)
    {
        try {
            Log::info("addCustomers process");
            $businessObj = new BusinessEntity();
            $businessResult = $businessObj->userSelectedBusiness($request);

            if ($businessResult['_metadata']['outcomeCode'] != 200) {
                return $this->helpError(1, 'Problem in selection of your business.');
            }
            $businessResult = $businessResult['records'];
            $businessId = $businessResult['business_id'];
            $userID = $businessResult['user_id'];
            $businessName = $businessResult['business_name'];

            if (!empty($request->input('token'))){
                JWTAuth::setToken($request->input('token'));
                $userData = JWTAuth::toUser();
                $user_id = $userData['id'];
            }else{
                $user_id = $request->user_id;
                $userData = User::where('id', $user_id)->first();
            }

            if (!empty($request->u_id)){
                $Useremail = $userData['email'];
                $settings = CrmSettings::where('user_id', $user_id)->first();
                $settings['business_id'] = $businessId;
                $settings['business_name'] = $businessName;
                $settings['user_email'] = $Useremail;
                $settings['sending_option'] = $request->sending_option;
                $recp = Recipient::where('id', $request->u_id)->first();
                $request->merge(
                    [
                        'verification_code' => $recp->verification_code,
                        'recipient_id' => $recp->id,
                        'email' => $recp->email,
                        'phoneNumber' => $recp->phone_number,
                        'firstName' => $recp->first_name,
                        'lastName' => $recp->last_name
                    ]);
                $this->smsEmailSending($request, $settings);
                Log::info('settings response');
                return $this->helpReturn("Mail Send Successfully.");
            }
            /**
             * this section for web optin wehen user pass token and we can generate token
             */

            if (isset($request->business_email)) {
                $user = User::where('email', $request->business_email)->first();
                $token = JWTAuth::fromUser($user);
                $request->request->add(['token' => $token]);
            }
            /**
             * section end
             */

            /**
             * this section for web optin wehen user pass token and we can generate token
             */

            if (isset($request->param)) {

                $currentDate = Carbon::now();
                $activeDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('Y-m-d');
                $lead = CustomerLeads::create([
                    'user_id' => $request->user_id,
                    'activity_date' => $activeDate,
                    'type' => 'lead',
                    'count' => 1
                ]);
                $user = User::where('id',  $request->get('user_id'))->first();
                $formData = NewFields::where('user_id', $request->get('user_id'))->get();
                if (!empty($formData)){
                    foreach ($formData as $field){
                        if (!empty($request->get($field['field_name']))){
                            $fields[] = $request->get($field['field_name']);
                            Mail::to($user->email)->send( new newCustomerAdded(
                                $request->firstName,
                                $request->phoneNumber,
                                $request->email,
                                $fields
                            ));
                        }
                    }
                }
            }
            /**
             * section end
             */

            if ($request->customer_id == '') {

                $filterNumber = filterPhoneNumber($request->phoneNumber);

                if (!empty($filterNumber)) {
                    $request->merge(['phone_number' => $filterNumber]);
                } else {
                    //this case for validate correct mobile number
                    $request->merge(['phone_number' => $request->phoneNumber]);
                }

                if (!$this->addReviewValidator->with($request->all())->passes()) {

                    return $this->helpError(2, 'Fill required field.', $this->addReviewValidator->errors());
                }
            }

            $thirdPartyMaster = TripadvisorMaster::select('third_party_id', 'business_id', 'type', 'average_rating', 'add_review_url', 'review_count')
                ->where('business_id', $businessId)
                ->whereNotNull('name')
                ->get()->toArray();

            $socialMediaMaster = SocialMediaMaster::select('id as third_party_id', 'type', 'add_review_url', 'id', 'average_rating', 'page_reviews_count as review_count')
                ->where('type', 'Facebook')
                ->where('business_id', $businessId)
                ->whereNotNull('name')
                ->get()->toArray();

            $mergeArray = array_merge($thirdPartyMaster, $socialMediaMaster); //merge both array

            Log::info("mergeArray");
            Log::info($mergeArray);

            $email = $request->email == null ? '' : $request->email;
            $phone_number = $request->phoneNumber == null ? '' : $request->phoneNumber;
            $verificationCode = randomString();
            $data = [];

            $Useremail = $userData['email'];

            /*************New Working for Add Customer with new requirements*******/
            $settings = CrmSettings::where('user_id', $user_id)->first();

            if (isset($request->enable_get_reviews)) {
                Log::info("enable_get_reviews user");

                // worked with settings to send email to customer.
                $settings = new CrmSettings();

                $settings->where('user_id', $user_id)
                    ->update(
                        [
                        'enable_get_reviews' => $request->enable_get_reviews,
                        'sending_option' => $request->sending_option,
                        'smart_routing' => $request->smart_routing,
                        'review_site' => $request->review_site,
                        'reminder' => $request->reminder,
                        'customize_email' => $request->customize_email,
                        'customize_sms' => $request->customize_sms
                        ]);

                $request->merge(
                    [
                        'verification_code' => $request->verification_code,
                        'recipient_id' => $request->customer_id
                    ]);

                $settings = CrmSettings::where('user_id', $user_id)->first();
                $settings['business_id'] = $businessId;
                $settings['business_name'] = $businessName;
                $settings['user_email'] = $Useremail;

                if (!empty($settings['smart_routing']) && !empty($settings['enable_get_reviews']) && $settings['enable_get_reviews'] == 'Yes' && !empty($settings['sending_option'])) {
                    Log::info("i am user");
                    if (!empty($settings)) {
                        Log::info('req');
                        Log::info($request);

                        Log::info('settings');
                        Log::info($settings);

                        $response = $this->smsEmailSending($request, $settings);

                        Log::info('settings response');
                        Log::info($response);
                    }
                }
            }
            else if (!empty($settings['enable_get_reviews']) && $settings['enable_get_reviews'] == 'No') {
                $recipient = Recipient::create(
                    [
                        'email' => $email,
                        'first_name' => $request->firstName,
                        'last_name' => $request->lastName,
                        'phone_number' =>  $phone_number,
                        'enquiries' =>  $request->status,
                        'enquiry_source' => null,
                        'revenue' => $request->cusRevenue,
                        'comments' => $request->cusComment,
                        'user_id' => $user_id,
                        'country_code' => $request->countryCode,
                        'country' => $request->country
                    ]
                );
            }
            else if (!empty($settings['enable_get_reviews']) && $settings['enable_get_reviews'] == 'Yes') {
                Log::info('i am here now');
                // only create recipient
                $recipient = Recipient::create(
                    [
                        'smart_routing' => $settings->smart_routing,
                        'email' => $email,
                        'first_name' => $request->firstName,
                        'last_name' => $request->lastName,
                        'phone_number' => $phone_number,
                        'enquiries' => $request->status,
                        'enquiry_source' => null,
                        'revenue' => $request->cusRevenue,
                        'comments' => $request->cusComment,
                        'user_id' => $user_id,
                        'verification_code' => $verificationCode,
                        'country_code' => $request->countryCode,
                        'country' => $request->country,
                        'birthdate' => $request->birthdate,
                        'birthmonth' => $request->birthmonth
                    ]
                );
                Log::info($recipient);
                $data = [
                    'customer_id' => $recipient->id,
                    'verification_code' => $recipient->verification_code
                ];

                $request->merge(['verification_code' => $recipient->verification_code, 'recipient_id' => $recipient->id]);

                if (isset($request->business_email) && !empty($request->business_email)) {
                    $settings['business_id'] = $businessId;
                    $settings['business_name'] = $businessName;
                    $settings['user_email'] = $Useremail;

                    if (!empty($settings['smart_routing']) && !empty($settings['enable_get_reviews']) && $settings['enable_get_reviews'] == 'Yes' && !empty($settings['sending_option'])) {
                        if (!empty($settings)) {
                            $this->smsEmailSending($request, $settings);
                        }
                    }
                }
            }
            /*************New Working for Add Customer with new requirements*******/

            return $this->helpReturn("Customer Added Successfully.", $data);

        } catch (Exception $exception) {
            Log::info(" addCustomers > " . $exception->getMessage() . " line > " . $exception->getLine());
            return $this->helpError(1, 'Some Problem happened. please try again.');

        }
    }

    public function updateCustomer($request)
    {
        try {
            $businessObj = new BusinessEntity();
            if (!empty($request->input('token'))){
                JWTAuth::setToken($request->input('token'));
                $userData = JWTAuth::toUser();
            }
            $user = $userData;
            $Useremail = $user['email'];
            $businessResult = $businessObj->userSelectedBusiness($request);

            if ($businessResult['_metadata']['outcomeCode'] != 200) {
                return $this->helpError(1, 'Problem in selection of user business.');
            }

            $businessId = $businessResult['records']['business_id'];
            $businessName = $businessResult['records']['business_name'];

            $record = Recipient::where(['id' => $request->id, 'user_id' => $user['id']])->first();
            //Log::info($record);

            if (empty($record)) {
                return $this->helpError("404", "Customer Not Found");
            }
            $filterNumber = filterPhoneNumber($request->phoneNumber);

            if (!empty($filterNumber)) {
                $request->merge(['phone_number' => $filterNumber]);
            } else {
                $request->merge(['phone_number' => $request->phoneNumber]);
            }

            if (!$this->editCustomerValidator->with($request->all())->passes()) {

                return $this->helpError(2, 'Fill required field.', $this->editCustomerValidator->errors());
            }

            /********Custome Validation Area******/
            if (isset($userData['email'])) {
                $errorArray = [];
                $user_id = $userData['id'];

                Log::info("i am here");
                $emailAll = Recipient::where('user_id', $user_id)->where('email', '!=', '')->where('id', '!=', $request->id)->get()->toArray();
                Log::info("i am email");
                Log::info($emailAll);
                $email = false;
                foreach ($emailAll as $item) {
                    if (strlen($item['email']) > 100) {
                        if (Crypt::decrypt($item['email']) == $request['email']) {
                            $email = true;
                        }
                    } else {
                        if ($item['email'] == $request['email']) {
                            $email = true;
                        }
                    }
                }

                $phoneAll = Recipient::where('user_id', $user_id)->where('phone_number', '!=', '')->where('id', '!=', $request->id)->get()->toArray();
                Log::info("i am phone");
                Log::info($phoneAll);
                $phone_number = false;
                foreach ($phoneAll as $phone) {
                    if (strlen($phone['phone_number']) > 100) {
                        if (Crypt::decrypt($phone['phone_number']) == $filterNumber) {
                            $phone_number = true;
                        }
                    } else {
                        if ($phone['phone_number'] == $request['phone_number']) {
                            $phone_number = true;
                        }
                    }
                }


                if (!empty($email) && $email == true && !empty($phone_number) && $phone_number == true) {

                    $errorArray = [
                        [
                            'map' => 'email',
                            'message' => 'Email address already exists. Enter a different email.',
                        ],
                        [
                            'map' => 'phone_number',
                            'message' => 'Phone number already exists. Enter a different phone number.',
                        ]
                    ];
                    return $this->helpError(2, 'Fill required field.', $errorArray);
                } else if (!empty($email) && $email == true) {
                    $errorArray[] = [
                        'map' => 'email',
                        'message' => 'Email address already exists. Enter a different email.',
                    ];
                    return $this->helpError(2, 'Fill required field.', $errorArray);
                } else if (!empty($phone_number) && $phone_number == true) {
                    $errorArray[] = [
                        'map' => 'phone_number',
                        'message' => 'Phone number already exists. Enter a different phone number.',
                    ];
                    return $this->helpError(2, 'Fill required field.', $errorArray);
                }
            }

            /********Custom Validation Area******/

            $email = $request->email == null ? '' : $request->email;
            $phone_number = $request->phoneNumber == null ? '' : $request->phoneNumber;

            $thirdPartyMaster = TripadvisorMaster::select('third_party_id', 'business_id', 'type', 'average_rating', 'add_review_url', 'review_count')->where('business_id', $businessId)->whereNotNull('name')->get()->toArray();
            $socialMediaMaster = SocialMediaMaster::select('id as third_party_id', 'type', 'add_review_url', 'id', 'average_rating', 'page_reviews_count as review_count')
                ->where('type', 'Facebook')
                ->where('business_id', $businessId)->whereNotNull('name')->get()->toArray();

            $mergeArray = array_merge($thirdPartyMaster, $socialMediaMaster); //merge both array


            $settings = CrmSettings::where('user_id', $user['id'])->first();
            $flag = false;

            if (!empty($settings['sending_option']) && $settings['enable_get_reviews'] == 'Yes') {

                if ($settings->sending_option == 1) {

                    if (empty($email) && $phone_number != $record->phone_number) {
                        $flag = true;
                        $request->merge(['email' => '']);

                    } else if ($email != $record->email) {
                        $flag = true;
                        $request->merge(['phone_number' => '']);

                    }

                } else if ($settings->sending_option == 2) {
                    if (empty($phone_number) && $email != $record->email) {
                        $flag = true;
                        $request->merge(['phone_number' => '']);
                    } else if ($phone_number != $record->phone_number) {
                        $flag = true;
                        $request->merge(['email' => '']);
                    }
                } else {
                    if ($phone_number != $record->phone_number && $email != $record->email) {
                        $flag = true;

                    } else if ($email != $record->email) {
                        $flag = true;
                        $request->merge(['phone_number' => '']);
                    } else if ($phone_number != $record->phone_number) {
                        $flag = true;
                        $request->merge(['email' => '']);
                    }
                }

            }
            Log::info("i am at end");
            $record->update(['email' => $email, 'first_name' => $request->firstName, 'last_name' => $request->lastName, 'phone_number' => $phone_number, 'enquiries' =>  $request->status, 'revenue' =>  $request->cusRevenue, 'comments' =>  $request->cusComment]);

            $request->merge(['verification_code' => $record->verification_code, 'recipient_id' => $record->id, 'action' => 'update']);

            if (!empty($mergeArray)) {

                if ($flag == true) {

                    $settings['business_id'] = $businessId;
                    $settings['business_name'] = $businessName;
                    $settings['user_email'] = $Useremail;

                    if (!empty($settings['smart_routing']) && !empty($settings['enable_get_reviews']) && $settings['enable_get_reviews'] == 'Yes' && !empty($settings['sending_option'])) {
                        // && $settings['smart_routing'] == 'Enable'
                        if (!empty($settings)) {
                            $this->smsEmailSending($request, $settings);
                        }
                    }
                } else {
                    Log::info('false');
                }
            }

            return $this->helpReturn("Customer Updated Successfully.");

        } catch (Exception $exception) {
            return $this->helpError('addCustomers', 'Some Problem happened. please try again.');
        }
    }

    public function sendExistingCustomerReviewRequest($request)
    {

        $businessObj = new BusinessEntity();
        $checkPoint = $this->setCurrentUser($request->get('token'))->userAllow();

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
        if (isset($request->enable_get_reviews)) {
            $settings = new CrmSettings();
            $settings->where('user_id', $user['id'])->update(['enable_get_reviews' => $request->enable_get_reviews, 'sending_option' => $request->sending_option,
                'smart_routing' => $request->smart_routing, 'review_site' => $request->review_site,
                'reminder' => $request->reminder, 'customize_email' => $request->customize_email, 'customize_sms' => $request->customize_sms]);


            $settings = CrmSettings::where('user_id', $user['id'])->first();
            $settings['business_id'] = $businessId;
            $settings['business_name'] = $businessName;
            $settings['user_email'] = $Useremail;

            $customers = Recipient::whereIn('id', $request->customers)->get()->toArray();
            foreach ($customers as $customer) {

                $request->merge(['verification_code' => $customer['verification_code'], 'recipient_id' => $customer['id'], 'email' => $customer['email'], 'first_name' => $customer['first_name'], 'last_name' => $customer['last_name'], 'smart_routing' => $customer['smart_routing'], 'phone_number' => $customer['phone_number']]);

                if (!empty($settings['smart_routing']) && !empty($settings['enable_get_reviews']) && $settings['enable_get_reviews'] == 'Yes' && !empty($settings['sending_option'])) {
                    if (!empty($settings)) {
                        $this->smsEmailSending($request, $settings);
                    }
                }
            }
        }

        return $this->helpReturn("Review Request Sent Successfully.");
    }

    public function smsEmailSending($request, $settings)
    {

        Log::info($request);
        Log::info(" settings ");
        Log::info($settings);
        $formatToReplace = array(" ", "’", "'", "--", "&", "$", "/", ",", "-", "(", ")", "+", "*", "%", "!", "@", "#", "^", "_", "=", "|", "}", "{", ".", "~", "`", "<", ">");
        $replaceFormat = array("", "-", "", "", "", "", "", "", "", "");

        $FormatedBusiness = str_replace($formatToReplace, $replaceFormat, $settings['business_name']);

        $currentDate = Carbon::now();
        $formatedDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('Y-m-d');

       $encodedurl = backDomain(); //use for local
        // $encodedurl = 'http://localhost/hod_backend/';
        try {
            $msg = '';

            if (strtolower($settings['smart_routing']) == 'enable') {
                $finalRedirectUrlArray = $this->smartRouting($settings['business_id'], $settings['smart_routing'] = 'enable', $request->recipient_id, $allSites = [], $flag = false);

                Log::info("finalRedirectUrlArray");
                Log::info($finalRedirectUrlArray);
            }
            else if (strtolower($settings['smart_routing']) == 'disable') {
                $thirdPartyMaster = TripadvisorMaster::select('third_party_id', 'business_id', 'type', 'average_rating', 'add_review_url', 'review_count')->where('type', $settings['review_site'])->where('business_id', $settings['business_id'])->whereNotNull('name')->get()->toArray();
                $socialMediaMaster = SocialMediaMaster::select('id as third_party_id', 'type', 'add_review_url', 'id', 'average_rating', 'page_reviews_count as review_count')
                    ->where('type', $settings['review_site'])->where('business_id', $settings['business_id'])->whereNotNull('name')->get()->toArray();

                $mergeArray = array_merge($thirdPartyMaster, $socialMediaMaster); //merge both array
                $finalRedirectUrlArray = $mergeArray;
            }

            if (isset($finalRedirectUrlArray['_metadata']['outcomeCode']) && $finalRedirectUrlArray['_metadata']['outcomeCode'] == 200 || isset($finalRedirectUrlArray[0])) {
                try {
                    if (isset($finalRedirectUrlArray[0])) {
                        $siteType = getThirdPartyTypeLongToShortForm($finalRedirectUrlArray[0]['type']);
                    } else {
                        $siteType = getThirdPartyTypeLongToShortForm($finalRedirectUrlArray['records']['type']);
                    }

                    if (isset($settings['sending_option']) && $settings['sending_option'] == '3' && !empty($request->phoneNumber) && !empty($request->email)) {
                        //both sms & email
                        $reviewRequestEmail = ReviewRequest::where(['recipient_id' => $request->recipient_id, 'type'=>'email'])->first();
                        if (!empty($reviewRequestEmail)){
                            Log::info('success email 3');
                            ReviewRequest::where(['recipient_id' => $request->recipient_id, 'type'=>'email'])->update(['date_sent' => $formatedDate, 'recipient_id' => $request->recipient_id, 'site' => $siteType, 'type' => 'email']);
                        }else{
                            ReviewRequest::create(['date_sent' => $formatedDate, 'recipient_id' => $request->recipient_id, 'site' => $siteType, 'type' => 'email']);
                        }

                        $reviewRequestSMS = ReviewRequest::where(['recipient_id' => $request->recipient_id, 'type'=>'sms'])->first();
                        if (!empty($reviewRequestSMS)){
                            Log::info('success sms 3');
                            ReviewRequest::where(['recipient_id' => $request->recipient_id, 'type'=>'sms'])->update(['date_sent' => $formatedDate, 'recipient_id' => $request->recipient_id, 'site' => $siteType, 'type' => 'sms', 'status' => 'READY_TO_SEND']);
                        }else{
                            ReviewRequest::create(['date_sent' => $formatedDate, 'recipient_id' => $request->recipient_id, 'site' => $siteType, 'type' => 'sms', 'message_body' => $msg, 'status' => 'READY_TO_SEND']);
                        }


                        $emailReview = ReviewRequest::where(['recipient_id' => $request->recipient_id, 'type'=>'email'])->first();
                        $smsReview = ReviewRequest::where(['recipient_id' => $request->recipient_id, 'type'=>'sms'])->first();

                        $url = $encodedurl . '/business-review/' . $request->phoneNumber . '/' . $request->verification_code . '/' . $settings['business_id'] . '/' . $smsReview->id;
                        $url = Bitly::getUrl($url); // http://bit.ly/nHcn3

                        if (empty($settings['sms_message'])) {
                            $msg = "Thanks for choosing " . $settings['business_name'] . ".I'd like to invite you to tell us about your experience. Any feedback is appreciated - " . $url;
                        } else {
                            $msg = $settings['sms_message'] . '.' . $url;
                        }

                        /*Twillio Integration*/
                        $phone = '+'.$request->phoneNumber;
                        $this->sendSms($phone, $msg);

                        $smsReview->update(['message_body' => $msg]);

                        if (isset($request->action)) {
                            SendingHistory::where('customer_id', $request->recipient_id)->update(['sms_count' => DB::raw('sms_count + 1'), 'email_count' => DB::raw('email_count + 1'), 'sms_last_sent' => $formatedDate, 'email_last_sent' => $formatedDate]);
                        } else {
                            SendingHistory::create(
                                ['customer_id' => $request->recipient_id, 'sms_count' => 1, 'email_count' => 1, 'sms_last_sent' => $formatedDate, 'email_last_sent' => $formatedDate
                                ]);
                        }
                        if (isset($request->queue) && $request->queue == 'enable') {
                          Mail::to($request->email)->later(2, new CreateSendReviewRequestEmail(
                                $request->firstName,
                                $request->verification_code,
                                $request->email,
                                $FormatedBusiness,
                                $emailReview->id,
                                $settings['business_name'],
                                $settings['user_email'],
                                $settings['customize_email'],
                                $settings['business_id'],
                                $settings['logo_image_src'],
                                $settings['background_image_src'],
                                $settings['top_background_color'],
                                $settings['review_number_color'],
                                $settings['star_rating_color'],
                                $settings['email_subject'],
                                $settings['email_heading'],
                                $settings['email_message'],
                                $settings['positive_answer'],
                                $settings['negative_answer'],
                                $settings['personal_avatar_src'],
                                $settings['full_name'],
                                $settings['company_role'],
                                $settings['email_negative_answer_setup_heading'],
                                $settings['email_negative_answer_setup_message']
                            ));
                        } else {

                            Mail::to($request->email)->send( new CreateSendReviewRequestEmail(
                                $request->firstName,
                                $request->verification_code,
                                $request->email,
                                $FormatedBusiness,
                                $emailReview->id,
                                $settings['business_name'],
                                $settings['user_email'],
                                $settings['customize_email'],
                                $settings['business_id'],
                                $settings['logo_image_src'],
                                $settings['background_image_src'],
                                $settings['top_background_color'],
                                $settings['review_number_color'],
                                $settings['star_rating_color'],
                                $settings['email_subject'],
                                $settings['email_heading'],
                                $settings['email_message'],
                                $settings['positive_answer'],
                                $settings['negative_answer'],
                                $settings['personal_avatar_src'],
                                $settings['full_name'],
                                $settings['company_role'],
                                $settings['email_negative_answer_setup_heading'],
                                $settings['email_negative_answer_setup_message']
                            ));
                        }

                        if (Mail::failures()) {

                        } else {
                            Log::info('success email');
                        }
                    }
                    else if (isset($settings['sending_option']) && $settings['sending_option'] == '4' && !empty($request->phone_number)) {
                        //sms
                        $smsReview = ReviewRequest::create(['date_sent' => '', 'recipient_id' => $request->recipient_id, 'site' => $siteType, 'type' => 'sms', 'status' => 'READY_TO_SEND']);
                        $url = $encodedurl . '/business-review/' . $request->phone_number . '/' . $request->verification_code . '/' . $settings['business_id'] . '/' . $smsReview->id;
                        if (empty($settings['customize_sms'])) {
                            $msg = "Thanks for choosing " . $settings['business_name'] . ".I'd like to invite you to tell us about your experience. Any feedback is appreciated - " . $url;
                        } else {
                            $msg = $settings['customize_sms'] . '.' . $url;
                        }
                        $smsReview->update(['message_body' => $msg]);

                        if (isset($request->action)) {
                            SendingHistory::where('customer_id', $request->recipient_id)->update(['sms_count' => DB::raw('sms_count + 1'), 'sms_last_sent' => $formatedDate]);
                        } else {
                            SendingHistory::create(
                                [
                                    'customer_id' => $request->recipient_id,
                                    'sms_count' => 1,
                                    'email_count' => null,
                                    'sms_last_sent' => $formatedDate,
                                    'email_last_sent' => ''
                                ]);
                        }
                    }
                    else if (isset($settings['sending_option']) && $settings['sending_option'] == '5' && !empty($request->email)) {
                        //email
                        $emailReview = ReviewRequest::create(['date_sent' => $formatedDate, 'recipient_id' => $request->recipient_id, 'site' => $siteType, 'type' => 'email']);

                        if (isset($request->queue) && $request->queue == 'enable') {

                           // Mail::to($request->email)->later(2, new CreateSendReviewRequestEmail($request->first_name, $FormatedBusiness, $settings['business_name'], $request->verification_code, $request->email, $settings['user_email'], $settings['customize_email'], $settings['business_id'], $emailReview->id));
                            ////                           Mail::to($request->email)->later(2,new CreateAddRecipientsEmail($request->first_name,$FormatedBusiness, $settings['business_name'], $request->varification_code, $request->email, $settings['user_email'],$settings['customize_email'],$settings['business_id'],$emailReview->id));
                            Mail::to($request->email)->later(2, new CreateSendReviewRequestEmail(
                                $request->firstName,
                                $request->verification_code,
                                $request->email,
                                $FormatedBusiness,
                                $emailReview->id,
                                $settings['business_name'],
                                $settings['user_email'],
                                $settings['customize_email'],
                                $settings['business_id'],
                                $settings['logo_image_src'],
                                $settings['background_image_src'],
                                $settings['top_background_color'],
                                $settings['review_number_color'],
                                $settings['star_rating_color'],
                                $settings['email_subject'],
                                $settings['email_heading'],
                                $settings['email_message'],
                                $settings['positive_answer'],
                                $settings['negative_answer'],
                                $settings['personal_avatar_src'],
                                $settings['full_name'],
                                $settings['company_role'],
                                $settings['email_negative_answer_setup_heading'],
                                $settings['email_negative_answer_setup_message']
                            ));
                        } else {
                            Mail::to($request->email)->send(new CreateSendReviewRequestEmail($request->firstName, $FormatedBusiness, $settings['business_name'], $request->verification_code, $request->email, $settings['user_email'], $settings['customize_email'], $settings['business_id'], $emailReview->id));
                        }

                        if (Mail::failures()) {

                        } else {
                            Log::info('success email');
                        }

                        if (isset($request->action)) {
                            SendingHistory::where('customer_id', $request->recipient_id)->update(['email_count' => DB::raw('email_count + 1'), 'email_last_sent' => $formatedDate]);
                        } else {
                            SendingHistory::create(
                                [
                                    'customer_id' => $request->recipient_id,
                                    'sms_count' => null,
                                    'email_count' => 1,
                                    'sms_last_sent' => null,
                                    'email_last_sent' => $formatedDate]);
                        }
                    }
                    else if (isset($settings['sending_option']) && $settings['sending_option'] == '2') {
                        Log::info('success phone');

                        if (!empty($request->phoneNumber)) {
                            Log::info('success phone 2');

                            $reviewRequest = ReviewRequest::where(['recipient_id' => $request->recipient_id, 'type'=>'sms'])->first();
                            if (!empty($reviewRequest)){
                                Log::info('success phone 3');
                                ReviewRequest::where(['recipient_id' => $request->recipient_id, 'type'=>'sms'])->update(
                                    [
                                        'date_sent' => $formatedDate,
                                        'recipient_id' => $request->recipient_id,
                                        'site' => $siteType,
                                        'type' => 'sms',
                                        'status' => 'READY_TO_SEND'
                                    ]
                                );
                            }else{
                                ReviewRequest::create(['date_sent' => $formatedDate, 'recipient_id' => $request->recipient_id, 'site' => $siteType, 'type' => 'sms', 'message_body' => $msg, 'status' => 'READY_TO_SEND']);
                            }

                            $smsReview =  ReviewRequest::where(['recipient_id' => $request->recipient_id, 'type'=>'sms'])->first();
                            $url = $encodedurl . 'business-review/' . $request->phoneNumber . '/' . $request->verification_code . '/' . $settings['business_id'] . '/' . $smsReview->id;
                            $url = Bitly::getUrl($url); // http://bit.ly/nHcn3
                            Log::info('success phone url');
                            Log::info($url);
                            if (empty($settings['sms_message'])) {
                                $msg = "Thanks for choosing " . $settings['business_name'] . ".I'd like to invite you to tell us about your experience. Any feedback is appreciated - " . $url;
                            } else {
                                $msg = $settings['sms_message'] . '.' . $url;
                            }
                            $smsReview->update(
                                ['message_body' => $msg]
                            );
                            $phone = '+'.$request->phoneNumber;
                            Log::info($phone);

                            $this->sendSms($phone, $msg);

                            if (isset($request->action)) {
                                SendingHistory::where('customer_id', $request->recipient_id)->update(['sms_count' => DB::raw('sms_count + 1'), 'sms_last_sent' => $formatedDate]);
                            } else {
                                SendingHistory::create(
                                    [
                                        'customer_id' => $request->recipient_id,
                                        'sms_count' => 1,
                                        'email_count' => null,
                                        'sms_last_sent' => $formatedDate,
                                        'email_last_sent' => null
                                    ]
                                );
                            }
                        }
                        else if (!empty($request->email) && empty($request->phoneNumber)) {
                            $emailReview = ReviewRequest::create(['date_sent' => $formatedDate, 'recipient_id' => $request->recipient_id, 'site' => $siteType, 'type' => 'email']);

                            if (isset($request->queue) && $request->queue == 'enable') {
                                //    Mail::to($request->email)->later(2,new CreateAddRecipientsEmail($request->first_name,$FormatedBusiness, $settings['business_name'], $request->varification_code, $request->email, $settings['user_email'],$settings['customize_email'],$settings['business_id'],$emailReview->id));
                                // Mail::to($request->email)->later(2, new CreateSendReviewRequestEmail($request->first_name, $FormatedBusiness, $settings['business_name'], $request->verification_code, $request->email, $settings['user_email'], $settings['customize_email'], $settings['business_id'], $emailReview->id));
                                Mail::to($request->email)->later(2, new CreateSendReviewRequestEmail(
                                    $request->firstName,
                                    $request->verification_code,
                                    $request->email,
                                    $FormatedBusiness,
                                    $emailReview->id,
                                    $settings['business_name'],
                                    $settings['user_email'],
                                    $settings['customize_email'],
                                    $settings['business_id'],
                                    $settings['logo_image_src'],
                                    $settings['background_image_src'],
                                    $settings['top_background_color'],
                                    $settings['review_number_color'],
                                    $settings['star_rating_color'],
                                    $settings['email_subject'],
                                    $settings['email_heading'],
                                    $settings['email_message'],
                                    $settings['positive_answer'],
                                    $settings['negative_answer'],
                                    $settings['personal_avatar_src'],
                                    $settings['full_name'],
                                    $settings['company_role'],
                                    $settings['email_negative_answer_setup_heading'],
                                    $settings['email_negative_answer_setup_message']
                                ));
                            } else {
                                Mail::to($request->email)->send( new CreateSendReviewRequestEmail(
                                    $request->firstName,
                                    $request->verification_code,
                                    $request->email,
                                    $FormatedBusiness,
                                    $emailReview->id,
                                    $settings['business_name'],
                                    $settings['user_email'],
                                    $settings['customize_email'],
                                    $settings['business_id'],
                                    $settings['logo_image_src'],
                                    $settings['background_image_src'],
                                    $settings['top_background_color'],
                                    $settings['review_number_color'],
                                    $settings['star_rating_color'],
                                    $settings['email_subject'],
                                    $settings['email_heading'],
                                    $settings['email_message'],
                                    $settings['positive_answer'],
                                    $settings['negative_answer'],
                                    $settings['personal_avatar_src'],
                                    $settings['full_name'],
                                    $settings['company_role'],
                                    $settings['email_negative_answer_setup_heading'],
                                    $settings['email_negative_answer_setup_message']
                                ));
                            }

                            if (Mail::failures()) {

                            } else {
                                Log::info('success email');
                            }

                            if (isset($request->action)) {
                                SendingHistory::where('customer_id', $request->recipient_id)->update(['email_count' => DB::raw('email_count + 1'), 'email_last_sent' => $formatedDate]);
                            } else {
                                SendingHistory::create(['customer_id' => $request->recipient_id, 'sms_count' => '', 'email_count' => 1, 'sms_last_sent' => null, 'email_last_sent' => $formatedDate]);
                            }

                        }
                    }
                    else if (isset($settings['sending_option']) && $settings['sending_option'] == '1') {
                        //primary email
                        if (!empty($request->email)) {
                            Log::info('here email');
                            Log::info($request->recipient_id);
                            $reviewRequest = ReviewRequest::where(['recipient_id' => $request->recipient_id, 'type'=>'email'])->first();
                            if (!empty($reviewRequest)){
                                Log::info('success phone 3');
                                ReviewRequest::where(['recipient_id' => $request->recipient_id, 'type'=>'email'])->update
                                (
                                    [
                                        'date_sent' => $formatedDate,
                                        'recipient_id' => $request->recipient_id,
                                        'site' => $siteType,
                                        'type' => 'email'
                                    ]
                                );
                            }else{
                                ReviewRequest::create(['date_sent' => $formatedDate, 'recipient_id' => $request->recipient_id, 'site' => $siteType, 'type' => 'email']);
                            }

                            $emailReview = ReviewRequest::where(['recipient_id' => $request->recipient_id, 'type'=>'email'])->first();
                            Log::info("email ");
                            Log::info($emailReview);
                            if (isset($request->queue) && $request->queue == 'enable') {
                                Mail::to($request->email)->later(2, new CreateSendReviewRequestEmail(
                                    $request->firstName,
                                    $request->verification_code,
                                    $request->email,
                                    $FormatedBusiness,
                                    $emailReview->id,
                                    $settings['business_name'],
                                    $settings['user_email'],
                                    $settings['customize_email'],
                                    $settings['business_id'],
                                    $settings['logo_image_src'],
                                    $settings['background_image_src'],
                                    $settings['top_background_color'],
                                    $settings['review_number_color'],
                                    $settings['star_rating_color'],
                                    $settings['email_subject'],
                                    $settings['email_heading'],
                                    $settings['email_message'],
                                    $settings['positive_answer'],
                                    $settings['negative_answer'],
                                    $settings['personal_avatar_src'],
                                    $settings['full_name'],
                                    $settings['company_role'],
                                    $settings['email_negative_answer_setup_heading'],
                                    $settings['email_negative_answer_setup_message']
                                ));
                            } else {
                                Mail::to($request->email)->send( new CreateSendReviewRequestEmail(
                                    $request->firstName,
                                    $request->verification_code,
                                    $request->email,
                                    $FormatedBusiness,
                                    $emailReview->id,
                                    $settings['business_name'],
                                    $settings['user_email'],
                                    $settings['customize_email'],
                                    $settings['business_id'],
                                    $settings['logo_image_src'],
                                    $settings['background_image_src'],
                                    $settings['top_background_color'],
                                    $settings['review_number_color'],
                                    $settings['star_rating_color'],
                                    $settings['email_subject'],
                                    $settings['email_heading'],
                                    $settings['email_message'],
                                    $settings['positive_answer'],
                                    $settings['negative_answer'],
                                    $settings['personal_avatar_src'],
                                    $settings['full_name'],
                                    $settings['company_role'],
                                    $settings['email_negative_answer_setup_heading'],
                                    $settings['email_negative_answer_setup_message']
                                ));
                            }

                            if (Mail::failures()) {

                            } else {
                                Log::info('success email');
                            }
                            if (isset($request->action)) {

                                Log::info("inside action ");
                                Log::info($request->action);

                                SendingHistory::where('customer_id', $request->recipient_id)
                                    ->update(
                                        [
                                            'email_count' => DB::raw('email_count + 1'),
                                            'email_last_sent' => $formatedDate
                                        ]
                                    );
                            } else {
                                // testing
                                SendingHistory::create(
                                    [
                                        'customer_id' => $request->recipient_id,
                                        'sms_count' => null,
                                        'email_count' => 1,
                                        'sms_last_sent' => null,
                                        'email_last_sent' => $formatedDate
                                    ]);
                            }
                        }
                        else if (!empty($request->phone_number) && empty($request->email)) {

                            $smsReview = ReviewRequest::create(['date_sent' => '', 'recipient_id' => $request->recipient_id, 'site' => $siteType, 'type' => 'sms', 'status' => 'READY_TO_SEND']);
                            $url = $encodedurl . '/business-review/' . $request->phone_number . '/' . $request->verification_code . '/' . $settings['business_id'] . '/' . $smsReview->id;
                            if (empty($settings['customize_sms'])) {
                                $msg = "Thanks for choosing " . $settings['business_name'] . ".I'd like to invite you to tell us about your experience. Any feedback is appreciated - " . $url;
                            } else {
                                $msg = $settings['customize_sms'] . '.' . $url;
                            }
                            $smsReview->update(['message_body' => $msg]);
                            if (isset($request->action)) {

                                SendingHistory::where('customer_id', $request->recipient_id)->update(['sms_count' => 1, 'sms_last_sent' => $formatedDate]);
                            } else {
                                SendingHistory::create(['customer_id' => $request->recipient_id, 'sms_count' => 1, 'email_count' => '', 'sms_last_sent' => $formatedDate, 'email_last_sent' => '']);
                            }
                        }

                    }

                } catch (\Exception $e) {
                    Log::info('Email Releven Exception');
                    Log::info($e->getMessage() . ' > ' . $e->getLine());
                }
            }
            else {
                Recipient::where('id', $request->recipient_id)->delete();
                return $finalRedirectUrlArray;
            }

        } catch (Exception $exception) {
            Log::info("smsEmailSending " . $exception->getMessage() . ' > ' . $exception->getLine());
        }
    }

    public function sendSms($phone, $msg){
        try{
            $sid  = env( 'TWILIO_SID' );
            $token = env( 'TWILIO_TOKEN' );
            $twilioNumber = env('TWILIO_FROM');

            $client = new Client($sid, $token);
            $phoneNumber = $phone;
            $message = $msg;

            try {
                $client->messages->create(
                    $phoneNumber,
                    [
                        "body" => $message,
                        "from" => $twilioNumber
                    ]
                );
                Log::info('Message sent to ' . $phoneNumber);
            } catch (TwilioException $e) {
                Log::error(
                    'Could not send SMS notification.' .
                    ' Twilio replied with: ' . $e
                );
            }
            return back()->with( 'success' . " messages sent!" );

            } catch (Exception $exception) {
                Log::info("smsEmailSending " . $exception->getMessage() . ' > ' . $exception->getLine());
            }
    }

    public function smartRouting($businessId, $smartRouting, $reciepentId, $allSites = [], $flag)
    {
        try {

            Log::info("smartRouting > businessId > $businessId (smartRouting $smartRouting ) > ( flag > $flag )" );
            Log::info($allSites);

            $businessName = Business::select('business_name')->where('business_id', $businessId)->first();

            //feedback case
            if ($smartRouting == 'enable' && !empty($allSites)) { //case when user add recipient
                Log::info("smartRouting > main IF ");
                $typeArray = [];
                foreach ($allSites as $value) {
                    $typeArray[] = ['type' => getThirdPartyTypeShortToLongForm($value['site'])];
                }

                $thirdPartyMaster = ThirdPartyMaster::select('third_party_id', 'page_url',  'business_id', 'type', 'average_rating', 'add_review_url', 'review_count')
                    ->whereNotIn('type', $typeArray)
                    ->where('business_id', $businessId)
                    ->whereNotNull('name')
                    ->get()->toArray();

                Log::info("smartRouting thirdPartyMaster");
                Log::info($thirdPartyMaster);

                $socialMediaMaster = SocialMediaMaster::select('id as third_party_id', 'page_url', 'type', 'add_review_url', 'id', 'average_rating', 'page_reviews_count as review_count')
                    ->whereNotIn('type', $typeArray)
                    ->where('business_id', $businessId)
                    ->whereNotNull('name')
                    ->get()->toArray();

                Log::info("smartRouting $socialMediaMaster");
                Log::info($socialMediaMaster);

                $mergeArray = array_merge($thirdPartyMaster, $socialMediaMaster); //merge both array

            }

            else if ($flag == true) {
                //case for when user pass feedback

                Log::info("smartRouting > main ELSE If Flag $flag ");

                $site = ReviewRequest::select('site', 'flag')
                    ->where('recipient_id', $reciepentId)
                    ->first()->toArray();

                $type = getThirdPartyTypeShortToLongForm($site['site']);

                $typeArray = thirdPartySources();

                $thirdPartyMaster = ThirdPartyMaster::select('third_party_id', 'page_url', 'business_id', 'type', 'average_rating', 'add_review_url', 'review_count')
                    ->whereIn('type', $typeArray)
                    ->where('business_id', $businessId)
                    ->whereNotNull('name')
                    ->get()->toArray();

                $socialMediaMaster = SocialMediaMaster::select('id as third_party_id', 'type', 'page_url', 'add_review_url', 'id', 'average_rating', 'page_reviews_count as review_count')
                    ->whereIn('type', $typeArray)
                    ->where('business_id', $businessId)
                    ->whereNotNull('name')->get()->toArray();

                $mergeArray = array_merge($thirdPartyMaster, $socialMediaMaster); //merge both array

            }
            else { //default case mostly use in add and update customer when review require
                Log::info("smartRouting > main ELSE Flag $flag ");


                $thirdPartyMaster = ThirdPartyMaster::select('third_party_id', 'page_url', 'business_id', 'type', 'average_rating', 'add_review_url', 'review_count')
                    ->where('business_id', $businessId)
                    ->whereNotNull('name')
                    ->get()->toArray();

                $socialMediaMaster = SocialMediaMaster::select('id as third_party_id', 'page_url', 'type', 'add_review_url', 'id', 'average_rating', 'page_reviews_count as review_count')
                    ->where('type', 'Facebook')
                    ->where('business_id', $businessId)
                    ->whereNotNull('name')->get()->toArray();

                $mergeArray = array_merge($thirdPartyMaster, $socialMediaMaster); //merge both array

            }

            if (!empty($mergeArray)) {

                if ($smartRouting == 'enable') {
                    //check smart routing

                    //find minimum value of average array
                    $minimumRatingValue = min(array_column($mergeArray, 'average_rating'));

                    Log::info("minimumRatingValue ");
                    Log::info($minimumRatingValue );

                    $minimumRatingValueFound = Arr::where($mergeArray, function ($value, $key) use ($minimumRatingValue) {
                        return $value['average_rating'] == $minimumRatingValue;
                    });


                    Log::info("minimumRatingValueFound ");
                    Log::info($minimumRatingValueFound );

                    $minimumRatingValueFound = array_values($minimumRatingValueFound);

                    Log::info("minimumRatingValueFound COL");
                    Log::info($minimumRatingValueFound );

                    if (count($minimumRatingValueFound) == 1) {
                        // if all values are equal then we use review count in else part
                        $finalRedirectUrlArray = $minimumRatingValueFound[0];

                    } else {

                        $minimumReviewValue = min(array_column($mergeArray, 'review_count')); //again as above we find minimum value of Review Count instead of Rating

                        $minimumReviewValueFound = Arr::where($mergeArray, function ($value, $key) use ($minimumReviewValue) {
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

                    if(empty($finalRedirectUrlArray['add_review_url']))
                    {
                        $finalRedirectUrlArray['add_review_url'] = (!empty($finalRedirectUrlArray['page_url'])) ? $finalRedirectUrlArray['page_url'] : '';
                    }
                }
                else {
                    //if smart routing disable

                    $finalRedirectUrlArray = $mergeArray;
                }

                Log::info("update flag $flag" );

                if ($flag == true) {
                    Log::info("inside flag $flag" );
                    $shortType = '';
                    $shortType = getThirdPartyTypeLongToShortForm($finalRedirectUrlArray['type']);
                    ReviewRequest::where('recipient_id', $reciepentId)->update(['site' => $shortType]);
                }

                return $this->helpReturn("site listing.", $finalRedirectUrlArray);
            }
            else {
                ReviewRequest::where('recipient_id', $reciepentId)->update(['flag' => 'deleted']);
                $messsage = "Unable to find review site. $businessName->name has already removed this site. Please contact $businessName->name.";
                return $this->helpError(404, $messsage);
            }

        } catch (Exception $exception) {
            Log::info("smartRouting " . $exception->getMessage() . " line > " . $exception->getLine() );
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }


    public function addCustomerSettings($request)
    {
        //try {
        $businessObj = new BusinessEntity();
        $checkPoint = $this->setCurrentUser($request->get('token'))->userAllow();

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
        $settings = CrmSettings::where('user_id', $user['id'])->first();

        if (!empty($settings)) {

            CrmSettings::where('id', '=', $settings->id)->update(['enable_get_reviews' => $request->enable_get_reviews, 'smart_routing' => $request->smart_routing, 'sending_option' => $request->sending_option, 'customize_email' => $request->email_message, 'customize_sms' => $request->sms_message, 'review_site' => $request->review_site, 'reminder' => $request->reminder, 'user_id' => $user['id']]);
        } else {
            CrmSettings::create(['enable_get_reviews' => $request->enable_get_reviews, 'smart_routing' => $request->smart_routing, 'sending_option' => $request->sending_option, 'user_id' => $user['id']]);
        }

        return $this->helpReturn("Settings Updated Successfully.");
//
//        } catch (Exception $exception) {
//            return $this->helpError('addCustomerSettings', 'Some Problem happened. please try again.');
//        }
    }

    public function customerSingleData($request){
        $cusData = Recipient::where('id', $request->customerID)->first();
        return $this->helpReturn("Customer Data", $cusData);
    }

    public function customerSettingsList($request)
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

            $settings = CrmSettings::select('id', 'enable_get_reviews', 'smart_routing', 'review_site', 'reminder', 'sending_option', 'customize_email', 'customize_sms')
                ->where('user_id', $userID)
                ->first();

            return $this->helpReturn("Setting List", $settings);

        } catch (Exception $exception) {
            return $this->helpError('customerSettingsList', 'Some Problem happened. please try again.');
        }
    }

    public function customersList($data, $request)
    {
        try
        {
        $businessObj = new BusinessEntity();
        $businessResult = $businessObj->userSelectedBusiness($request);

        if ($businessResult['_metadata']['outcomeCode'] != 200) {
            return $this->helpError(1, 'Problem in selection of your business.');
        }

        $businessResult = $businessResult['records'];
        $businessId = $businessResult['business_id'];
        $userID = $businessResult['user_id'];


        $customers = Recipient::select('id', 'email', 'phone_number', 'created_at', 'first_name', 'last_name', 'enquiries', 'enquiry_source', 'revenue', 'comments')
            ->where('user_id', $userID)
            ->wherenull('deleted_at')
            ->with(['reviewRequest' => function ($q) {
                $q->select('recipient_id', 'status', 'review_status', 'message', 'type');
            }]);
        $data = [];

        $datatable = DataTables::of($customers)
            ->editColumn('first_name', function ($data) {
                return strlen($data->first_name) > 100 ? Crypt::decrypt($data->first_name) : $data->first_name;
            })
            ->editColumn('last_name', function ($data) {
                // return $data->last_name;
                return strlen($data->last_name) > 100 ? Crypt::decrypt($data->last_name) : $data->last_name;
            })->addColumn('name', function ($data) {
                $name = '';
                !empty($data->first_name && $data->last_name) ? $name = $data->first_name . ' ' . $data->last_name : (!empty($data->first_name) ? $name = $data->first_name : (!empty($data->last_name) ? $name = $data->last_name : ''));
                return $name;
            })->addColumn('extra', function ($data) {
                return '';
            });

        $crmSettings = CrmSettings::where('user_id', $userID)->first();
        //add new key for identify front end screen , direct customer or customer with review screen
        $value = !empty($crmSettings['enable_get_reviews']) && $crmSettings['enable_get_reviews'] == 'Yes' ? 'enabled' : 'disabled';


        $data['customers'] = collect($datatable->make(true)->getData());
        $data['enable_get_reviews'] = $value;

        $customersData = $datatable->make(true)->getData();
        $carbon = new Carbon();

        foreach($customersData->data as $index => $customer)
        {
            $createdAt = $carbon->createFromTimestamp(strtotime($customer->created_at),'EST');
            $time =  $createdAt->format('Y-m-d H:i:s');
            $customersData->data[$index]->created_at = $time;
        }

            $data['customers'] = collect($customersData);
            return $this->helpReturn("Customers List", $data);
        } catch (Exception $exception) {
            Log::info(" customersList " . $exception->getMessage());
            return $this->helpError('customersList', 'Some Problem happened. please try again.');
        }
    }

    public function getCustomersById($request)
    {
        try {
            $businessObj = new BusinessEntity();
            $checkPoint = $this->setCurrentUser($request->get('token'))->userAllow();

            if ($checkPoint['_metadata']['outcomeCode'] != 200) {
                return $checkPoint;
            }

            $user = $checkPoint['records'];
            $Useremail = $user['email'];
            $businessResult = $businessObj->userSelectedBusiness($user);

            if ($businessResult['_metadata']['outcomeCode'] != 200) {
                return $this->helpError(1, 'Problem in selection of user business.');
            }

            $customer = Recipient::with(['reviewRequest' => function ($q) {
                $q->select('recipient_id', 'site', 'type', 'date_sent');
            }])
                ->where('id', '=', $request->customer_id)
                ->where('user_id', '=', $user['id'])
                ->select('id', 'email', 'phone_number', 'created_at', 'first_name', 'last_name')
                ->first();
            $settings = CrmSettings::where('user_id', $user['id'])->first();

            $appendArray = null;
            if ($customer != null) {
                $customer = $customer->toArray();
                $name = '';
                $firstName = strlen($customer['first_name']) > 100 ? Crypt::decrypt($customer['first_name']) : $customer['first_name'];
                $lastName = strlen($customer['last_name']) > 100 ? Crypt::decrypt($customer['last_name']) : $customer['last_name'];
                $phone = strlen($customer['phone_number']) > 100 ? Crypt::decrypt($customer['phone_number']) : $customer['phone_number'];
                $email = strlen($customer['email']) > 100 ? Crypt::decrypt($customer['email']) : $customer['email'];
                $date = Carbon::createFromFormat('Y-m-d H:i:s', $customer['created_at'])->format('Y-m-d');
                !empty($firstName && $lastName) ? $name = $firstName . ' ' . $lastName : (!empty($firstName) ? $name = $firstName : (!empty($lastName) ? $name = $lastName : ''));

                $appendArray['id'] = $customer['id'];
                $appendArray['created_at'] = $date;
                $appendArray['name'] = $name;
                $appendArray['review_request'] = $customer['review_request'];
                $appendArray['smart_routing'] = $settings->smart_routing;
                $appendArray['phone_number'] = !empty($phone) ? $phone : '';;
                $appendArray['email'] = !empty($email) ? $email : '';;
                $appendArray['first_name'] = !empty($firstName) ? $firstName : '';
                $appendArray['last_name'] = !empty($lastName) ? $lastName : '';
            } else {
                return $this->helpError('404', 'Customer Not Exist.');
            }

            return $this->helpReturn("Customers List", $appendArray);

        } catch (Exception $exception) {
            return $this->helpError('getCustomersById', 'Some Problem happened. please try again.');
        }
    }

    public function deleteCustomer($request)
    {
        try {
            $ids =  $request->customerID;
            $businessObj = new BusinessEntity();
            $businessResult = $businessObj->userSelectedBusiness($request);

            if ($businessResult['_metadata']['outcomeCode'] != 200) {
                return $this->helpError(1, 'Problem in selection of your business.');
            }

            $businessResult = $businessResult['records'];
            $businessId = $businessResult['business_id'];
            $userID = $businessResult['user_id'];


            Log::info('user_id');
            Log::info($userID);

            Log::info('id');
            Log::info($ids);

            $customer = Recipient::where(['id'=> $ids, 'user_id'=> $userID])->get()->toArray();
            Log::info('customer');
            Log::info($customer);

            if (!empty($customer)) {
                Recipient::where(['id'=> $ids, 'user_id'=> $userID])->delete();
            } else {
                return $this->helpError(404, 'Record Not Exists');
            }

            return $this->helpReturn("Customer Deleted Successfully.");
        } catch (Exception $exception) {
            Log::info("deleteCustomer " . $exception->getMessage() . '> ' . $exception->getLine());
            return $this->helpError('deleteCustomer', 'Some Problem happened. please try again.');
        }
    }

    public function smsEmailSendCronJob($request)
    {
        try {
            /****New Working******/
            //->where('id',1381)
            User::select('id', 'email')->with('singleBusiness', 'recipients.sendingHistory', 'recipients.reviewRequestForNegativeFeedback', 'recipients.reviewRequestForPostitiveFeedback')
                ->chunk(200, function ($users) use ($request) {
                    foreach ($users->toArray() as $user) {
                        Log::info('check recipeints');
                        Log::info($user);

                        if (!empty($user['recipients'])) {
                            foreach ($user['recipients'] as $recipient) {

                                if (!isset($recipient['review_request_for_negative_feedback']) && empty($recipient['review_request_for_negative_feedback']) && !empty($recipient['review_request_for_postitive_feedback'])) {

                                    if (isset($recipient['sending_history'])) {
                                        $checkEmailLastSendDate = Carbon::createFromFormat('Y-m-d H:i:s', $recipient['sending_history']['email_last_sent'])->format('D');
                                    }
                                    if (!empty($recipient['sending_history'])) {

                                        /************Get Email and SMS Sent dates differnce for sending SMS or EMAIL using Cron Job************/
                                        $diff_in_days_for_email = '';
                                        $diff_in_days_for_sms = '';

                                        if (strtotime($recipient['sending_history']['email_last_sent']) != strtotime('0000-00-00 00:00:00')) {

                                            $to_for_email = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', $recipient['sending_history']['email_last_sent']);
                                            $from_for_email = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', Carbon::now());
                                            $diff_in_days_for_email = $to_for_email->diffInDays($from_for_email);

                                        }

                                        if (strtotime($recipient['sending_history']['sms_last_sent']) != strtotime('0000-00-00 00:00:00')) {
                                            $to_for_sms = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', $recipient['sending_history']['sms_last_sent']);
                                            $from_for_sms = \Carbon\Carbon::createFromFormat('Y-m-d H:s:i', Carbon::now());
                                            $diff_in_days_for_sms = $to_for_sms->diffInDays($from_for_sms);

                                        }

                                        /************Get Settings for furthor actions************/
                                        $settings = CrmSettings::where('user_id', $user['id'])->first();
                                        $settings['business_id'] = $user['single_business']['business_id'];
                                        $settings['business_name'] = $user['single_business']['name'];
                                        $settings['user_email'] = $user['email'];

                                        /*********Work Done Due TO encryption*************/
                                        strlen($recipient['first_name']) > 100 ? $firstName = Crypt::decrypt($recipient['first_name']) : $firstName = $recipient['first_name'];
                                        strlen($recipient['email']) > 100 ? $email = Crypt::decrypt($recipient['email']) : $email = $recipient['email'];
                                        strlen($recipient['phone_number']) > 100 ? $phoneNumber = Crypt::decrypt($recipient['phone_number']) : $phoneNumber = $recipient['phone_number'];
                                        /*********Work Done Due TO encryption*************/


                                        if (!empty($settings['smart_routing']) && ($settings['smart_routing'] == 'enable' || $settings['smart_routing'] == 'Enable') && !empty($settings['enable_get_reviews']) && $settings['enable_get_reviews'] == 'Yes' && !empty($settings['sending_option'])) {

                                            /**
                                             * this is for first time when cron job run and just pick record where dates have max 6 day differnce and count equal 1
                                             */
                                            if ($diff_in_days_for_email >= 6 && $recipient['sending_history']['email_count'] == 1 && $diff_in_days_for_sms >= 6 && $recipient['sending_history']['sms_count'] == 1) {
                                                $request->request->add(['queue' => 'enable']);
                                                $request->merge(['verification_code' => $recipient['verification_code'], 'recipient_id' => $recipient['id'], 'phone_number' => $phoneNumber, 'email' => $email, 'first_name' => $firstName, 'action' => 'update']);
                                                $this->smsEmailSending($request, $settings);
                                            } else if ($diff_in_days_for_email >= 6 && $recipient['sending_history']['email_count'] == 1) {
                                                $request->request->add(['queue' => 'enable']);
                                                $request->merge(['verification_code' => $recipient['verification_code'], 'recipient_id' => $recipient['id'], 'phone_number' => '', 'email' => $email, 'first_name' => $firstName, 'action' => 'update']);
                                                $this->smsEmailSending($request, $settings);
                                            } else if ($diff_in_days_for_sms >= 6 && $recipient['sending_history']['sms_count'] == 1) {
                                                $request->merge(['verification_code' => $recipient['verification_code'], 'recipient_id' => $recipient['id'], 'phone_number' => $phoneNumber, 'email' => '', 'first_name' => $firstName, 'action' => 'update']);
                                                $this->smsEmailSending($request, $settings);
                                            }


                                            /**
                                             * this is for second and third time when cron job run and just pick record where dates have max 6 or more days differnce and count equal 1
                                             */
                                            if ($recipient['sending_history']['email_count'] > 1 && $recipient['sending_history']['email_count'] < 4 && $diff_in_days_for_email >= 6 && $recipient['sending_history']['sms_count'] > 1 && $recipient['sending_history']['sms_count'] < 4 && $diff_in_days_for_sms >= 6) {
                                                $request->merge(['verification_code' => $recipient['verification_code'], 'recipient_id' => $recipient['id'], 'phone_number' => $phoneNumber, 'email' => $email, 'first_name' => $firstName, 'action' => 'update']);
                                                $this->smsEmailSending($request, $settings);
                                            } else if ($recipient['sending_history']['email_count'] > 1 && $recipient['sending_history']['email_count'] < 4 && $diff_in_days_for_email >= 6) {
                                                $request->merge(['verification_code' => $recipient['verification_code'], 'recipient_id' => $recipient['id'], 'phone_number' => '', 'email' => $email, 'first_name' => $firstName, 'action' => 'update']);
                                                $this->smsEmailSending($request, $settings);
                                            } else if ($recipient['sending_history']['sms_count'] > 1 && $recipient['sending_history']['sms_count'] < 4 && $diff_in_days_for_sms >= 6) {
                                                $request->merge(['verification_code' => $recipient['verification_code'], 'recipient_id' => $recipient['id'], 'phone_number' => $phoneNumber, 'email' => '', 'first_name' => $firstName, 'action' => 'update']);
                                                $this->smsEmailSending($request, $settings);
                                            }

                                        }
                                        /************RUN Three Cases depend on date difference and sms and email count************/
                                    }
                                }
                            }
                        }
                    }
                });

            return $this->helpReturn("Reminder Emails Send to All Review Requests");
        } catch (Exception $exception) {
            Log::info("smsEmailSendCronJob " . $exception->getMessage());
            return $this->helpError('smsEmailSendCronJob', 'Some Problem happened. please try again.');
        }
    }


    public function updateCRMStats($request)
    {

        try {
            $currentDate = Carbon::now();
            $FormatedCurrentDate = Carbon::createFromFormat('Y-m-d H:i:s', $currentDate)->format('Y-m-d');
            $allUser = User::select('id')->get()->toArray();

            $allCustomer = Recipient::withTrashed()->select('id')->get()->toArray();
            $allReviewRequest = ReviewRequest::select('id')->get()->toArray();
            $allPromo = Promo::select('id')->get()->toArray();

            $CustomerHistoricalStats = DB::table('user_master as um')
                ->join('recipients as cm', 'um.id', '=', 'cm.user_id')
                // ->where('um.id', 184)
                ->whereIn('um.id', $allUser)
                ->where('cm.deleted_at', '=', null)
                ->select('um.id', 'cm.created_at', DB::raw('Count(um.id) as total'))
                ->groupBy('um.id')
//                ->groupBy('cm.created_at')
                ->get()->toArray();


            if (empty($CustomerHistoricalStats)) {
                return $this->helpReturn("No Customer Found");
            }
            $dateFormat = dateFormatUsing();

            foreach ($CustomerHistoricalStats as $customerdata) {

                $CustomerCreatedDate = getFormattedDate($customerdata->created_at);

                $appendCustomerStatsArray[] = [
                    'user_id' => $customerdata->id,
                    'activity_date' => $CustomerCreatedDate,
                    'site_type' => 'CRM',
                    'count' => $customerdata->total,
                    'type' => 'CU',
                ];
            }
            $ReviewRequestStats = DB::table('recipients as cm')
                ->join('reviews_requests as rr', 'cm.id', '=', 'rr.recipient_id')
                ->whereIn('cm.id', $allCustomer)
               ->select('cm.user_id', 'cm.id', 'rr.created_at', DB::raw('count(cm.id) as total'))
                ->groupBy('cm.id')
                ->get()->toArray();

            if (empty($ReviewRequestStats)) {
                return $this->helpReturn("No Review Request Found");
            }

            $dateFormat = dateFormatUsing();

            foreach ($ReviewRequestStats as $reviewrequestdata) {

                $ReviewRequestCreatedDate = getFormattedDate($reviewrequestdata->created_at);

                $appendReviewRequestStatsArray[] = [
                    'user_id' => $reviewrequestdata->user_id,
                    'activity_date' => $ReviewRequestCreatedDate,
                    'site_type' => 'CRM',
                    'count' => $reviewrequestdata->total,
                    'type' => 'RR',
                ];
            }

            $SmsPromo = DB::table('user_master as um')
                ->join('promo as pm', 'um.id', '=', 'pm.user_id')
                ->whereIn('um.id', $allUser)
                ->where('type', 1)
                ->select('um.id', 'pm.created_at', DB::raw('Count(um.id) as total'))
//                ->groupBy('pm.created_at')
                ->groupBy('um.id')
                ->get()->toArray();

            if (empty($SmsPromo)) {
                return $this->helpReturn("No SMS Promo Found");
            }

            $dateFormat = dateFormatUsing();

            foreach ($SmsPromo as $smspromodata) {

                $SMSPromoCreatedDate = getFormattedDate($smspromodata->created_at);

                $appendSmsPromoStatsArray[] = [
                    'user_id' => $smspromodata->id,
                    'activity_date' => $SMSPromoCreatedDate,
                    'site_type' => 'CRM',
                    'count' => $smspromodata->total,
                    'type' => 'SP',
                ];
            }


            $EmailPromo = DB::table('user_master as um')
                ->join('promo as pm', 'um.id', '=', 'pm.user_id')
                ->whereIn('um.id', $allUser)
                //              ->where('um.created_at', '<=', $FormatedCurrentDate)
                ->where('type', 2)
                ->select('um.id', 'pm.created_at', DB::raw('Count(um.id) as total'))
                ->groupBy('um.id')
                ->get()->toArray();
            if (empty($EmailPromo)) {
                return $this->helpReturn("No Email Promo Found");
            }

            $dateFormat = dateFormatUsing();

            foreach ($EmailPromo as $emailpromodata) {

                $EmailPromoCreatedDate = getFormattedDate($emailpromodata->created_at);

                $appendEmailPromoStatsArray[] = [
                    'user_id' => $emailpromodata->id,
                    'activity_date' => $EmailPromoCreatedDate,
                    'site_type' => 'CRM',
                    'count' => $emailpromodata->total,
                    'type' => 'EP',
                ];
            }

            StatTracking::whereIn('recipient_id', $allCustomer)->delete();
            StatTracking::whereIn('user_id', $allUser)->delete();
            StatTracking::insert($appendCustomerStatsArray);
            StatTracking::insert($appendReviewRequestStatsArray);
            StatTracking::insert($appendSmsPromoStatsArray);
            StatTracking::insert($appendEmailPromoStatsArray);

            $finalArray[] = [
                'Customers Stats' => $appendCustomerStatsArray,
                'Review Request' => $appendReviewRequestStatsArray,
                'SMS Promo Stats' => $appendSmsPromoStatsArray,
                'Email Promo Stats' => $appendEmailPromoStatsArray,
            ];
            return $this->helpReturn("Update CRM Module Stats", $finalArray);

        } catch (Exception $exception) {
            Log::info("updateCustomersStats " . $exception->getMessage());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }


    public function addCustomersUsingFile($request)
    {

        try {

            $file = $request->file('file');

            Log::info("file ");
            Log::info($file);

            $extension = $file->getClientOriginalExtension();
            $fileName = $file->getFilename() . '.' . $extension;
            $path = request()->file('file')->getRealPath();

            Log::info("extension ");
            Log::info($extension);

            Log::info("fileName ");
            Log::info($fileName);

            Log::info("path =");
            Log::info($path);

            $businessObj = new BusinessEntity();
            $businessResult = $businessObj->userSelectedBusiness($request);
            Log::info("business =");
            Log::info($businessResult);

            if ($businessResult['_metadata']['outcomeCode'] != 200) {
                return $this->helpError(1, 'Problem in selection of your business.');
            }

            $businessResult = $businessResult['records'];
            $businessId = $businessResult['business_id'];
            $userID = $businessResult['user_id'];
            $businessName = $businessResult['business_name'];

            /**************************SAVE CSV FILE**********************/
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                Log::info("enter =");
                Log::info($file);

                $extension = $file->getClientOriginalExtension();
                $fileName = $file->getFilename() . '.' . $extension;
                $path = request()->file('file')->getRealPath();

                Log::info("my path =");
                Log::info($path);
                Storage::disk('local')->put($fileName, File::get($file));
            }

            /****Get Saved File***/

            $file = fopen(storage_path('app/' . $fileName), "r");

            $flag = true;
            $appendArray = [];

            $thirdPartyMaster = TripadvisorMaster::select('third_party_id', 'business_id', 'type', 'average_rating', 'add_review_url', 'review_count')->where('business_id', $businessId)->whereNotNull('name')->get()->toArray();
            $socialMediaMaster = SocialMediaMaster::select('id as third_party_id', 'type', 'add_review_url', 'id', 'average_rating', 'page_reviews_count as review_count')
                ->where('type', 'Facebook')
                ->where('business_id', $businessId)->whereNotNull('name')->get()->toArray();

            Log::info("my party =");
            Log::info($thirdPartyMaster);

            $mergeArray = array_merge($thirdPartyMaster, $socialMediaMaster); //merge both array
            $settings = CrmSettings::where('user_id', $userID)->first();

            $checkRecord = Recipient::select('email', 'phone_number')->where('user_id', $userID)->get()->toArray();
            $columnEmail = array_column($checkRecord, 'email');
            $columnPhone = array_column($checkRecord, 'phone_number');

            $emailDuplicate = array();
            $phoneNumberDuplicate = array();


            $i = 1;
            while (($data = fgetcsv($file, 1000, ",")) !== FALSE) {  //read the csv file
                Log::info("i am here now =");
                if ($flag != true) { //not include titles in excell sheets
                    if (!empty($data['0']) && trim($data[0]) || !empty($data['1']) && trim($data[1]) || !empty($data['2']) && trim($data[2]) || !empty($data['3']) && trim($data[3])) {

                        /****New Working ******/
                        $mobile = !empty($data[3]) ? $data[3] : '';
                        $filterPhoneNumber = filterPhoneNumber($mobile);

                        $verificationCode = randomString();
                        $email = !empty($data[0]) ? $data[0] : '';
                        $first_name = !empty($data[1]) ? $data[1] : '';
                        $last_name = !empty($data[2]) ? $data[2] : '';

                        $check_email = 0;
                        $check_phone_number = 0;
                        $emailExist = 0;
                        $phoneExist = 0;

                        /******check record not duplicate********/
                        if ($i == 1) {

                            if (!empty($filterPhoneNumber)) {
                                $phoneNumberDuplicate [] = $filterPhoneNumber;
                            }

                            if (!empty($email)) {
                                $emailDuplicate [] = $email;
                            }

                        } else {
                            $checkEmailExist = in_array($email, $emailDuplicate);

                            if ($checkEmailExist == true) {

                                $emailExist = 1;
                            } else {


                                if (!empty($email)) {
                                    $emailDuplicate [] = $email;
                                }

                            }

                            $checkPhoneExist = in_array($filterPhoneNumber, $phoneNumberDuplicate);

                            if ($checkPhoneExist == true) {
                                $phoneExist = 1;

                            } else {
                                if (!empty($filterPhoneNumber)) {
                                    $phoneNumberDuplicate [] = $filterPhoneNumber;
                                }
                            }
                        }

                        $i++;
                        /***********************************Custom Validation due to encrypt data***************************************/
                        if ($phoneExist != 1 && $emailExist != 1) {
                            Log::info('cross condtion');
                            if (!empty($checkRecord)) {
                                if (!empty($email)) {
                                    $checkindex = in_array($email, $columnEmail);

                                    if ($checkindex == true) {
                                        $check_email = 1;
                                    }
                                }
                                if (!empty($filterPhoneNumber)) {
                                    $checkPhoneindex = in_array($filterPhoneNumber, $columnPhone);
                                    if ($checkPhoneindex == true) {
                                        $check_phone_number = 1;
                                    }
                                }
                            }

                            Log::info($check_email);
                            Log::info($check_phone_number);
                            if ($check_email != 1 && $check_phone_number != 1) {
                                Log::info($email);
                                $appendArray[] = [
                                    'email' => $email,
                                    'first_name' => $first_name,
                                    'last_name' => $last_name,
                                    'phone_number' => $filterPhoneNumber,
                                    'verification_code' => $verificationCode,
                                    'user_id' => $userID,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s'),
                                ];

                                $appendArray2[] = [
                                    'email' => $email,
                                    'phone_number' => $filterPhoneNumber,

                                ];
                            }
                        }
                        /****New Working ******/
                    }
                } else {
                    //validation of csv file titles
                    if (!empty($data['0']) && $data['0'] == 'email_address' && !empty($data['1']) && $data['1'] == 'first_name' && !empty($data['2']) && $data['2'] == 'last_name' && !empty($data['3']) && $data['3'] == 'phone_number') {

                    } else {
                        return $this->helpError(3, 'Incorrect column title. Review the column titles and make sure they match the required format.');
                    }
                }
                $flag = false;
            }
            fclose($file);


            /**************************GET FILE DATA IN ARRAY AND PASS TO LOOP FOR ONE BY ONE PROCESSING**********************/

            /*******new working*****/


            $firstId = Recipient::where('user_id', $userID)->orderBy('id', 'desc')->first();

            Recipient::insert($appendArray);
            if ($firstId != null) {
                $firstidArray = ['first_id' => $firstId->id, 'flag' => 'yes'];
            } else {
                $firstId = Recipient::where('user_id', $userID)->first();
                $firstidArray = ['first_id' => $firstId->id, 'flag' => 'no'];
            }

            if (!empty($fileName)) {
                Storage::disk('local')->delete($fileName);
            }

            return $this->helpReturn("Contacts Added Successfully.", $firstidArray);
        } catch (Exception $exception) {
            Log::info("file customer section " . $exception->getMessage() . ' > ' . $exception->getLine());
            return $this->helpError('addCustomersUsingFile', 'Some Problem happened. please try again.');
        }
    }

    public function uploadCustomersFile($request)
    {
        try {
            $file = $request->file('file');

            Log::info("file ");
            Log::info($file);

            $extension = $file->getClientOriginalExtension();
            $fileName = $file->getFilename() . '.' . $extension;
            $path = request()->file('file')->getRealPath();

            Log::info("extension ");
            Log::info($extension);

            Log::info("fileName ");
            Log::info($fileName);

            Log::info("path =");
            Log::info($path);

            $businessObj = new BusinessEntity();
            $businessResult = $businessObj->userSelectedBusiness($request);

            if ($businessResult['_metadata']['outcomeCode'] != 200) {
                return $this->helpError(1, 'Problem in selection of your business.');
            }

            $businessResult = $businessResult['records'];
            $businessId = $businessResult['business_id'];
            $userID = $businessResult['user_id'];
            $businessName = $businessResult['business_name'];

            /**************************SAVE CSV FILE**********************/
            if ($request->hasFile('file')) {
                $file = $request->file('file');

                $extension = $file->getClientOriginalExtension();
                $fileName = $file->getFilename() . '.' . $extension;
                $path = request()->file('file')->getRealPath();
                Storage::disk('local')->put($fileName, File::get($file));
            }


            if(!empty($fileName))
            {
                UserReviewsFiles::create([
                    'user_id' => $userID,
                    'business_id' => $businessId,
                    'file_name' => $fileName,
                ]);

                return $this->helpReturn("File uploaded Successfully.");
            }

            return $this->helpError(404, 'File not upload. Please try again');
        } catch (Exception $exception) {
            Log::info("file customer section " . $exception->getMessage() . ' > ' . $exception->getLine());
            return $this->helpError(1, 'Some Problem happened. please try again.');
        }
    }

    public function smsEmailSendBackgroundJob($request)
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
            $businessName = $businessResult['business_name'];
            JWTAuth::setToken($request->input('token'));
            $user = JWTAuth::toUser();
            Log::info($user);

            /**************************SAVE CSV FILE**********************/

            $flag = true;

            $thirdPartyMaster = TripadvisorMaster::select('third_party_id', 'business_id', 'type', 'average_rating', 'add_review_url', 'review_count')->where('business_id', $businessId)->whereNotNull('name')->get()->toArray();
            $socialMediaMaster = SocialMediaMaster::select('id as third_party_id', 'type', 'add_review_url', 'id', 'average_rating', 'page_reviews_count as review_count')
                ->where('type', 'Facebook')
                ->where('business_id', $businessId)->whereNotNull('name')->get()->toArray();
            $mergeArray = array_merge($thirdPartyMaster, $socialMediaMaster); //merge both array
            Log::info("thirds party");
            Log::info($thirdPartyMaster);
            $settings = new CrmSettings();
            $settings->where('user_id', $user['id'])->update(['enable_get_reviews' => $request->enable_get_reviews, 'sending_option' => $request->sending_option,
                'smart_routing' => $request->smart_routing, 'review_site' => $request->review_site,
                'reminder' => $request->reminder, 'customize_email' => $request->customize_email, 'customize_sms' => $request->customize_sms]);
            $settings = CrmSettings::where('user_id', $user['id'])->first();
            Log::info("settings");
            Log::info($settings);
            /**************************GET FILE DATA IN ARRAY AND PASS TO LOOP FOR ONE BY ONE PROCESSING**********************/
            if ($request->flag == 'yes') {
                $condition = '>';

            } else if ($request->flag == 'no') {
                $condition = '>=';
            }

            Recipient::where('user_id', $user['id'])->where('id', $condition, $request->first_id)->chunk(200, function ($recipients) use ($request, $businessId, $businessName, $user, $mergeArray, $settings) {

                foreach ($recipients as $recipient) {
                    $request->merge(['verification_code' => $recipient->verification_code, 'recipient_id' => $recipient->id, 'first_name' => $recipient->first_name, 'phone_number' => $recipient->phone_number, 'email' => $recipient->email]);
                    if (!empty($mergeArray)) {
                        //get Settings for furthor actions
                        $settings['business_id'] = $businessId;
                        $settings['business_name'] = $businessName;
                        $settings['user_email'] = $user['email'];

                        if (!empty($settings['smart_routing']) && !empty($settings['enable_get_reviews']) && $settings['enable_get_reviews'] == 'Yes' && !empty($settings['sending_option'])) {
                            //   if (!empty($settings['smart_routing']) && ($settings['smart_routing'] == 'enable' || $settings['smart_routing'] == 'Enable') && !empty($settings['enable_get_reviews']) && $settings['enable_get_reviews'] == 'Yes' && !empty($settings['sending_option'])) {
                            if (!empty($settings)) {

                                $request->request->add(['queue' => 'enable']);
                                $this->smsEmailSending($request, $settings);
                            }
                        }
                    } else {
                        Log::info('not submit');
                    }

                }
            });

            return $this->helpReturn("Email Send To Customer Successfully.");
        } catch (Exception $exception) {
            Log::info("file customer section " . $exception->getMessage());
            return $this->helpError('smsEmailSendBackgroundJob', 'Some Problem happened. please try again.');
        }
    }


    public function searchCustomers($request)
    {
        try {
            $businessObj = new BusinessEntity();

            JWTAuth::setToken($request->input('token'));
            $userData = JWTAuth::toUser();
            $user = $userData;

            $businessResult = $businessObj->userSelectedBusiness($request);

            if ($businessResult['_metadata']['outcomeCode'] != 200) {
                return $this->helpError(1, 'Problem in selection of user business.');
            }
            $businessId = $businessResult['records']['business_id'];
            $businessName = $businessResult['records']['business_name'];
            $settings = [];
            $items = [];
            $appendArray = [];

            $appendCustomerArray = [];
            if (!empty($request->keyword)) {
                $keyword = $request->keyword;
                $items = Recipient::where('user_id', $user['id'])->get()->filter(function ($record) use ($keyword) {
                    if ($record->first_name == $keyword || $record->last_name == $keyword) {
                        return $record;
                    }
                })->toArray();

                $settings = Recipient::where('user_id', $user['id'])
                    ->Where('phone_number', 'like', '%' . $request->keyword . '%')
                    ->orWhere('email', 'like', '%' . $request->keyword . '%')
                    ->get()->toArray();
                $appendArray = array_unique(array_merge($items, $settings), SORT_REGULAR);

            } else {
                return $this->helpError(1, 'Please enter Keyword.');
            }

            return $this->helpReturn("Get Customer Successfully.", $appendArray);
        } catch (Exception $exception) {
            Log::info("file customer section " . $exception->getMessage());
            return $this->helpError('addCustomersUsingFile', 'Some Problem happened. please try again.');
        }
    }


    public function getThirdParties($request)
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

            $result = [];
            $facebook = [];
            $thirdParties = [];

            $thirdParties = TripadvisorMaster::select('type')
                ->where('business_id', $businessId)->where('name', '!=', '')
                ->get()->toArray();



            $facebook = SocialMediaMaster::select('type')->where('type', 'Facebook')->where('business_id', $businessId)->where('name', '!=', '')
                ->get()->toArray();

            $result = array_merge($thirdParties, $facebook);

            return $this->helpReturn("Third Parties.", $result);
        } catch (Exception $exception) {
            Log::info("getThirdParties crm " . $exception->getMessage());
            return $this->helpError('getThirdParties', 'Some Problem happened. please try again.');
        }
    }

    public function addCustomForm($request){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();

        $user_id = CustomerFormSettings::select('user_id')->where(['user_id' => $userData['id'], 'type' => $request->type])->first();
        if ($user_id == null){
            if ($request->type == 'form'){
                $rec = CustomerFormSettings::create([
                    'user_id' => $userData['id'],
                    'type' => $request->type,
                    'width' => $request->fieldWidth,
                    'height' => $request->fieldHeight,
                    'fontSize' => $request->fieldFontSize,
                    'fontColor' => $request->fieldFontColor,
                    'labelColor' => $request->fieldLabelFontColor,
                    'labelFontSize' => $request->fieldLabelFontSize
                ]);
            }else if ($request->type == 'button'){
                $rec = CustomerFormSettings::create([
                    'user_id' => $userData['id'],
                    'type' => $request->type,
                    'btnWidth' => $request->btnWidth,
                    'btnHeight' => $request->btnHeight,
                    'fontSize' => $request->btnFontSize,
                    'fontColor' => $request->btnFontColor,
                    'backgroundColor' => $request->btnBackgroundColor,
                    'borderColor' => $request->btnBorderColor,
                ]);
            }else if ($request->type == 'head'){
                $rec = CustomerFormSettings::create([
                    'user_id' => $userData['id'],
                    'type' => $request->type,
                    'headColor' => $request->headColor,
                    'headFontSize' => $request->headFontSize,
                    'headingText' => $request->headText,
                ]);
            }else if ($request->type == 'font'){
                $rec = CustomerFormSettings::create([
                    'user_id' => $userData['id'],
                    'type' => $request->type,
                    'allFontFamily' => $request->formFontFamily,
                ]);
            }
        }else{
            if ($request->type == 'form'){
                $rec = CustomerFormSettings::where(['user_id' => $userData['id'], 'type' => $request->type])
                    ->update([
                        'type' => $request->type,
                        'width' => $request->fieldWidth,
                        'height' => $request->fieldHeight,
                        'fontSize' => $request->fieldFontSize,
                        'fontColor' => $request->fieldFontColor,
                        'labelColor' => $request->fieldLabelFontColor,
                        'labelFontSize' => $request->fieldLabelFontSize
                    ]);
            }else if ($request->type == 'button'){
                $rec = CustomerFormSettings::where(['user_id' => $userData['id'], 'type' => $request->type])
                    ->update([
                        'type' => $request->type,
                        'btnWidth' => $request->btnWidth,
                        'btnHeight' => $request->btnHeight,
                        'fontSize' => $request->btnFontSize,
                        'fontColor' => $request->btnFontColor,
                        'backgroundColor' => $request->btnBackgroundColor,
                        'borderColor' => $request->btnBorderColor,
                    ]);
                }else if ($request->type == 'head'){
                $rec = CustomerFormSettings::where(['user_id' => $userData['id'], 'type' => $request->type])
                    ->update([
                        'type' => $request->type,
                        'headColor' => $request->headColor,
                        'headFontSize' => $request->headFontSize,
                        'headingText' => $request->headText,
                    ]);
                }else if ($request->type == 'font'){
                $rec = CustomerFormSettings::where(['user_id' => $userData['id'], 'type' => $request->type])
                    ->update([
                        'type' => $request->type,
                        'allFontFamily' => $request->formFontFamily,
                    ]);
                }
            }
        return $this->helpReturn("Custom Form.");
    }

    public function deleteFormField($request){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();

        if ($request){
            $deleted = DeletedFields::create([
                'user_id' => $userData['id'],
                'field_id' => $request->get('field_id'),
                'field_name' => $request->get('field_name')
            ]);
        }
        return $this->helpReturn("Field Deleted.", $deleted);
    }

    public function addFormField($request){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();

        if ($request){
            $new = NewFields::create([
                'user_id' => $userData['id'],
                'field_type' => $request->get('field_type'),
                'field_name' => $request->get('field_name'),
                'field_placeholder' => $request->get('field_placeholder'),
                'label' => $request->get('label')
            ]);
        }
        return $this->helpReturn("Field Added.", $new);
    }

    public function customerOverview($request){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();

        $user_id = $userData['id'];

        $revenue = Recipient::where('user_id', $user_id)->sum('revenue');

        $walkIn = Recipient::where(['user_id'=> $user_id, 'enquiries' => 'Walk In'])->get()->count();

        $customer = Recipient::where(['user_id'=> $user_id, 'enquiries' => 'Customer'])->get()->count();


        $statusData['revenue'] = $revenue;
        $statusData['walkIn'] = $walkIn;
        $statusData['customer'] = $customer;

        return $this->helpReturn("Customer Form.", $statusData);
    }

    public function customerSearch($request){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();

        $user_id = $userData['id'];

        $result = Recipient::where('user_id', $user_id)
            ->where('first_name', 'like', '%' . $request->searchVal . '%')
            ->orWhere('last_name', 'like', '%' . $request->searchVal . '%')
            ->orWhere('email', 'like', '%' . $request->searchVal . '%')
            ->get();

        return $this->helpReturn("Customer Form.", $result);
    }

    public function customerLeads($request){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();

        $user_id = $userData['id'];

        $graphStatsQuery = CustomerLeads::where('user_id', $user_id);
        $graphStats = $graphStatsQuery->select('activity_date', 'count')->get()->toArray();

        /*New query for all Data*/
            $last_twelve_month_ary = $salesTotalAry = [];
            $varCounter = 0;
            for ($g = 11; $g > -1; $g--){
                $varCounter++;
                $bar_year = date("Y", strtotime("-$g months"));
                $bar_month = date("m", strtotime("-$g months"));
                //$last_twelve_month_ary[] = '"'.date("M Y", strtotime("-$g months")).'"';

                ${'standard_query_'.$varCounter} = clone $graphStatsQuery;
                //prepare condition
                $data = ${'standard_query_'.$varCounter}->whereMonth('activity_date', '=', $bar_month)->whereYear('activity_date', '=', $bar_year)->sum('count') ;
                $last_twelve_month_ary[date("M Y", strtotime("-$g months"))] = $data;
            }

            $encodedData = [];
            $k= 0;
            foreach($last_twelve_month_ary as $index => $val)
            {
                $encodedData[$k]['activity_date'] = $index;
                $encodedData[$k]['count'] = $val;

                $k++;
            }
            $graphStats = $encodedData;
            $counts = $graphStatsQuery->sum('count');

            $statusData['count'] = $counts;
            $statusData['graph_data'] = $graphStats;
        /*New query for all Data*/
        return $this->helpReturn("Customer Leads.", $statusData);
    }

    public function customerLeadsGrowth($request){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();

        $user_id = $userData['id'];

        /*For Current Month records*/
        $graphStatsQueryCurrent = CustomerLeads::where('user_id', $user_id);

        $currentMonth = date('m');
        $graphStatsQueryCurrent->where(function ($query) use ($currentMonth) {
                $query->whereRaw('MONTH(activity_date) = ?',[$currentMonth]);
        });

        $graphStatsCurrentMonth = $graphStatsQueryCurrent->select('activity_date', 'count')->count();
        /*For Current Month records*/

        /*For previous Month records*/
        $graphStatsQueryLast = CustomerLeads::where('user_id', $user_id);

        $lastMonth = date('m', strtotime("-1 month"));
        $graphStatsQueryLast->where(function ($query) use ($lastMonth) {
            $query->whereRaw('MONTH(activity_date) = ?',[$lastMonth]);
        });

        $graphStatsLastMonth = $graphStatsQueryLast->select('activity_date', 'count')->count();

        $graphStatsQueryTotal = CustomerLeads::where('user_id', $user_id)->count();
        /*For previous Month records*/

        if (!empty($graphStatsQueryTotal)){
            /*Total Calculations*/
            $total = ($graphStatsCurrentMonth - $graphStatsLastMonth)/$graphStatsQueryTotal;
            $totalPercent = $total*100;
        }else{
            $totalPercent = 0;
        }

        $statusData['percent'] = $totalPercent;

        return $this->helpReturn("Customer LeadsPercent.", $statusData);
    }
}
