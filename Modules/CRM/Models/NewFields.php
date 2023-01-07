<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;

class NewFields extends Model
{
    protected $table = 'customerform_new_fields';
    protected $fillable = [
        'user_id', 'field_type', 'field_name', 'field_placeholder', 'label'];
}
