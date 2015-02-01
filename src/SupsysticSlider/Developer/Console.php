<?php

class SupsysticSlider_Developer_Console
{
    public static function log($msg, $append = false)
    {
        if (!is_scalar($msg)) {
            if (is_array($msg)) {
                $msg = print_r($msg, true);
            } elseif (is_object($msg)) {
                $msg = get_class($msg);
            }
        }

        self::write($msg, $append);
    }

    public static function dump()
    {
        ob_start(); call_user_func_array('var_dump', func_get_args());

        self::write(ob_get_clean());
    }

    public static function write($data, $append = false)
    {
        $flags = $append ? FILE_APPEND : null;

        @file_put_contents('/home/artur/Logs/php_debug.log', $data . PHP_EOL, $flags);
    }

    public static function wipe()
    {
        @file_put_contents('/home/artur/Logs/php_debug.log', PHP_EOL);
    }
}
