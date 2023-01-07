<?php

namespace Modules\CRM\Http\Controllers;

use App\Services\SessionService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use Modules\Business\Models\Countries;
use Modules\CRM\Entities\CRMEntity;
use Modules\CRM\Entities\GetReviewsEntity;
use Modules\CRM\Models\CrmSettings;
use Modules\CRM\Models\CustomerFormSettings;
use Modules\CRM\Models\DeletedFields;
use Modules\CRM\Models\NewFields;
use Tymon\JWTAuth\Facades\JWTAuth;
use Log;

class CRMController extends Controller
{

    protected $crmEntity;

    protected $data;

    public function __construct()
    {
        $this->crmEntity = new CRMEntity();
    }

    public function updateCustomer(Request $request)
    {
        return $this->crmEntity->updateCustomer($request);
    }

    public function addCustomerSettings(Request $request)
    {
        return $this->crmEntity->addCustomerSettings($request);
    }

    public function customerSettingsList(Request $request)
    {
        return $this->crmEntity->customerSettingsList($request);
    }

    public function singleCustomerData(Request $request)
    {
        return $this->crmEntity->customerSingleData($request);
    }
    public function customersList(Request $request)
    {
        $this->data['moduleView'] = 'get_more_reviews';

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();

        $this->data['userData'] = $userData;

        $data = [
            'screen' => 'web',
            'start' => 0,
            'length' => 1
        ];
        $responseData = $this->crmEntity->customersList($data, $request);

        $this->data['countryCodes'] = Countries::all()->toArray();

        $thirdPartiesList = $this->crmEntity->getThirdParties($request);

        $this->data['third_parties_list'] = $thirdPartiesList['records'];

        $customerSettingsList = $this->crmEntity->customerSettingsList($request);

        $this->data['reviewRequestSettings'] = $customerSettingsList['records'];

        $this->data['enable_get_reviews'] = $responseData['records']['enable_get_reviews'];

        $myData = $this->data;
        return response()->json(compact('myData'));
    }


    public function showRecipientList(Request $request)
    {
        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $this->data['userData'] = $userData;
        $this->data['moduleView'] = 'review_request';

        $this->data['enable_get_reviews'] = '';
        $this->data['third_parties_list'] = [];
        $this->data['reviewRequestSettings'] = [];
        $this->data['countryCodes'] = [];

        try {
            $reviewsEntity = new GetReviewsEntity();

            $recipientList = $reviewsEntity->getRecipientsList($request);


            if ($recipientList['_metadata']['outcomeCode'] == 200) {
                try {
                    $thirdPartyResponse = $reviewsEntity->checkThirdParties($request);

                    if ($thirdPartyResponse['records']['flag'] == 0) {
                        $this->data['flag'] = 0;
                        $this->data['message'] = 'We detected that you have not added your business on Facebook, or Google My Business. In order to use Get Reviews, you need to register your business in at least one of these sites.';
                    }
                } catch (Exception $e) {
                }

                /*  CRM API */
                /* ---------------------------------------------------*/

                $data = ['screen' => 'web'];
                $responseCRMData = $this->crmEntity->customersList($data, $request);

                if ($responseCRMData['_metadata']['outcomeCode'] == 200) {
                    $this->data['enable_get_reviews'] = $responseCRMData['records']['enable_get_reviews'];
                }
                /* ---------------------------------------------------*/

               // $this->data['countryCodes'] = Countries::all()->toArray();

                /* ---------------------------------------------------*/
                $thirdPartiesList = $this->crmEntity->getThirdParties($request);

                $this->data['third_parties_list'] = $thirdPartiesList['records'];

                /* ---------------------------------------------------*/

                $customerSettingsList = $this->crmEntity->customerSettingsList($request);

                $this->data['reviewRequestSettings'] = $customerSettingsList['records'];
                /* ---------------------------------------------------*/
                /*  CRM API */

                $this->data['records'] = $recipientList['records'];
                $data = $this->data;
                return response()->json(compact('data'));
                //return view('layouts.crm-customers.recipient', $this->data);
            } else {
                $this->data['flag'] = 0;
                $this->data['message'] = 'Recipients not found';
                $data = $this->data;
                return response()->json(compact('data'));
            }
        } catch (Exception $e) {
            $this->data['flag'] = 0;
            $this->data['message'] = 'Problem in retrieving reviews list. Please try again later';
            $data = $this->data;
            return response()->json(compact('data'));
        }
    }

