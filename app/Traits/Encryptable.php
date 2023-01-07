<?php
namespace App\Traits;
use Illuminate\Support\Facades\Crypt;
use Log;

trait Encryptable {
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if (in_array($key, $this->encryptable)) {

            try {
                if (empty($value)) {
                    $value = '';
                } else if (!empty($value) && strlen($value) > 40) {
                    $value = \Crypt::decrypt($value);
                } else {
                    $value = $value;
                }
                return $value;
            } catch (DecryptException $e) {

                Log::info('Value not decryptable');
            }
        }

        return $value;
    }

    public function setAttribute($key, $value)
    {
        if (in_array($key, $this->encryptable)) {
            try {
                if(empty($value)){
                    $value = '';
                }
                else if (!empty($value) && strlen($value) < 40) {
                    $value = \Crypt::encrypt($value);
                }else{
                    $value = $value;
                }
            } catch (DecryptException $e) {
                Log::info('Value not increped');
                Log::info($value);
            }
        }
        return parent::setAttribute($key, $value);
    }
}
