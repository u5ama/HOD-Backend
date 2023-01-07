<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerFormSettings extends Model
{
    protected $table = 'customer_form_settings';
    protected $fillable = [
        'user_id', 'type', 'width', 'height', 'fontSize', 'fontColor', 'backgroundColor', 'labelColor', 'labelFontSize', 'borderColor', 'btnWidth', 'btnHeight', 'headColor', 'headFontSize', 'allFontFamily', 'headingText'
    ];
}
