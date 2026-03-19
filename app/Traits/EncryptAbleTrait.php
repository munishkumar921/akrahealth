<?php

namespace App\Traits;

use Illuminate\Support\Facades\Crypt;

trait EncryptAbleTrait
{
    /**
     * getAttribute
     *
     * @param  mixed  $key
     * @return void
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if ($this->shouldDecrypt($key, $value)) {
            try {
                return Crypt::decryptString($value);
            } catch (\Exception $e) {
                return $value;
            }
        }

        return $value;
    }

    /**
     * setAttribute
     *
     * @param  mixed  $key
     * @param  mixed  $value
     * @return void
     */
    public function setAttribute($key, $value)
    {
        if ($this->shouldEncrypt($key, $value)) {
            try {
                $value = Crypt::encryptString($value);
            } catch (\Exception $e) {
                // handle or log encryption failure
            }
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * shouldEncrypt
     *
     * @param  mixed  $key
     * @param  mixed  $value
     */
    protected function shouldEncrypt($key, $value): bool
    {
        return isset($this->encryptAble) &&
            in_array($key, $this->encryptAble) &&
            ! is_null($value) &&
            ! is_object($value);
    }

    /**
     * shouldDecrypt
     *
     * @param  mixed  $key
     * @param  mixed  $value
     */
    protected function shouldDecrypt($key, $value): bool
    {
        return isset($this->encryptAble) &&
            in_array($key, $this->encryptAble) &&
            ! is_null($value) &&
            is_string($value);
    }
}
