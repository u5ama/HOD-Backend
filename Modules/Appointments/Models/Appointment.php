<?php

namespace Modules\Appointments\Models;

use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    protected $table = 'appointments';

    protected $primaryKey = 'id';

    protected $fillable = ['user_id', 'appointment_name', 'appointment_description', 'appointment_date', 'appointment_time', 'appointment_location', 'appointment_service', 'appointment_service_provider'];

    public function userInfo()
    {
        return $this->hasOne(AppointmentUserInformation::class, 'appointment_id', 'id');
    }
}
