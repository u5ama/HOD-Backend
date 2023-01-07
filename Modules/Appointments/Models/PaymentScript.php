<?php

namespace Modules\Appointments\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentScript extends Model
{
    protected $table = 'appointment-payment-script';

    protected $primaryKey = 'id';

    protected $fillable = ['user_id', 'service_id', 'payment_script'];
}
