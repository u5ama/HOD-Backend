<?php

namespace App\Entities;

use App\Traits\GlobalErrorHandlingTrait;
use App\Traits\GlobalResponseTrait;

class AbstractEntity
{
    use GlobalErrorHandlingTrait,GlobalResponseTrait;
}