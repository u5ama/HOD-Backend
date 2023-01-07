<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\Auth\Models\User;


class RecipientListXref extends Model
{
    protected $table = 'recipient_list_xref';

    protected $primaryKey = 'id';

    protected $fillable = [
        'list_id', 'customer_id'
    ];

}
