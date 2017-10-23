<?php

namespace SB\Utils;

use SB\Utils;

Requests::init();

class Requests {

	public static function init()
	{

        add_action('parse_request', array(__CLASS__, 'parse_request'));

	}

	public static function post_var($post_key)
	{

		if (!isset($post_key)) return false;
		if (!isset($_POST[$post_key])) return false;

		return $_POST[$post_key];

	}

	public static function post_string($post_key, $striptags = true)
	{

		if (!isset($post_key)) return false;
		if (!isset($_POST[$post_key])) return false;
		if (!$striptags) {
			return trim($_POST[$post_key]);
		}
		$string = trim(strip_tags($_POST[$post_key]));
		return $string;
	}

	public static function post_array($post_key, $striptags = true)
	{
		if (!isset($post_key)) return false;
		if (!isset($_POST[$post_key])) return false;
		if (!is_array($_POST[$post_key])) return false;
		$array = array();
		foreach ($_POST[$post_key] as $key => $value) {
			if (!is_array($value)) {
				$value = trim($value);
				if ($striptags) {
					$value = strip_tags($value);
				}
			}
			$array[$key] = $value;
		}
		return $array;
	}

	public static function post_int($post_key)
	{
		if (!isset($post_key)) return false;
		if (!isset($_POST[$post_key])) return false;
		$val = str_replace(',' ,'.', $_POST[$post_key]);
		if (is_numeric($val)) return $val;
		return false;
	}

	public static function get_string($get_key, $striptags = true)
	{
		if (!isset($get_key)) return false;
		if (!isset($_GET[$get_key])) return false;
		$string = trim(strip_tags($_GET[$get_key]));
		return $string;
	}

	public static function get_int($get_key)
	{
		if (!isset($get_key)) return false;
		if (!isset($_GET[$get_key])) return false;
		$val = str_replace(',', '.', $_GET[$get_key]);
		if (is_numeric($val)) return $val;
		return false;
	}

	public static function is_ajax_request()
	{
		if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
			&& 'xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH']))
		{
			return true;
		}

		return false;

	}

	public static function get_cookie($cookie_name)
	{

		if (!isset($cookie_name)) return false;
		if (!isset($_COOKIE[$cookie_name])) return false;
		$value = trim(strip_tags($_COOKIE[$cookie_name]));
		return $value;

	}

	/**
	 * Conditional to see if / is loaded without checking the query.
	 */
	public static function is_start_page()
	{
		if ($_SERVER['REQUEST_URI'] == '/') return true;
		return false;
	}

	/**
	 * Conditional to see if request comes from Facebook (or from Facebook-iframe).
	 */
	public static function from_facebook()
	{

		if (strpos($_SERVER['HTTP_USER_AGENT'], 'facebook') !== false){
			return true;
		}

		if (isset($_GET['fb'])){
			return true;
		}

		if (isset($_REQUEST['signed_request'])){
			return true;
		}

		return false;

	}

	/**
	 * This method takes care of uppercase requests. Different case is not ok in SEO.
	 * @param Object $wp
	 * @return none
	 */
	public static function parse_request($wp){

		$query = $wp->query_vars;

		$link = site_url();

		$requested = $_SERVER['REQUEST_URI'];

		if( !empty($requested) && strlen($requested) > 1 ) {

			if( preg_match('/[A-Z]/', $requested) ) {
				$link .= strtolower($requested);
				wp_redirect($link, '301');
				exit;
			}
		}

	}

}