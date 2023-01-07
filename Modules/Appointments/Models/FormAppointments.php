<?php

namespace Modules\Appointments\Models;

use Illuminate\Database\Eloquent\Model;

class FormAppointments extends Model
{
    protected $table = 'appointment_form_appointments';

    protected $primaryKey = 'id';

    protected $fillable = ['user_id', 'service_id', 'available_date', 'available_time'];

    public function services()
    {
        return $this->hasMany(AppointmentService::class, 'id', 'service_id');
    }
}
