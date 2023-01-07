<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerLeads extends Model
{
    protected $table = 'customer_leads';
    protected $fillable = [
        'user_id', 'type', 'activity_date', 'count'];
}
