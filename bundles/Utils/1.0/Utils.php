<?php

namespace SB;

Utils::init();

class Utils {

	public static $version = 2;

	public static $dev_domains = array();
	public static $content_paths = array();
	public static $bundle_versions = array();
	public static $default_template = null;
    public static $lowercase_request = true;

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

		if(!session_id()) {
			session_start();
		}

		add_action('admin_enqueue_scripts', array(__CLASS__, 'javascript'));
		add_action('admin_print_styles', array(__CLASS__, 'stylesheet'));

		add_action('wp_logout', array(__CLASS__, 'destroy_session'));
		add_action('wp_login', array(__CLASS__, 'destroy_session'));

	}

	public static function javascript()
	{

		wp_register_script('sb-utils', self::get_bundle_uri('Utils').'/1.0/js/utils.js', 'jquery', self::$version, true);
		wp_enqueue_script('sb-utils');

	}

	public static function stylesheet()
	{

		wp_register_style('sb-utils-css', Utils::get_bundle_uri('Utils').'/1.0/css/utils.css', false, self::$version);
		wp_enqueue_style('sb-utils-css');

	}

	public static function load($type, $object, $version = false, $parent = true)
	{
		return Utils\Bundles::load($type, $object, $version, $parent);
	}

	public static function get_bundle_uri($bundle)
	{
		return Utils\Bundles::get_bundle_uri($bundle);
	}

	public static function post_string($post_key, $striptags = true)
	{
		return Utils\Requests::post_string($post_key, $striptags);
	}

	public static function post_array($post_key, $striptags = true)
	{
		return Utils\Requests::post_array($post_key, $striptags);
	}

	public static function post_int($post_key)
	{
		return Utils\Requests::post_int($post_key);
	}

	public static function post_var($post_key)
	{
		return Utils\Requests::post_var($post_key);
	}

	public static function get_string($get_key, $striptags = true)
	{
		return Utils\Requests::get_string($get_key, $striptags);
	}

	public static function get_int($get_key)
	{
		return Utils\Requests::get_int($get_key);
	}

	public static function get_cookie($cookie_name)
	{
		return Utils\Requests::get_cookie($cookie_name);
	}

	public static function is_ajax_request()
	{
		return Utils\Requests::is_ajax_request();
	}

	public static function add_post_meta($post_type, $meta_key, $default = null)
	{
		return Utils\Wordpress::add_post_meta($post_type, $meta_key, $default);
	}

	public static function is_start_page()
	{
		return Utils\Wordpress::is_start_page();
	}

	public static function add_template_meta_box($id, $title, $callback, $post_type, $context, $priority, $callback_args, $templates = array())
	{
		return Utils\Templates::add_template_meta_box($id, $title, $callback, $post_type, $context, $priority, $callback_args, $templates);
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
		    $short = mb_substr($short, 0, $limit);
		    $short = mb_substr($short, 0, mb_strrpos($short,' '));
		} else {
		    $short = mb_substr($text, 0, $limit);
		}

	    if (mb_strlen($text) > $limit) {
	        $short = $short.$suffix;
	    }

	    return $short;

	}

	public static function remove_perm($content)
	{

		$CONTENT_DIR = str_replace(self::$content_paths, '', WP_CONTENT_DIR);
		$CONTENT_DIR .= '/uploads/';

		$dev_domains = array(self::wp_siteurl().'/wp-content/uploads/');
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

		return preg_replace('/([a-zA-Z0-9-.]{1,}@([a-zA-Z\.])?[a-zA-Z]{1,}\.[a-zA-Z]{1,4})/i', '<a href="mailto:$1">$1</a>', $content);

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

	public static function mb_lcfirst($str, $enc = null)
	{

		if($enc === null) $enc = mb_internal_encoding();
		return mb_strtolower(mb_substr($str, 0, 1, $enc), $enc).mb_substr($str, 1, mb_strlen($str, $enc), mb_internal_encoding());

	}

	public static function mb_ucfirst($str, $enc = null)
	{

		if($enc === null) $enc = mb_internal_encoding();
		return mb_strtoupper(mb_substr($str, 0, 1, $enc), $enc).mb_substr($str, 1, mb_strlen($str, $enc), mb_internal_encoding());

	}

	public static function get_domain($url)
	{

		preg_match('/(?:http(s)?:\/\/)?([^\/]*)/', $url, $match);
		if (isset($match[2])) return $match[2];
		return $url;

	}

	public static function domain_name()
	{

		$WP_SITEURL = self::wp_siteurl();
		$protocol = (isset($_SERVER['HTTPS'])) ? 'https://' : 'http://';
		$domain = str_replace($protocol, '', $WP_SITEURL);
		return str_replace('/', '', $domain);

	}

	public static function wp_siteurl()
	{

		if (defined('WP_SITEURL')) return WP_SITEURL;
		$protocol = (isset($_SERVER['HTTPS'])) ? 'https://' : 'http://';
		return $protocol.$_SERVER['HTTP_HOST'];

	}

	public static function destroy_session()
	{

		session_destroy();

	}

	public static function register_copy($post_types = array('post', 'page'))
	{
		Utils\Copy::init($post_types);
	}

	public static function register_load_more($config)
	{
		Utils\LoadMore::init($config);
	}

	public static function return_JSON($data)
	{

		header('Content-Type: application/json');
		echo json_encode($data);
		die();

	}

	public static function get_project_path()
	{

		return str_replace('wordpress', '', $_SERVER["DOCUMENT_ROOT"]);

	}

	public static function tracker()
	{

		add_action('shutdown', '\SB\Utils\Developer::shutdown', 9999);

	}

	public static function disable_emojis()
	{

		add_action('init', '\SB\Utils\Wordpress::disable_emojis');

	}

}
