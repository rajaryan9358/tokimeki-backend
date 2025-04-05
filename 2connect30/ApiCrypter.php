<?php

class Security
{

    public static function encrypt($input, $key)
    {
        return openssl_encrypt($input,"AES-256-ECB",$key);
    }

    public static function decrypt($sStr, $sKey)
    {
       return openssl_decrypt($sStr,"AES-256-ECB",$sKey);
    }
}

?>