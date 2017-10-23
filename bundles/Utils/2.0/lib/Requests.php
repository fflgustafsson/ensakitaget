<?php

namespace SB\Utils;

class Requests
{

    public static function init()
    {

        add_action('parse_request', array(__CLASS__, 'parseRequest'));

    }

    /**
     * Helper to retrieve $_POST var, untyped
     * @param string $post_key
     * @return mixed
     */
    public static function postVar($post_key)
    {

        if (!isset($post_key)) {
            return false;
        }

        if (!isset($_POST[$post_key])) {
            return false;
        }

        return $_POST[$post_key];

    }

    /**
     * Helper to retrieve $_POST string
     * @param string $post_key
     * @param boolean $striptags
     * @return string
     */
    public static function postString($post_key, $striptags = true)
    {

        if (!isset($post_key)) {
            return false;
        }

        if (!isset($_POST[$post_key])) {
            return false;
        }

        if (!$striptags) {
            return trim($_POST[$post_key]);
        }

        $string = trim(strip_tags($_POST[$post_key]));
        return $string;

    }

    /**
     * Helper to retrieve $_POST array
     * @param string $post_key
     * @param boolean $striptags
     * @return array
     */
    public static function postArray($post_key, $striptags = true)
    {

        if (!isset($post_key)) {
            return false;
        }

        if (!isset($_POST[$post_key])) {
            return false;
        }

        if (!is_array($_POST[$post_key])) {
            return false;
        }

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

    /**
     * Helper to retrieve $_POST string
     * @param string $post_key
     * @return string
     */
    public static function postInt($post_key)
    {

        if (!isset($post_key)) {
            return false;
        }

        if (!isset($_POST[$post_key])) {
            return false;
        }

        $val = str_replace(',', '.', $_POST[$post_key]);
        if (is_numeric($val)) {
            return $val;
        }

        return false;

    }

    /**
     * Helper to retrieve $_GET string
     * @param string $get_key
     * @param boolean $striptags
     * @return string
     */
    public static function getString($get_key, $striptags = true)
    {

        if (!isset($get_key)) {
            return false;
        }

        if (!isset($_GET[$get_key])) {
            return false;
        }

        $string = trim(strip_tags($_GET[$get_key]));

        return $string;

    }

    /**
     * Helper to retrieve $_GET integer
     * @param integer $get_key
     * @return integer
     */
    public static function getInt($get_key)
    {

        if (!isset($get_key)) {
            return false;
        }

        if (!isset($_GET[$get_key])) {
            return false;
        }

        $val = str_replace(',', '.', $_GET[$get_key]);

        if (is_numeric($val)) {
            return $val;
        }

        return false;

    }

    /**
     * Helper to retrieve $_SERVER string
     * @param string $server_key
     * @return string
     */
    public static function serverString($server_key)
    {

        if (!isset($server_key)) {
            return false;
        }

        if (!isset($_SERVER[$server_key])) {
            return false;
        }

        $string = trim(strip_tags($_SERVER[$server_key]));
        return $string;

    }

    /**
     * Conditional if request is an ajax call
     * @return boolean
     */
    public static function isAjaxRequest()
    {

        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
            && 'xmlhttprequest' == strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])) {
            return true;
        }

        return false;

    }

    /**
     * Helper to retrieve $_COOKIE string
     * @param string $cookie_name
     * @return string
     */
    public static function getCookie($cookie_name)
    {

        if (!isset($cookie_name)) {
            return false;
        }

        if (!isset($_COOKIE[$cookie_name])) {
            return false;
        }

        $value = trim(strip_tags($_COOKIE[$cookie_name]));
        return $value;

    }

    /**
     * Conditional to see if / is loaded without checking the query.
     */
    public static function isStartPage()
    {

        if ($_SERVER['REQUEST_URI'] == '/') {
            return true;
        }

        return false;

    }

    /**
     * Conditional to see if request comes from Facebook (or from Facebook-iframe).
     */
    public static function fromFacebook()
    {

        if (strpos($_SERVER['HTTP_USER_AGENT'], 'facebook') !== false) {
            return true;
        }

        if (isset($_GET['fb'])) {
            return true;
        }

        if (isset($_REQUEST['signed_request'])) {
            return true;
        }

        return false;

    }

    /**
     * This method takes care of uppercase requests. Different case is not ok in SEO.
     * @param Object $wp
     * @return none
     */
    public static function parseRequest($wp)
    {

        $query = $wp->query_vars;

        $link = site_url();

        $requested = $_SERVER['REQUEST_URI'];

        if (!empty($requested) && strlen($requested) > 1) {
            if (preg_match('/[A-Z]/', $requested)) {
                $link .= strtolower($requested);
                wp_redirect($link, '301');
                exit;
            }
        }

    }
}
