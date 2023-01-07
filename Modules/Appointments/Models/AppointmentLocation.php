<?php

namespace Modules\Appointments\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentLocation extends Model
{
    protected $table = 'appointment_location';

    protected $primaryKey = 'id';

    protected $fillable = ['user_id', 'locations_name', 'state', 'country'];
}
