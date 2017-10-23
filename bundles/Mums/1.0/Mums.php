<?php

namespace SB;

class Mums
{
    public static $dependencies = array(
        'Utils' => '2.0'
        );

    public static $version = 1;

    public static function init()
    {

        require_once('lib/CookieDisclaimer.php');

    }
}
