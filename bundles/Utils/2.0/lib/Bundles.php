<?php

namespace SB\Utils;

use SB\Utils;
use \Exception;

class Bundles
{

    public static function load($type, $object, $version, $function = false, $parent = true)
    {

        $path = array(
            'lib' => 'lib',
            'post_type' => 'lib/Posttypes',
            'bundle' => 'bundles'.'/'.$object.'/'.$version
            );

        if (!isset($path[$type])) {
            Utils::debug('ERROR unknown load type '.$type);
            return;
        }

        if ($type == 'bundle' && !$version) {
            Utils::debug('ERROR no bundle version specified for '.$object);
            return;
        }

        $base_path = ($parent) ? TEMPLATEPATH : STYLESHEETPATH;

        if (file_exists($base_path.'/'.$path[$type].'/'.$object.'.php')) {
            include_once($base_path.'/'.$path[$type].'/'.$object.'.php');

            if ($type == 'bundle') {
                self::addToLoaded($object, $version);
            }

            if (class_exists('SB\\'.$object, false)) {
                $classVars = get_class_vars('SB\\'.$object);
                if (!empty($classVars['dependencies'])) {
                    foreach ($classVars['dependencies'] as $dep_object => $dep_version) {
                        if (array_key_exists($dep_object, \SB\Utils::$loaded_bundles)) {
                            if (floatval($dep_version) != floatval(\SB\Utils::$loaded_bundles[$dep_object])) {
                                throw new Exception('Dependency not met. Version mismatch. '.$dep_object.', '.$dep_version);
                            }
                        } else {
                            throw new Exception('Dependency not met. Bundle not found. '.$dep_object.', '.$dep_version);
                        }
                    }
                }
            }

            if (!empty($function)) {
                if (is_callable('SB\\'.$object.'::'.$function)) {
                    call_user_func('SB\\'.$object.'::'.$function);
                } else {
                    Utils::debug('ERROR calling SB\\'.$object.'::'.$function);
                }
            }

        } else {
            Utils::debug('ERROR loading '.$object);
        }

    }

    public static function addToLoaded($bundle, $version)
    {

        Utils::$loaded_bundles[$bundle] = $version;

    }

    public static function getBundlePath($bundle, $parent = true)
    {

        if (empty($bundle)) {
            return false;
        }

        $version = (!empty(Utils::$loaded_bundles[$bundle])) ? Utils::$loaded_bundles[$bundle] : false;

        $base_path = ($parent) ? TEMPLATEPATH : STYLESHEETPATH;

        return $base_path.'/bundles/'.$bundle.'/'.$version;

    }

    public static function getBundleUri($bundle)
    {

        if (empty($bundle)) {
            return false;
        }

        $version = (!empty(Utils::$loaded_bundles[$bundle])) ? Utils::$loaded_bundles[$bundle] : false;

        return get_template_directory_uri().'/bundles/'.$bundle.'/'.$version;

    }
}
