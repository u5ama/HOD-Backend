<?php

namespace Modules\Appointments\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentUserInformation extends Model
{
    protected $table = 'appointment_user_information';

    protected $primaryKey = 'id';

    protected $fillable = ['user_id', 'appointment_id', 'first_name', 'last_name', 'email', 'phone_number', 'gender', 'street_address', 'city', 'state', 'payment'];
}
