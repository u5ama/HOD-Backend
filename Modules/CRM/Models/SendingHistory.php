<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Auth\Models\User;


class SendingHistory extends Model
{
    protected $table = 'setting_history';

    protected $primaryKey = 'id';

    protected $fillable = [
        'customer_id', 'sms_count', 'email_count', 'sms_last_sent', 'email_last_sent'
    ];

}
