<?php

namespace SB\API;

use SB\API;
use \Exception;

class Cache
{

    public static $root = false;

    public static function init()
    {

        self::$root = str_replace('//', '/', WP_CONTENT_DIR.'/'.API::$cache);

        if (!file_exists(self::$root)) {
            mkdir(self::$root, 0744);
        }

        if (!is_writable(self::$root)) {
            throw new Exception('Can\'t write cache.');
        }

    }

    public static function file($endpoint, $vars)
    {

        $file = self::$root.'/'.$endpoint->id.'.json';
        return apply_filters('SB_API_cache_file_'.$endpoint->id, $file, $endpoint, $vars);

    }

    public static function read($endpoint, $vars = false, $create_missing = false)
    {

        $file = self::file($endpoint, $vars);

        if (file_exists($file) && !empty($endpoint->max_age)) {
            $age = filemtime($file);
            if (time() - $age > $endpoint->max_age) {
                debug('CACHE :: remove old '.$file);
                unlink($file);
            }
        }

        if (!file_exists($file)) {
            if (!$create_missing) {
                return false;
            } else {
                $data = self::purge($endpoint->id, $vars, true);
                debug('CACHE :: create missing '.$file);
            }
        }

        if (empty($data) && file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
        }

        if (empty($data)) {
            return false;
        }

        $data['age'] = time() - filemtime($file);

        debug('CACHE :: read '.$file);

        return $data;

    }

    public static function write($data, $endpoint, $vars = false)
    {

        if (empty($data)) {
            return false;
        }

        $file = self::file($endpoint, $vars);

        $check = file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT));

        if (is_numeric($check)) {
            return $data;
        }

        return false;

    }

    public static function purge($uri, $args = array(), $write = true)
    {

        $endpoint = API::getEndpoint($uri);

        $file = self::file($endpoint, $args);

        $process = API::getProcess($endpoint);

        debug('CACHE :: purge '.$file);

        if (!$write) {
            if (file_exists($file)) {
                debug('CACHE :: unlink '.$file);
                unlink($file);
            }
            return;
        }

        // NOTE cache is only used for static data or generated for specific purposes
        // of course it won't go through the usual headers and vars and you need to
        // make sure the data generation works anyway.
        // Second argument can be used to send in specific data or values that can be
        // used instead.
        if (!empty($process)) {
            switch ($process) {
                case 'callback':
                    if (is_callable($endpoint->callback)) {
                        $data = call_user_func_array(
                            $endpoint->callback,
                            array($endpoint, false, $args)
                        );
                    } elseif (is_array($endpoint->callback) && is_callable('SB\\'.implode('::', $endpoint->callback))) {
                        $data = call_user_func_array(
                            'SB\\'.implode('::', $endpoint->callback),
                            array($endpoint, false, $args)
                        );

                    } else {
                        throw new Exception('Callback not found.');
                    }
                    break;

                case 'template':
                    throw new Exception('Not yet implemented.');
                    $data = false;
                    break;

                case 'query':
                    throw new Exception('Not yet implemented.');
                    break;

                default:
                    throw new Exception('Must have a known process, "callback", "template" or "query".');
                    break;
            }
        }

        if (empty($data)) {
            return false;
        }

        debug('CACHE :: write '.$file);
        return self::write($data, $endpoint, $args);

    }

    public static function purgeAll($write = false)
    {

        foreach (API::getEndpoints() as $endpoint) {
            if ($endpoint->cached && $endpoint->method == 'GET') {
                self::purge($endpoint->id, array(), $write);
                // for custom file names do specific purge
                do_action('SB_API_purge_all_'.$endpoint->id);
            }
        }

    }
}
