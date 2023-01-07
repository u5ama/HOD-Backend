<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;

class SmsDeliveryEvent extends Model
{
    protected $table = 'sms_delivery_event';

    protected $primaryKey = 'id';

    protected $fillable = [
        'promo_id', 'recipient_id', 'message_sid', 'event_type', 'event_details', 'created_at', 'updated_at'
    ];

}
