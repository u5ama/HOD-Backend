<?php

namespace Modules\Appointments\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentService extends Model
{
    protected $table = 'appointment_services';

    protected $primaryKey = 'id';

    protected $fillable = ['user_id', 'category_id', 'service_name'];

    public function category()
    {
        return $this->hasMany(AppointmentCategory::class, 'id', 'category_id');
    }
}
