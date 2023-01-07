<?php

namespace Modules\ThirdParty\Entities;


use Facebook\PersistentData\PersistentDataInterface;
use Illuminate\Support\Facades\Session;

class CustomPersistentDataHandler implements PersistentDataInterface
{

    /**
     * @var string Prefix to use for session variables.
     */
    protected $sessionPrefix = 'FBRLH_';

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        return Session::get($this->sessionPrefix . $key);
    }

    /**
     * @inheritdoc
     */
    public function set($key, $value)
    {
        Session::put($this->sessionPrefix . $key, $value);
    }
}