    public function getCustomersById(Request $request)
    {
        return $this->crmEntity->getCustomersById($request);
    }

    public function smsEmailSendCronJob(Request $request)
    {
        return $this->crmEntity->smsEmailSendCronJob($request);
    }

    public function updateCRMStats(Request $request)
    {
        return $this->crmEntity->updateCRMStats($request);
    }

    public function searchCustomers(Request $request)
    {
        return $this->crmEntity->searchCustomers($request);
    }

    public function getThirdParties(Request $request)
    {
        return $this->crmEntity->getThirdParties($request);
    }

    public function sendExistingCustomerReviewRequest(Request $request)
    {
        return $this->crmEntity->sendExistingCustomerReviewRequest($request);
    }

    public function getCRMCustomersList(Request $request)
    {
        try {
            $data = ['screen' => 'web'];
            $responseData = $this->crmEntity->customersList($data, $request);

            $data = [
                "draw" => 1,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
            ];

            if (!empty($responseData['records']['customers'])) {
                $data = $responseData['records']['customers'];
            }
            return response()->json(compact('data'));
            //return $data;
        } catch (Exception $e) {
            Log::info("getCRMCustomersList > " . $e->getMessage());

            $data = [
                "draw" => 1,
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => [],
            ];
            return response()->json(compact('data'));
            //return $data;
        }
    }

    public function addCustomer(Request $request)
    {
        if(empty($request->get('u_id'))) {

            $data['first_name'] = $request->get('firstName');
            $data['last_name'] = $request->get('lastName');
            $data['email'] = $request->get('email');
            $data['phone_number'] = $request->get('phoneNumber');
            $data['country'] = $request->get('country');
            $data['country_code'] = $request->get('countryCode');

            $data['enquiries'] = $request->get('status');
            $data['enquiry_source'] = null;
            $data['revenue'] = $request->get('cusRevenue');
            $data['comments'] = $request->get('cusComment');

            if ($request->get('enable_get_reviews')) {
                $data['enable_get_reviews'] = $request->get('enable_get_reviews');
            }

            if ($request->get('smart_routing')) {
                $data['smart_routing'] = $request->get('smart_routing');
            }

            if ($request->get('sending_option')) {
                $data['sending_option'] = $request->get('sending_option');
            }

            if ($request->get('review_site')) {
                $data['review_site'] = $request->get('review_site');
            }

            if ($request->get('reminder')) {
                $data['reminder'] = $request->get('reminder');
            }
            if ($request->get('customize_email')) {
                $data['customize_email'] = $request->get('customize_email');
            }
            if ($request->get('customize_sms')) {
                $data['customize_sms'] = $request->get('customize_sms');
            }
            if ($request->get('customer_id')) {
                $data['customer_id'] = $request->get('customer_id');
            }
            if ($request->get('verification_code')) {
                $data['verification_code'] = $request->get('verification_code');
            }
        }
        $responseData = $this->crmEntity->addCustomers($request);

        return $responseData;
    }

    public function deleteCustomer(Request $request)
    {
        return $this->crmEntity->deleteCustomer($request);
    }

    public function uploadCustomersCSV(Request $request)
    {
        Log::info("file ");
        Log::info($request->file);
        return $this->crmEntity->addCustomersUsingFile($request);
    }

    public function uploadCustomersFile(Request $request)
    {
        Log::info("file ");
        Log::info($request->file);
        return $this->crmEntity->uploadCustomersFile($request);
    }

    public function CRMBackgroundService(Request $request)
    {
        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $this->data['userData'] = $userData;

        return $this->crmEntity->smsEmailSendBackgroundJob($request);
    }

