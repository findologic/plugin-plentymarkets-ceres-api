<?php

class SdkRestApi
{
    public static $params = [];
    public static function getParam(string $param)
    {
        return isset(self::$params[$param]) ? self::$params[$param] : null;
    }
}
