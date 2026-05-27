<?php

if (!defined('ABSPATH')) {
    exit;
}

class MCWS_Utils
{
    public static function normalize_key(string $value): string
    {
        $value = strtoupper(trim($value));
        return remove_accents($value);
    }

    public static function mask_token(string $token): string
    {
        if (strlen($token) <= 8) {
            return str_repeat('*', strlen($token));
        }

        return substr($token, 0, 4) . str_repeat('*', strlen($token) - 8) . substr($token, -4);
    }
}
