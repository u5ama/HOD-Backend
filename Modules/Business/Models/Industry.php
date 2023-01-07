<?php

namespace Modules\Business\Models;

use Illuminate\Database\Eloquent\Model;

class Industry extends Model
{
    protected $table = 'industry';

    protected $guarded = [];

    public function niches()
    {
        return $this->hasMany(Niches::class,'industry_id', 'id');
    }
}
