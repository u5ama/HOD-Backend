<?php

namespace Modules\Appointments\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentServiceProvider extends Model
{
    protected $table = 'appointment_service_providers';

    protected $primaryKey = 'id';

    protected $fillable = ['user_id', 'service_id', 'provider_name', 'provider_email', 'provider_contact'];
}
