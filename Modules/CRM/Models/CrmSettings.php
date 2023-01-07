<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Auth\Models\User;


class CrmSettings extends Model
{
    protected $table = 'crm_settings';

    protected $primaryKey = 'id';

    protected $fillable = [
        'enable_get_reviews',
        'smart_routing',
        'sending_option',
        'customize_email',
        'customize_sms',
        'review_site',
        'reminder',
        'user_id',
        'logo_image_src',
        'background_image_src',
        'top_background_color',
        'review_number_color',
        'star_rating_color',
        'email_subject',
        'email_heading',
        'email_message',
        'positive_answer',
        'negative_answer',
        'email_negative_answer_setup_heading',
        'email_negative_answer_setup_message'
    ];

}
