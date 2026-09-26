<?php

function encrypt($any)
{
    $encrypter = \Config\Services::encrypter();

    return bin2hex($encrypter->encrypt($any));
   
}

function decrypt($any)
{
    if (empty($any)) return $any;
    if (is_numeric($any)) return $any;

    if (is_string($any) && ctype_xdigit($any) && (strlen($any) % 2 === 0) && strlen($any) >= 32) {
        try {
            $encrypter = \Config\Services::encrypter();
            $bin = @hex2bin($any);
            if ($bin !== false) {
                return $encrypter->decrypt($bin);
            }
        } catch (\Throwable $e) {
            return $any;
        }
    }

    return $any;
}
