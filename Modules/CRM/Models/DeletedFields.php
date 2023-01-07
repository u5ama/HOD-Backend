<?php

namespace Modules\CRM\Models;

use Illuminate\Database\Eloquent\Model;

class DeletedFields extends Model
{
    protected $table = 'customerform_deleted_field';
    protected $fillable = [
        'user_id', 'field_id', 'field_name'];
}
