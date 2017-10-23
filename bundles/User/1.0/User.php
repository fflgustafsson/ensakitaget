<?php

namespace SB;

use SB\Utils;
use SB\Forms\Fields;

class User
{

    public static $meta_data = array();

    public static $dependencies = array(
        'Utils' => '2.0',
        'Forms' => '2.0',
        'Media' => '2.0'
        );

    public static function init()
    {

        require_once('lib/MetaData.php');

    }
}
