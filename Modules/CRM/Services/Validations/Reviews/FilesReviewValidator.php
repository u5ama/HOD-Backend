<?php

namespace Modules\CRM\Services\Validations\Reviews;

use App\Services\Validations\LaravelValidator;

class FilesReviewValidator extends LaravelValidator
{
    protected $rules = [
        'file' => 'required|mimes:csv,txt',
        'smart_routing' => 'required',

    ];
}