    public function emailPersonalizeDesign(Request $request)
    {
        # code...
        $data = [
            'review_number_color' => $request->review_number_color,
            'star_rating_color' => $request->star_rating_color,
            'top_background_color' => $request->top_background_color
        ];
        if ($request->file('background_image_src')) {
            # code...
            $background_image_src = $request->file('background_image_src')->store('background_image_src','public');
            Log::info("$background_image_src");
            $data['background_image_src'] =  URL::asset('storage/app/public/').'/'.$background_image_src;
        }
        if ($request->file('personal_avatar_src')) {
            # code...
            $personal_avatar_src = $request->file('personal_avatar_src')->store('personal_avatar_src','public');
            Log::info("$personal_avatar_src");
            $data['personal_avatar_src'] =  URL::asset('storage/app/public/').'/'.$personal_avatar_src;
        }
        if ($request->file('logo_image_src')) {
            # code...
            $logo_image_src = $request->file('logo_image_src')->store('logo_image_src','public');
            Log::info("$logo_image_src");
            $data['logo_image_src'] =  URL::asset('storage/app/public/').'/'.$logo_image_src;
        }
        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $user_id = $userData['id'];
        $CrmSettings = CrmSettings::where('user_id', $user_id)
            ->update($data);
        $CrmSettings = CrmSettings::where('user_id', $user_id)->get();
        return response()->json($CrmSettings, 200);
    }

    public function emailSentUser(Request $request)
    {
        # code...

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $user_id = $userData['id'];
        $CrmSettings = CrmSettings::where('user_id', $user_id)
            ->update([
                'email_heading' => $request->email_heading,
                'email_message' => $request->email_message,
                'email_subject' => $request->email_subject,
                'positive_answer' => $request->positive_answer,
                'negative_answer' => $request->negative_answer
            ]);
        $CrmSettings = CrmSettings::where('user_id', $user_id)->get();
        return response()->json($CrmSettings, 200);
    }

    public function emailNegativeAnswerSetup(Request $request)
    {

        $data = [
            'email_negative_answer_setup_heading' => $request->email_negative_answer_setup_heading,
            'email_negative_answer_setup_message' => $request->email_negative_answer_setup_message
        ];
        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $user_id = $userData['id'];
        $CrmSettings = CrmSettings::where('user_id', $user_id)
            ->update($data);
        $CrmSettings = CrmSettings::where('user_id', $user_id)->get();
        return response()->json($CrmSettings, 200);
    }

    public function personalizeTouch(Request $request)
    {
        # code...
        $data = [
            'company_role' => $request->company_role,
            'full_name' => $request->full_name
        ];
        if ($request->file('personal_avatar_src')) {
            # code...
            $personal_avatar_src = $request->file('personal_avatar_src')->store('personal_avatar_src','public');
            $data['personal_avatar_src'] = $personal_avatar_src;
        }

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $user_id = $userData['id'];
        $CrmSettings = CrmSettings::where('user_id', $user_id)
            ->update($data);
        $CrmSettings = CrmSettings::where('user_id', $user_id)->get();
        return response()->json($CrmSettings, 200);
    }

    public function smsView(Request $request)
    {
        // $this->data['showAdditionalBar'] = true;
        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $user_id = $userData['id'];

        $this->data['CrmSettings'] = CrmSettings::where('user_id', $user_id)->first();

        $data = $this->data;
        return response()->json($data, 200);
    }

    public function smsImage(Request $request)
    {
        # code...
        $data = [];
        if ($request->file('sms_image')) {
            # code...
            $sms_image = $request->file('sms_image')->store('sms_image','public');
            $data['sms_image'] = URL::asset('storage/app/public/').'/'.$sms_image;
        }
        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $user_id = $userData['id'];
        $CrmSettings = CrmSettings::where('user_id', $user_id)
            ->update($data);
        $CrmSettings = CrmSettings::where('user_id', $user_id)->get();
        return response()->json($CrmSettings, 200);
    }

