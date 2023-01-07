<?php

namespace Modules\ThirdParty\Models;

use Illuminate\Database\Eloquent\Model;

class TripadvisorReview extends Model
{
    protected $table = 'third_party_review';

    protected $primaryKey = 'review_id';

    protected $fillable = [
        'rating', 'reviewer', 'message','reviewer_image','review_url', 'third_party_id'
    ];

}
