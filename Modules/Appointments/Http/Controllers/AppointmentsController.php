<?php

namespace Modules\Appointments\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use Modules\Appointments\Models\Appointment;
use Modules\Appointments\Models\AppointmentFormSettings;
use Tymon\JWTAuth\Facades\JWTAuth;

class AppointmentsController extends Controller
{
    public function displayForm(Request $request){
        $id = $request->get('user_id');

        $head = AppointmentFormSettings::where(['user_id' => $id, 'type' => 'head'])->first();
        $form = AppointmentFormSettings::where(['user_id' => $id, 'type' => 'form'])->first();
        $button = AppointmentFormSettings::where(['user_id' => $id, 'type' => 'button'])->first();
        $font = AppointmentFormSettings::where(['user_id' => $id, 'type' => 'font'])->first();

        $this->data['form'] = $form;
        $this->data['button'] = $button;
        $this->data['head'] = $head;
        $this->data['font'] = $font;

        return view('appointments.scheduleForm', $this->data);
    }

    public function createAppointment(Request $request){

        $selectedDate = $request->appointment_date;
        $selectedDate = date('yy-m-d',strtotime($selectedDate));
        $appointment = Appointment::create([
            'user_id' => $request->user_id,
            'appointment_name' => $request->appointment_name,
            'appointment_description' => $request->appointment_description,
            'appointment_date' => $selectedDate,
            'appointment_time' => $request->appointment_time
        ]);
       // return response()->json(compact('appointment'));
        return response()->json(['success' => 'true', 'message' => 'Appointment added Successfully!', 'appointment' => $appointment],200);
    }

    public function createAppointmentInfo(Request $request){

        $validator = Validator::make($request->all(), [
            'appointment_location' => 'required',
            'appointment_service' => 'required',
            'appointment_provider' => 'required',
        ]);

        $user_id = $request->get('user_id');
        if ($validator->passes()) {
                Appointment::where('user_id', $user_id)->update([

                    'appointment_location' => $request->appointment_location,
                    'appointment_service' => $request->appointment_service,
                    'appointment_service_provider' => $request->appointment_provider,

                ]);
            return response()->json(['success'=>'Added new records.']);
        }
        return response()->json(['error'=>$validator->errors()->all()]);
    }

    public function getAppointments(Request $request){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $user_id = $userData['id'];

        $appointments = Appointment::where('user_id', $user_id)->with('userInfo')->get()->toArray();

        if (!empty($appointments)){
            return response()->json(compact('appointments'));
        }
    }

    public function getAppointmentDetail(Request $request){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();
        $user_id = $userData['id'];

        $appointmentId = $request->get('appointment_id');
        $appointments = Appointment::where(['user_id' => $user_id,'id'=> $appointmentId])->with('userInfo')->first();

        if (!empty($appointments)){
            return response()->json(compact('appointments'));
        }
    }
}
