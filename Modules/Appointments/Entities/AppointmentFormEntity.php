<?php

namespace Modules\Appointments\Entities;


use App\Entities\AbstractEntity;
use App\Traits\UserAccess;
use Modules\Appointments\Models\AppointmentFormSettings;
use Tymon\JWTAuth\Facades\JWTAuth;

class AppointmentFormEntity extends AbstractEntity
{
    use UserAccess;

    public function addCustomForm($request){
        JWTAuth::setToken($request->input('token'));
        $userData = JWTAuth::toUser();

        $user_id = AppointmentFormSettings::select('user_id')->where(['user_id' => $userData['id'], 'type' => $request->type])->first();
        if ($user_id == null){
            if ($request->type == 'form'){
                $rec = AppointmentFormSettings::create([
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
                $rec = AppointmentFormSettings::create([
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
                $rec = AppointmentFormSettings::create([
                    'user_id' => $userData['id'],
                    'type' => $request->type,
                    'headColor' => $request->headColor,
                    'headFontSize' => $request->headFontSize,
                    'headingText' => $request->headText,
                ]);
            }else if ($request->type == 'font'){
                $rec = AppointmentFormSettings::create([
                    'user_id' => $userData['id'],
                    'type' => $request->type,
                    'allFontFamily' => $request->formFontFamily,
                ]);
            }
        }else{
            if ($request->type == 'form'){
                $rec = AppointmentFormSettings::where(['user_id' => $userData['id'], 'type' => $request->type])
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
                $rec = AppointmentFormSettings::where(['user_id' => $userData['id'], 'type' => $request->type])
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
                $rec = AppointmentFormSettings::where(['user_id' => $userData['id'], 'type' => $request->type])
                    ->update([
                        'type' => $request->type,
                        'headColor' => $request->headColor,
                        'headFontSize' => $request->headFontSize,
                        'headingText' => $request->headText,
                    ]);
            }else if ($request->type == 'font'){
                $rec = AppointmentFormSettings::where(['user_id' => $userData['id'], 'type' => $request->type])
                    ->update([
                        'type' => $request->type,
                        'allFontFamily' => $request->formFontFamily,
                    ]);
            }
        }
        return $this->helpReturn("Custom Form.");
    }
}
