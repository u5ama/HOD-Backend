<?php

namespace Modules\Appointments\Entities;

use App\Entities\AbstractEntity;
use App\Services\SessionService;
use App\Traits\UserAccess;
use App\User;
use DB;
use Illuminate\Http\Request;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use File;
use Illuminate\Support\Facades\URL;
use Log;
use Mail;
use Config;

use Modules\Appointments\Models\AppointmentCategory;
use Modules\Appointments\Models\AppointmentLocation;
use Modules\Appointments\Models\AppointmentService;
use Modules\Appointments\Models\AppointmentServiceProvider;
use Modules\Appointments\Models\FormAppointments;
use Modules\Appointments\Models\PaymentScript;
use Redirect;
use Tymon\JWTAuth\Facades\JWTAuth;

/**
 * Class AuthEntity
 * @package Modules\Auth\Entities
 */
class AppointmentSettingsEntity extends AbstractEntity
{
    use UserAccess;


    public function __construct()
    {
//        $this->thirdPartyEntity = new ThirdPartyEntity();
    }

    public function AppointmentLocation($request){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $user_id = $userData['id'];

        $location_name = $request->get('location_name');
        $location_state = $request->get('location_state');
        $location_country = $request->get('location_country');

        $location = AppointmentLocation::create([
            'user_id' => $user_id,
            'locations_name' => $location_name,
            'state' => $location_state,
            'country' => $location_country
        ]);

        if($location) {
            return $this->helpReturn("location added.", $location);
        }
    }

    public function AppointmentCategory($request){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $user_id = $userData['id'];

        $location_id = $request->get('location_id');
        $category_name = $request->get('category_name');

        $category = AppointmentCategory::create([
            'user_id' => $user_id,
            'location_id' => $location_id,
            'category_name' => $category_name,
        ]);

        if($category) {
            return $this->helpReturn("Category added.", $category);
        }
    }

    public function AppointmentService($request){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $user_id = $userData['id'];

        $category_id = $request->get('category_id');
        $service_name = $request->get('service_name');

        $service = AppointmentService::create([
            'user_id' => $user_id,
            'category_id' => $category_id,
            'service_name' => $service_name,
        ]);

        if($service) {
            return $this->helpReturn("Service added.", $service);
        }
    }

    public function AppointmentServiceProvider($request, $service){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $user_id = $userData['id'];
        $providers = $request->get('provider');
        foreach ($providers as $provider){
            $serviceProvider = AppointmentServiceProvider::create([
                'user_id' => $user_id,
                'service_id' => $service['records']['id'],
                'provider_name' => $provider['prov_name'],
                'provider_email' => $provider['prov_email'],
                'provider_contact' => $provider['prov_phone'],
            ]);
        }
        if($serviceProvider) {
            return $this->helpReturn("Service Provider added.", $serviceProvider);
        }
    }

    public function AddPaymentScript($request, $service){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $user_id = $userData['id'];

        $service_id = $service['records']['id'];

        $record = PaymentScript::where(['user_id' => $user_id, 'service_id' => $service_id])->first();
        if (!empty($record)){
            $script = PaymentScript::where(['user_id' => $user_id, 'service_id' => $service_id])->update([
                'payment_script' => $request->get('payment_script'),
                'service_id' => $service_id,
            ]);
        }else{
            $script = PaymentScript::create([
                'user_id' => $user_id,
                'service_id' => $service_id,
                'payment_script' => $request->get('payment_script')
            ]);
        }

        return $this->helpReturn("payment script added.", $script);
    }

    public function AppointmentDate($request){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $user_id = $userData['id'];

        $timeSlots = $request->get('appointment_time');
        foreach ($timeSlots as $time) {
            $appointmentDates = FormAppointments::create([
                'user_id' => $user_id,
                'service_id' => $request->get('service_id'),
                'available_date' => $request->get('appointment_date'),
                'available_time' => $time,
            ]);
        }
        if($appointmentDates) {
            return $this->helpReturn("Appointment Date added.", $appointmentDates);
        }
    }

    public function AppointmentUserInfo($request){

    }

    public function RemoveLocation($request){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $user_id = $userData['id'];

        $location_id = $request->get('location_id');

        AppointmentLocation::where(['user_id' => $user_id, 'id' => $location_id])->delete();

        return $this->helpReturn("location Removed.");
    }

    public function RemoveCategory($request){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $user_id = $userData['id'];

        $category_id = $request->get('category_id');

        AppointmentCategory::where(['user_id' => $user_id, 'id' => $category_id])->delete();

        return $this->helpReturn("category Removed.");
    }

    public function RemoveService($request){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $user_id = $userData['id'];

        $service_id = $request->get('service_id');

        AppointmentService::where(['user_id' => $user_id, 'id' => $service_id])->delete();

        return $this->helpReturn("service Removed.");
    }

    public function RemoveDate($request){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $user_id = $userData['id'];

        $app_date = $request->get('appointment_id');

        FormAppointments::where(['user_id' => $user_id, 'id' => $app_date])->delete();

        return $this->helpReturn("service Removed.");

    }
}
