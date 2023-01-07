<?php

namespace Modules\Appointments\Models;

use Illuminate\Database\Eloquent\Model;

class AppointmentFormSettings extends Model
{
    protected $table = 'appointment_form_settings';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id', 'type', 'width', 'height', 'fontSize', 'fontColor', 'backgroundColor', 'labelColor', 'labelFontSize', 'borderColor', 'btnWidth', 'btnHeight', 'headColor', 'headFontSize', 'allFontFamily', 'headingText'
    ];
}
