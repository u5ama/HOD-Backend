<?php

namespace Modules\Appointments\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentCategory extends Model
{
    protected $table = 'appointment_category';

    protected $primaryKey = 'id';

    protected $fillable = ['user_id', 'location_id', 'category_name'];

    public function locations()
    {
        return $this->hasMany(AppointmentLocation::class, 'id', 'location_id');
    }

    public function services()
    {
        return $this->hasMany(AppointmentService::class, 'category_id', 'id');
    }
}
