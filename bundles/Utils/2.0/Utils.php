<?php

namespace SB;

class Utils
{

    public static $version = 2;

    public static $dev_domains = array();
    public static $content_paths = array();
    public static $loaded_bundles = array();
    public static $default_template = null;

    public static function init()
    {

        require_once('lib/Developer.php');
        require_once('lib/Bundles.php');
        require_once('lib/Wordpress.php');
        require_once('lib/Requests.php');
        require_once('lib/Copy.php');
        require_once('lib/LoadMore.php');
        require_once('lib/Firewall.php');
        require_once('lib/Templates.php');

        Utils\Wordpress::init();
        Utils\Requests::init();
        Utils\Developer::init();

        if (!session_id()) {
            session_start();
        }

        add_action('admin_enqueue_scripts', array(__CLASS__, 'javascript'));
        add_action('admin_print_styles', array(__CLASS__, 'stylesheet'));

        add_action('wp_logout', array(__CLASS__, 'destroySession'));
        add_action('wp_login', array(__CLASS__, 'destroySession'));

        Utils\Bundles::addToLoaded('Utils', '2.0');

    }

    public static function javascript()
    {

        wp_register_script('sb-utils', self::getBundleUri('Utils').'/js/utils.js', 'jquery', self::$version, true);
        wp_enqueue_script('sb-utils');

    }

    public static function stylesheet()
    {

        wp_register_style('sb-utils-css', self::getBundleUri('Utils').'/css/utils.css', false, self::$version);
        wp_enqueue_style('sb-utils-css');

    }

    public static function load($type, $object, $version = null, $function = false, $parent = true)
    {
        return Utils\Bundles::load($type, $object, $version, $function, $parent);
    }

    public static function getBundlePath($bundle)
    {
        return Utils\Bundles::getBundlePath($bundle);
    }

    public static function getBundleUri($bundle)
    {
        return Utils\Bundles::getBundleUri($bundle);
    }

    public static function get_bundle_uri($bundle)
    {
        self::deprecated('get_bundle_uri', 'getBundleUri');
        return Utils\Bundles::getBundleUri($bundle);
    }

    // Requests / Legacy
    public static function post_string($post_key, $striptags = true)
    {
        self::deprecated('post_string', 'postString');
        return Utils\Requests::postString($post_key, $striptags);
    }

    public static function post_array($post_key, $striptags = true)
    {
        self::deprecated('post_array', 'postArray');
        return Utils\Requests::postArray($post_key, $striptags);
    }

    public static function post_int($post_key)
    {
        self::deprecated('post_int', 'postInt');
        return Utils\Requests::postInt($post_key);
    }

    public static function post_var($post_key)
    {
        self::deprecated('post_var', 'postVar');
        return Utils\Requests::postVar($post_key);
    }

    public static function get_string($get_key, $striptags = true)
    {
        self::deprecated('get_string', 'getString');
        return Utils\Requests::getString($get_key, $striptags);
    }

    public static function get_int($get_key)
    {
        self::deprecated('get_int', 'getInt');
        return Utils\Requests::getInt($get_key);
    }

    public static function get_cookie($cookie_name)
    {
        self::deprecated('get_cookie', 'getCookie');
        return Utils\Requests::getCookie($cookie_name);
    }

    public static function is_ajax_request()
    {
        self::deprecated('is_ajax_request', 'isAjaxRequest');
        return Utils\Requests::isAjaxRequest();
    }

    public static function postString($post_key, $striptags = true)
    {
        return Utils\Requests::postString($post_key, $striptags);
    }

    public static function postArray($post_key, $striptags = true)
    {
        return Utils\Requests::postArray($post_key, $striptags);
    }

    public static function postInt($post_key)
    {
        return Utils\Requests::postInt($post_key);
    }

    public static function postVar($post_key)
    {
        return Utils\Requests::postVar($post_key);
    }

    public static function getString($get_key, $striptags = true)
    {
        return Utils\Requests::getString($get_key, $striptags);
    }

    public static function getInt($get_key)
    {
        return Utils\Requests::getInt($get_key);
    }

    public static function serverString($server_key)
    {
        return Utils\Requests::serverString($server_key);
    }

    public static function getCookie($cookie_name)
    {
        return Utils\Requests::getCookie($cookie_name);
    }

    public static function isAjaxRequest()
    {
        return Utils\Requests::isAjaxRequest();
    }

    // Wordpress
    public static function addPostMeta($post_type, $meta_key, $default = null)
    {
        return Utils\Wordpress::addPostMeta($post_type, $meta_key, $default);
    }

    public static function isStartPage()
    {
        return Utils\Wordpress::isStartPage();
    }

    public static function addTemplateMetaBox(
        $id,
        $title,
        $callback,
        $post_type,
        $context,
        $priority,
        $callback_args,
        $templates = array()
    ) {
        return Utils\Templates::addTemplateMetaBox(
            $id,
            $title,
            $callback,
            $post_type,
            $context,
            $priority,
            $callback_args,
            $templates
        );
    }

