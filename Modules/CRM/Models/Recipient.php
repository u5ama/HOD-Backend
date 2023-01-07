<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Auth\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Business\Models\EmailTemplate;
use Modules\CRM\Models\CrmSettings;
use Modules\CRM\Models\SendingHistory;
use App\Traits\Encryptable;

class Recipient extends Model
{
    use Encryptable;
    use SoftDeletes;

    protected $table = 'recipients';

    protected $primaryKey = 'id';
    protected $dates = ['deleted_at'];

    protected $encryptable = [];

    protected $fillable = [
        'email', 'first_name', 'last_name', 'smart_routing', 'user_id', 'verification_code', 'enquiries', 'enquiry_source', 'revenue', 'comments', 'phone_number', 'country', 'country_code', 'birthmonth', 'birthdate', 'created_at', 'updated_at'
    ];


    public function reviewRequest()
    {
        return $this->hasMany(ReviewRequest::class, 'recipient_id', 'id');
    }

    public function reviewRequestForNegativeFeedback()
    {
        return $this->hasOne(ReviewRequest::class, 'recipient_id', 'id')->where('message', '!=', null)->where('site', '=', null);
    }

    public function reviewRequestForPostitiveFeedback()
    {
        return $this->hasOne(ReviewRequest::class, 'recipient_id', 'id')->where('review_status', '!=', 'true')->where('message', '=', null);
    }

    public function negativeReview()
    {
        return $this->hasOne(ReviewRequest::class, 'recipient_id', 'id');
    }

    public function sendingHistory()
    {
        return $this->hasOne(SendingHistory::class, 'customer_id', 'id');
    }


    public function hasManyEmailTemplates()
    {
        return $this->belongsToMany(EmailTemplate::class, 'campaign_users_track',  'recipient_id', 'template_id');
    }
}
