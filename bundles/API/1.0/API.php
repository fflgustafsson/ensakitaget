<?php

namespace SB;

use SB\Utils;
use SB\Rewrite;
use SB\API\Cache;

use \stdClass;
use \Exception;

class API
{

    public static $dependencies = array(
        'Utils' => '2.0',
        'Rewrite' => '2.0'
        );

    protected static $endpoints = array();
    public static $validationData = array();
    public static $headers = null;
    public static $response = null;
    public static $salt = null;
    public static $cache = 'cache';

    public static $defaultEndpoint = array(
        'id'        => null, // unique identifier
        'public'    => true, // false checks for logged in admin user
        'uri'       => false,
        'method'    => 'GET',
        'cached'    => true,
        'max_age'   => false, // false never regenerates, number of seconds 600, // 60 * 10 = 10 minutes
        'post_type' => false, // adds save hook to specific post_type for regeneration
        'query'     => false, // WP_Query args
        'callback'  => false, // if callback exists query is bypassed and callback is responsible for return
        'headers'   => array(),
        'type'      => 'array', // data == regular post variables, json
        'data'      => array(),
        'validate'  => false,
        'errors'    => true // true shows all errors, {num} shows last number of errors
        );

    protected static $error_codes = array(
        'BAD_AUTH' => array(100 => 'Bad Authentication data.'),
        'WRONG_METHOD' => array(101 => 'Wrong request method.'),
        'MISSING_DATA' => array(102 => 'Missing required request data.'),
        'VALIDATION_FAILED' => array(103 => 'Validation failed.'),
        'AUTHENTICATION_FAILED' => array(104 => 'Authentication failed.'),
        'DATABASE_ERROR' => array(105 => 'Database error.'),
        'EMPTY_RESPONSE' => array(106 => 'Empty response.')
        );

    public static function init()
    {

        require_once('lib/Cache.php');

        Cache::init();

    }

    public static function addEndpoint($args)
    {

        // if (Utils::isAjaxRequest()) {
        //     return false;
        // }

        $endpoint = wp_parse_args($args, self::$defaultEndpoint);

        $endpoint = self::addRewrite($endpoint);

        // Add the rewrite init, just once
        if (!is_array(self::$response)) {
            add_action('init', array('SB\Rewrite', 'init'), 10);
            self::$response = array();
        }

        if (empty($endpoint->id)) {
            $endpoint = self::setId($endpoint);
        }

        self::$endpoints[] = $endpoint;

    }

    public static function getEndpoint($uri = false)
    {

        if (!$uri) {
            $uri = self::createUri(Utils::serverString('REQUEST_URI'));
        } else {
            $uri = self::createUri($uri);
        }

        foreach (self::$endpoints as $endpoint) {
            if ($uri == $endpoint->uri) {
                return $endpoint;
            }
        }

        return false;

    }

    public static function getEndpoints()
    {

        return self::$endpoints;

    }

    public static function setId($endpoint)
    {

        $endpoint->id = sanitize_title($endpoint->uri);

        return $endpoint;

    }

    public static function addRewrite($args)
    {

        global $wp_rewrite;

        if (empty($args['uri'])) {
            return false;
        }

        $args['uri'] = self::createUri($args['uri']);

        // Check for child theme
        $base = get_template_directory();
        if (get_template_directory() != get_stylesheet_directory()) {
            $base = get_stylesheet_directory();
        }

        $self = str_replace($base.'/', '', Utils::getBundlePath('API'));

        Rewrite::addRule(array(
            'uri' => $args['uri'],
            'template' => $self.'/lib/Request',
            'after' => 'top'
        ));

        $object = new stdClass();
        foreach ($args as $key => $value) {
            $object->$key = $value;
        }

        return $object;

    }

    public static function createUri($uri)
    {

        $uri = preg_replace('/[.*^?].*/', '', $uri);

        $uri_array = array_filter(explode('/', $uri));
        $uri = implode('/', $uri_array).'/?';
        return $uri;

    }

    public static function requestData($endpoint, $request, $type = 'array')
    {

        $returnData = new stdClass();
        $types = array('bool', 'int', 'float', 'str', 'json');

        if (!is_array($request) && $type == 'json') {
            $request = json_decode($request, true);
        }

        foreach ($endpoint as $string => $args) {
            $require = false;
            $type = false;

            // Fix for mixed arrays
            $string = (is_numeric($string)) ? $args : $string;

            if (!empty($args) && is_array($args)) {
                foreach ($args as $arg) {
                    switch ($arg) {
                        case 'require':
                            $require = true;
                            break;
                        case 'str':
                        case 'int':
                        case 'bool':
                        case 'float':
                        case 'json':
                            $type = $arg;
                            break;

                        default:
                            break;
                    }

                }
            }

            if (isset($request[$string])) {
                if ($type) {
                    switch ($type) {
                        case 'str':
                            $returnData->$string = trim(strip_tags($request[$string]));
                            break;
                        case 'int':
                            $returnData->$string = intval($request[$string]);
                            break;
                        case 'bool':
                            $str_bool = array('true' => true, 'false' => false);
                            $returnData->$string = (array_key_exists(
                                trim($request[$string]),
                                $str_bool
                            )) ? $str_bool[$request[$string]] : boolval($request[$string]);
                            break;
                        case 'float':
                            $returnData->$string = floatval($request[$string]);
                            break;
                        case 'json':
                            $returnData->$string = json_decode($request[$string]);
                            break;

                        default:
                            break;
                    }
                } else {
                    $returnData->$string = $request[$string];
                }

            } else {
                $returnData->$string = false;

                if ($require) {
                    self::addError('MISSING_DATA');
                    self::addHeader('400', 'Bad Request');
                }

            }

        }

        return $returnData;

    }