    public function smsMessage(Request $request)
    {
        # code...
        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $user_id = $userData['id'];
        $CrmSettings = CrmSettings::where('user_id', $user_id)
            ->update([
                'sms_message' => $request->sms_message
            ]);
        $CrmSettings = CrmSettings::where('user_id', $user_id)->get();
        return response()->json($CrmSettings, 200);
    }

    public function emailSettings(Request $request)
    {
        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $user_id = $userData['id'];

        $this->data['CrmSettings'] = CrmSettings::where('user_id', $user_id)->first();
        $this->data['user_data'] = $userData;

        $this->data['showAdditionalBar'] = true;

        $data = $this->data;
        return response()->json($data, 200);
    }

    public function CustomerFormSettings(Request $request){

        $rec = $this->crmEntity->addCustomForm($request);
        return response()->json($rec, 200);
    }

    public function getCustomerFormSettings(Request $request)
    {
        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();

        $form = CustomerFormSettings::where(['user_id' => $userData['id'], 'type' => 'form'])->first();
        $button = CustomerFormSettings::where(['user_id' => $userData['id'], 'type' => 'button'])->first();
        $head = CustomerFormSettings::where(['user_id' => $userData['id'], 'type' => 'head'])->first();
        $font = CustomerFormSettings::where(['user_id' => $userData['id'], 'type' => 'font'])->first();

        $data['form'] = $form;
        $data['button'] = $button;
        $data['head'] = $head;
        $data['font'] = $font;

        return response()->json($data, 200);
    }

    public function deleteField(Request $request){
        $rec = $this->crmEntity->deleteFormField($request);
        return response()->json($rec, 200);
    }

    public function deleteCustomField(Request $request){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $rec = NewFields::where(['user_id' => $userData['id'], 'id' => $request->get('field_id')])->delete();
        return response()->json($rec, 200);
    }

    public function getDeleteFields(Request $request){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();

        $rec = DeletedFields::where('user_id', $userData['id'])->get();

        return response()->json($rec, 200);
    }

    public function addField(Request $request){
        $rec = $this->crmEntity->addFormField($request);
        return response()->json($rec, 200);
    }

    public function getAddField(Request $request){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();

        $rec = NewFields::where('user_id', $userData['id'])->get();

        return response()->json($rec, 200);
    }

    public function iframeForm(Request $request){
        $id = $request->get('myid');

        $head = CustomerFormSettings::where(['user_id' => $id, 'type' => 'head'])->first();
        $form = CustomerFormSettings::where(['user_id' => $id, 'type' => 'form'])->first();
        $button = CustomerFormSettings::where(['user_id' => $id, 'type' => 'button'])->first();
        $font = CustomerFormSettings::where(['user_id' => $id, 'type' => 'font'])->first();
        $deletedLastname = DeletedFields::where(['user_id' => $id, 'field_name' => 'lastname'])->first();
        $deletedComment = DeletedFields::where(['user_id' => $id, 'field_name' => 'comment'])->first();
        $fields = NewFields::where('user_id', $id)->get();

        $this->data['form'] = $form;
        $this->data['button'] = $button;
        $this->data['head'] = $head;
        $this->data['font'] = $font;
        $this->data['lastname'] = $deletedLastname;
        $this->data['comment'] = $deletedComment;
        $this->data['fields'] = $fields;

        return view('iframes.myform', $this->data);
    }

    public function iframeTest(){
        return view('iframes.test');
    }

    public function AllCountries(){
        $countries = Countries::all();
        return response()->json($countries, 200);
    }

    public function customersListSearch(Request $request){

        $this->data['customerResult'] =  $this->crmEntity->customerSearch($request);

        return response()->json($this->data, 200);
    }

    public function getCustomersOverview(Request $request){

        $this->data['funnel'] =  $this->crmEntity->customerOverview($request);

        return response()->json($this->data, 200);
    }

    public function customerLeads(Request $request){

        $this->data['leads'] =  $this->crmEntity->customerLeads($request);

        $this->data['leadsGrowth'] =  $this->crmEntity->customerLeadsGrowth($request);

        return response()->json($this->data, 200);
    }

}
