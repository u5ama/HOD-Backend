<?php

namespace Modules\Appointments\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Modules\Appointments\Entities\AppointmentFormEntity;
use Modules\Appointments\Models\AppointmentFormSettings;
use Tymon\JWTAuth\Facades\JWTAuth;

class AppointFormController extends Controller
{
    protected $appFormEntity;

    public function __construct()
    {
        $this->appFormEntity = new AppointmentFormEntity();
    }

    public function AppointmentFormSettings(Request $request){
        $rec = $this->appFormEntity->addCustomForm($request);
        return response()->json($rec, 200);
    }

    public function getAppointmentFormSettings(Request $request){

        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();

        $form = AppointmentFormSettings::where(['user_id' => $userData['id'], 'type' => 'form'])->first();
        $button = AppointmentFormSettings::where(['user_id' => $userData['id'], 'type' => 'button'])->first();
        $head = AppointmentFormSettings::where(['user_id' => $userData['id'], 'type' => 'head'])->first();
        $font = AppointmentFormSettings::where(['user_id' => $userData['id'], 'type' => 'font'])->first();

        $data['form'] = $form;
        $data['button'] = $button;
        $data['head'] = $head;
        $data['font'] = $font;

        return response()->json($data, 200);
    }
}