    public static function addError($code)
    {

        $message = 'Unknown error';

        if (is_array($code)) {
            list($code, $message) = $code;
        } elseif (!empty(self::$error_codes[$code])) {
            $message = current(self::$error_codes[$code]);
            $code = key(self::$error_codes[$code]);
        }

        if (empty(self::$response['errors'])) {
            self::$response['errors'] = array();
        }

        foreach (self::$response['errors'] as $error) {
            if ($error['code'] == $code) {
                return;
            }
        }

        array_push(self::$response['errors'], array('code' => intval($code), 'message' => $message));

    }

    public static function hasErrors()
    {

        if (empty(self::$response['errors'])) {
            return false;
        }

        return true;

    }

    public static function addErrorCodes($list)
    {

        $max = end(self::$error_codes);
        $max = key($max);

        $int = $max + 1;

        foreach ($list as $key => $message) {
            $code = strtoupper($key);
            if (!in_array($code, self::$error_codes)) {
                self::$error_codes[$code] = array($int => $message);
                $int = $int + 1;
            }

        }

    }

    public static function getErrorCodes()
    {

        return array('data' => self::$error_codes);

    }

    public static function addHeader($code, $message)
    {

        if (empty(self::$headers)) {
            self::$headers = array();
        }

        array_push(self::$headers, array('code' => intval($code), 'message' => $message));

    }

    public static function returnOptions($endpoint) // FIXME if public, return method, headers and data
    {

        header('Access-Control-Allow-Origin: *');
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode(array('options'));
        die();

    }

    public static function returnResponse($data = array())
    {

        $endpoint = self::getEndpoint();

        if (!self::hasErrors()) {
            self::$response = $data;
            if (!empty($data)) {
                self::$response['cached'] = $endpoint->cached;
            }
            if ($endpoint->cached && !array_key_exists('age', self::$response)) {
                self::$response['age'] = 0;
            }
        } else {
            if (!empty($endpoint) && is_numeric($endpoint->errors)) {
                $num = -$endpoint->errors;
                self::$response['errors'] = array_slice(self::$response['errors'], $num);
            }
        }

        if (self::$response == null) {
            self::addError('EMPTY_RESPONSE');
        }

        // add status header
        if (!empty(self::$headers)) {
            foreach (self::$headers as $header) {
                header('HTTP/1.0 '.$header['code'].' '.$header['message']);
            }
        }

        $last_modified = time();

        if (!empty($data['time'])) {
            $len = strlen($data['time']);

            $last_modified = $data['time'];

            if (10 < $len) {
                $last_modified = $data['time'] / 1000;
            }

        }

        header('Access-Control-Allow-Origin: *');

        if (!empty($endpoint->max_age)) {
            header('Expires: ' . gmdate('D, d M Y H:i:s', $last_modified + $endpoint->max_age) . ' GMT');
        }

        header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $last_modified) . ' GMT');
        header('Content-Type: application/json; charset=UTF-8');

        // debug(self::$response);

        echo json_encode(self::$response);
        die();

    }

    public static function getProcess($endpoint)
    {

        $process = array();

        if (!empty($endpoint->callback)) {
            $process[] = 'callback';
        }

        if (!empty($endpoint->template)) {
            $process[] = 'template';
        }

        if (!empty($endpoint->query)) {
            $process[] = 'query';
        }

        if (1 < count($process)) {
            throw new Exception('Can\'t have multiple process methods.');
        }

        return current($process);

    }

    public static function validateRequest($endpoint, $headers, $requestData)
    {

        if (!$endpoint->validate) {
            return true;
        }

        if (!is_array($endpoint->validate)) {
            throw new Exception('Validate key needs to be an array');
        }

        if (empty($endpoint->checksum)) {
            throw new Exception('Checksum key not set in endpoint');
        } else {
            $checksum = strtolower($headers->{"$endpoint->checksum"});
        }

        $validateKeys = array();
        foreach ($endpoint->validate as $key) {
            $validateKeys[] = (!empty($headers->$key)) ? $headers->$key : false;
        }

        $validateKeys = implode('', $validateKeys);

        if ('GET' == $endpoint->method && is_array($requestData)) {
            $requestData = http_build_query($requestData);
        }

        $validationHash = strtolower(md5($validateKeys.':'.$requestData.':'.self::$salt));

        if ($validationHash == $checksum) {
            return true;
        }

        debug('Correct hash: '.$validationHash);

        API::addError('VALIDATION_FAILED');
        API::addHeader('400', 'Bad Request');
        return false;

    }
}