    public static function deprecated($old_function, $new_function)
    {

        $array = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        self::debug($array);
        foreach ($array as $data) {
            if (!empty($data['function']) && $data['function'] == $old_function) {
                $details = (!empty($data['file']) && !empty($data['line'])) ? $data['file'].'::'.$data['line'] : false;
                self::console('Deprecated: '.$old_function.', use '.$new_function.' '.$details);
            }
        }

    }

    public static function console($data)
    {

        return Utils\Developer::console($data);

    }

    public static function debug($data, $backtrace = 1)
    {

        return Utils\Developer::debug($data, $backtrace);

    }

    public static function shorten($text, $limit, $suffix = '&hellip;', $rough = false)
    {

        $text = strip_tags($text);

        if (!$rough) {
            $short = $text.' ';
            $short = mb_substr($short, 0, $limit + 1);
            $short = mb_substr($short, 0, mb_strrpos($short, ' '));
        } else {
            $short = mb_substr($text, 0, $limit);
        }

        if (mb_strlen($text) > $limit) {
            $short = $short.$suffix;
        }

        return $short;

    }

    public static function removePerm($content)
    {

        $CONTENT_DIR = str_replace(self::$content_paths, '', WP_CONTENT_DIR);
        $CONTENT_DIR .= '/uploads/';

        $dev_domains = array(self::wpSiteurl().'/wp-content/uploads/');
        foreach (self::$dev_domains as $url) {
            $dev_domains[] = $url.'/wp-content/uploads/';
        }

        return str_replace($dev_domains, $CONTENT_DIR, $content);

    }

    public static function urlify($content, $target = false)
    {

        $target = ($target) ? ' target="_blank"' : false;
        return preg_replace('/(https?:\/\/[^\s]+)/', '<a href="$1"'.$target.'>$1</a>', $content);

    }

    public static function emailify($content)
    {

        return preg_replace(
            '/([a-zA-Z0-9-.]{1,}@([a-zA-Z\.])?[a-zA-Z]{1,}\.[a-zA-Z]{1,4})/i',
            '<a href="mailto:$1">$1</a>',
            $content
        );

    }

    public static function phonify($content)
    {

        return preg_replace('/(\d{2}-\d{3}\s?\d{2}\s?\d{2})/', '<a href="tel:$1">$1</a>', $content);

    }

    public static function linkify($content, $target = false)
    {

        $content = self::urlify($content, $target);
        $content = self::emailify($content);
        $content = self::phonify($content);
        return $content;

    }

    public static function mbLcfirst($str, $enc = null)
    {

        if ($enc === null) {
            $enc = mb_internal_encoding();
        }

        return mb_strtolower(mb_substr($str, 0, 1, $enc), $enc)
            .mb_substr($str, 1, mb_strlen($str, $enc), mb_internal_encoding());

    }

    public static function mbUcfirst($str, $enc = null)
    {

        if ($enc === null) {
            $enc = mb_internal_encoding();
        }

        return mb_strtoupper(mb_substr($str, 0, 1, $enc), $enc)
            .mb_substr($str, 1, mb_strlen($str, $enc), mb_internal_encoding());

    }

    public static function getDomain($url)
    {

        preg_match('/(?:http(s)?:\/\/)?([^\/]*)/', $url, $match);
        if (isset($match[2])) {
            return $match[2];
        }
        return $url;

    }

    public static function domainName()
    {

        $WP_SITEURL = self::wpSiteurl();
        $protocol = (isset($_SERVER['HTTPS'])) ? 'https://' : 'http://';
        $domain = str_replace($protocol, '', $WP_SITEURL);
        return str_replace('/', '', $domain);

    }

    public static function wpSiteurl()
    {

        if (defined('WP_SITEURL')) {
            return WP_SITEURL;
        }
        $protocol = (isset($_SERVER['HTTPS'])) ? 'https://' : 'http://';
        return $protocol.$_SERVER['HTTP_HOST'];

    }

    public static function destroySession()
    {

        session_destroy();

    }

    public static function registerCopy($post_types = array('post', 'page'))
    {
        Utils\Copy::init($post_types);
    }

    public static function registerLoadMore($config)
    {
        Utils\LoadMore::init($config);
    }

    public static function returnJSON($data)
    {

        header('Content-Type: application/json');
        echo json_encode($data);
        die();

    }

    public static function getProjectPath()
    {

        return str_replace('wordpress', '', $_SERVER["DOCUMENT_ROOT"]);

    }

    public static function tracker()
    {

        add_action('shutdown', '\SB\Utils\Developer::shutdown', 9999);

    }

    public static function disableEmojis()
    {

        add_action('init', '\SB\Utils\Wordpress::disableEmojis');

    }
}
