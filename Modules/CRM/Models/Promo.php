<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Auth\Models\User;
use Carbon\Carbon;
use Log;

class Promo extends Model
{
    protected $table = 'promo';

    protected $primaryKey = 'id';

    protected $fillable = [
        'user_id', 'customer_id', 'name', 'type', 'subject', 'message', 'schedule_type', 'status', 'schedule_date', 'sent_date', 'created_at', 'updated_at'
    ];

    public function getStatusAttribute($value)
    {

        if (!empty($value)) {
            if ($value == 1) {
                $value = 'Draft';
            } else if ($value == 2 && !empty($this->schedule_date)) {
                $date = Carbon::createFromFormat('Y-m-d H:i:s', $this->schedule_date, 'America/New_York')->format('m/d/Y, g:i A T');
                $value = 'Scheduled - ' . $date;
            } else if ($value == 3) {
                $value = 'Sending in Progress';
            } else if ($value == 4 && !empty($this->sent_date)) {
                $date = Carbon::createFromFormat('Y-m-d H:i:s', $this->sent_date, 'America/New_York')->format('m/d/Y, g:i A T');
                $value = 'Sent - ' . $date;
            } else if ($value == 5) {
                $value = 'Failed';
            } else if ($value == 6) {
                $value = 'Queue';
            }
        }
        return $value;

    }

    public function getTypeAttribute($value)
    {
        if (!empty($value)) {
            if ($value == 1) {
                $value = 'sms';
            } else if ($value == 2) {

                $value = 'email';
            }
        }
        return $value;
    }

    public function getCustomerIdAttribute($value)
    {

        if ($value == '') {
            $value = 'All Customers';
        } else {
            $value = $value;
        }

        return $value;
    }

}
