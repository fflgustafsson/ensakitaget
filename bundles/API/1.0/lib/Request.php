<?php

namespace SB;

use SB\Utils;
use SB\API\Cache;
use \Exception;

// Utils::debug($_SERVER, 0);
// Utils::debug($_GET, 0);

// https://dev.twitter.com/overview/api/response-codes
// http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html

$endpoint = API::getEndpoint();
$contentType = null;
$headers = array();
$data = array();
$return = true;
$requestData = null;
$vars = null;

Utils::debug('Endpoint: '.$endpoint->id, 0);

if (empty($endpoint)) {
    API::addError('EMPTY_RESPONSE');
    API::addHeader('404', 'Not Found');
    API::returnResponse();
}

// Public
if (!$endpoint->public) {
    $current_user = wp_get_current_user();
    if (0 == $current_user->ID) {
        API::addError('BAD_AUTH');
        API::addHeader('401', 'Unauthorized');
    }
}

// Method
if (Utils::serverString('REQUEST_METHOD') == 'OPTIONS') {
    API::returnOptions($endpoint);
}

if (strtolower(Utils::serverString('REQUEST_METHOD')) != strtolower($endpoint->method)) {
    API::addError('WRONG_METHOD');
    API::addHeader('400', 'Bad Request');
}

// Headers
if (!empty($endpoint->headers)) {
    $requestHeaders = apache_request_headers();
    $contentType = (!empty($requestHeaders['Content-Type'])) ? $requestHeaders['Content-Type'] : false;
    $headers = API::requestData($endpoint->headers, $requestHeaders);
}

// Validate data
if (!empty($endpoint->data)) {
    $method = strtolower($endpoint->method);

    switch ($method) {
        case 'get':
            $requestData = $_GET;
            break;

        case 'post':
            if ($contentType == 'application/x-www-form-urlencoded') {
                $requestData = $_POST;
            } else {
                $requestData = @file_get_contents("php://input");
            }
            // Force no cache because of default
            $endpoint->cached = false;
            break;

        default:
            $requestData = array();
            break;
    }

    API::$validationData = $requestData;
    $vars = API::requestData($endpoint->data, $requestData, $endpoint->type);

}

// Validate request
if (!API::validateRequest($endpoint, $headers, $requestData)) {
    API::returnResponse();
}

// Make custom validation
do_action('SB_API_validate_request', $endpoint, $headers, $requestData);

// Process
if (API::hasErrors()) {
    API::returnResponse();
}

// Cache
if ($endpoint->cached) {
    $data = Cache::read($endpoint, $vars);
    if (!empty($data)) {
        API::returnResponse($data);
    }
}

$process = API::getProcess($endpoint);

if (!empty($process)) {
    switch ($process) {
        case 'callback':
            if (is_callable($endpoint->callback)) {
                $data = call_user_func_array($endpoint->callback, array($endpoint, $headers, $vars));
            } elseif (is_array($endpoint->callback) && is_callable('SB\\'.implode('::', $endpoint->callback))) {
                $data = call_user_func_array(
                    'SB\\'.implode('::', $endpoint->callback),
                    array($endpoint, $headers, $vars)
                );
            } else {
                throw new Exception('Callback not found.');
            }
            break;

        case 'template':
            locate_template(array($endpoint->template), true, true);
            // README If you use a stand-alone template you're on your own from now on
            // or you can call API::returnResponse with the results of that template
            // Cache write on purge is not yet implemented
            $return = false;
            break;

        case 'query':
            throw new Exception('Not yet implemented.');
            break;

        default:
            throw new Exception('Must have a known process, "callback", "template" or "query".');
            break;
    }
}

if ($endpoint->cached) {
    Cache::write($data, $endpoint, $vars);
}

// global $wpdb;
// debug($wpdb);

if ($return) {
    API::returnResponse($data);
}

die();
