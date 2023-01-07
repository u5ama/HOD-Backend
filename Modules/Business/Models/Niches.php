<?php

namespace Modules\Business\Models;

use Illuminate\Database\Eloquent\Model;

class Niches extends Model
{
    protected $table = 'industry_niches';

    protected $guarded = [];

    public function industry()
    {
        return $this->belongsTo(Industry::class,'industry_id','id');
    }
}
