<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;

class Lists extends Model
{
    protected $table = 'list';

    protected $primaryKey = 'id';

    protected $fillable = [
        'name'
    ];

}
