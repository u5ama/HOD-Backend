<?php

namespace Modules\Appointments\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Validator;
use Modules\Appointments\Entities\AppointmentSettingsEntity;
use Modules\Appointments\Models\AppointmentCategory;
use Modules\Appointments\Models\AppointmentLocation;
use Modules\Appointments\Models\AppointmentService;
use Modules\Appointments\Models\AppointmentServiceProvider;
use Modules\Appointments\Models\AppointmentUserInformation;
use Modules\Appointments\Models\FormAppointments;
use Modules\Appointments\Models\PaymentScript;
use Tymon\JWTAuth\Facades\JWTAuth;

class AppointmentSettingsController extends Controller
{
    /**
     * @var AppointmentSettingsEntity
     */
    private $AppointmentSetting;

    protected  $data = [];
    public function __construct()
    {
        $this->AppointmentSetting = new AppointmentSettingsEntity();
    }

    public function appointmentPage(Request $request){
        $id = $request->get('user_id');

        $locations = AppointmentLocation::where('user_id', $id)->get();

        $this->data['locations'] = $locations;

        return view('appointments.appointment-form', $this->data);
    }

    public function getFormServices(Request $request){

        $location_id = $request->get('location_id');
        $services = AppointmentCategory::where('location_id', $location_id)->with('services')->get()->toArray();

        $this->data['services'] = $services;

        return json_encode($this->data);
    }

    public function getFormProviders(Request $request){

        $service_id = $request->get('service_id');
        $providers = AppointmentServiceProvider::where('service_id', $service_id)->get()->toArray();
        $appointments = FormAppointments::where('service_id', $service_id)->get();

        $this->data['providers'] = $providers;
        $this->data['appointments'] = $appointments;

        return json_encode($this->data);
    }

    public function getFormAppointments(Request $request){

        $id = $request->get('user_id');

        $selectedDate = $request->get('date');
        $selectedDate = date('yy-m-d',strtotime($selectedDate));

        $appointments = FormAppointments::where(['user_id' => $id, 'available_date' => $selectedDate])->get();

        $this->data['appointments'] = $appointments;

        return json_encode($this->data);
    }

    public function getFormAppointmentScript(Request $request){

        $id = $request->get('user_id');
        $service_id = $request->get('service_id');

        $script = PaymentScript::where(['user_id' => $id, 'service_id' => $service_id])->first();

        $this->data['script'] = $script;

        return json_encode($this->data);
    }



    public function AddAppointmentForm(Request $request)
    {

        if ($request->get('type') == 'location'){
            $this->AppointmentSetting->AppointmentLocation($request);
            return response()->json(['success' => 'true', 'message' => 'Appointment Location Added!'],200);
        }

        if ($request->get('type') == 'category'){
            $this->AppointmentSetting->AppointmentCategory($request);
            return response()->json(['success' => 'true', 'message' => 'Appointment Category Added!'],200);
        }

        if ($request->get('type') == 'service'){
            $service = $this->AppointmentSetting->AppointmentService($request);

            if ($service){
                $payment = $this->AppointmentSetting->AddPaymentScript($request, $service);

                $providers = $request->get('provider');
                if (!empty($providers)){
                    $this->AppointmentSetting->AppointmentServiceProvider($request, $service);
                }
            }
            return response()->json(['success' => 'true', 'message' => 'Appointment Service Added!'],200);
        }

        if ($request->get('type') == 'date'){

            $this->AppointmentSetting->AppointmentDate($request);
            return response()->json(['success' => 'true', 'message' => 'Appointment Date Added!'],200);
        }

        if ($request->get('type') == 'user'){

            $this->AppointmentSetting->AppointmentUserInfo($request);
            return response()->json(['success' => 'true', 'message' => 'Appointment Date Added!'],200);
        }

    }

    public function RemoveAppointment(Request $request){

        if ($request->get('type') == 'location'){
            $this->AppointmentSetting->RemoveLocation($request);
            return response()->json(['success' => 'true', 'message' => 'Appointment location Deleted!'],200);
        }

        if ($request->get('type') == 'category'){
            $this->AppointmentSetting->RemoveCategory($request);
            return response()->json(['success' => 'true', 'message' => 'Appointment category Deleted!'],200);
        }

        if ($request->get('type') == 'service'){
            $this->AppointmentSetting->RemoveService($request);
            return response()->json(['success' => 'true', 'message' => 'Appointment service Deleted!'],200);
        }

        if ($request->get('type') == 'date'){
            $this->AppointmentSetting->RemoveDate($request);
            return response()->json(['success' => 'true', 'message' => 'Appointment Date Deleted!'],200);
        }
    }

    public function GetAppointmentLocation(Request $request){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $user_id = $userData['id'];

        $locations = AppointmentLocation::where('user_id', $user_id)->get();

        return response()->json(['locations' => $locations],200);
    }

    public function GetAppointmentCategories(Request $request){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $user_id = $userData['id'];

        $categories = AppointmentCategory::where('user_id', $user_id)->with('locations')->get();

        return response()->json(['categories' => $categories],200);
    }

    public function GetAppointmentServices(Request $request){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $user_id = $userData['id'];

        $services = AppointmentService::where('user_id', $user_id)->with('category')->get();

        return response()->json(['services' => $services],200);
    }

    public function GetAppointmentDates(Request $request){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $user_id = $userData['id'];

        $dates = FormAppointments::where('user_id', $user_id)->with('services')->get();

        return response()->json(['dates' => $dates],200);
    }

    public function createUserInfo(Request $request){

        $validator = Validator::make($request->all(), [
            'first_name' => 'required',
            'last_name' => 'required',
            'email' => 'required',
            'phone_number' => 'required',
            'street_address' => 'required',
            'city' => 'required',
            'state' => 'required',
        ]);

        $user_id = $request->get('user_Id');
        $appointment_id = $request->get('appointment_id');

        if ($validator->passes()) {

            AppointmentUserInformation::create([
                'user_id' => $user_id,
                'appointment_id' => $appointment_id,
                'first_name' => $request->get('first_name'),
                'last_name' => $request->get('last_name'),
                'email' => $request->get('email'),
                'phone_number' => $request->get('phone_number'),
                'gender' => $request->get('gender'),
                'street_address' => $request->get('street_address'),
                'city' => $request->get('city'),
                'state' => $request->get('state'),
                'payment' => $request->get('payment'),

            ]);
            return response()->json(['success'=>'Added new records.']);
        }
        return response()->json(['error'=>$validator->errors()->all()]);
    }
}
