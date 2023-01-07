<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Auth\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\CRM\Models\CrmSettings;
use Modules\CRM\Models\SendingHistory;
use App\Traits\Encryptable;

class UserReviewsFiles extends Model
{
    protected $table = 'user_reviews_files';

    protected $fillable = [
        'user_id', 'business_id', 'file_name'
    ];

}
