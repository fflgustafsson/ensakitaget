<?php

namespace SB\Utils;

class Wordpress
{

    public static $admin_menu_separators = array();

    public static function init()
    {

        add_action('admin_init', array(__CLASS__, 'addAdminMenuSeparatorAction'));

    }

    public static function pubDate($date)
    {

        return substr($date, 0, 10);

    }

    public static function getPostParent()
    {

        global $post;
        if ($post->post_parent > 0) {
            return $post->post_parent;
        }
        return $post->ID;

    }

    public static function isPageParent()
    {

        global $post;
        if ($post->post_type != 'page') {
            return;
        }

        if (0 == $post->post_parent) {
            return true;
        }

        return false;

    }

    public static function postNameAsClass($classes)
    {

        global $post;
        if (!isset($post->post_name)) {
            return $classes;
        }
        $classes[] = $post->post_name;
        return $classes;

    }

    public static function setDefaultTemplate($template_php)
    {

        self::$default_template = $template_php;
        add_filter('get_post_metadata', array(__CLASS__, 'postMetaFilterTemplate'), 10, 3);

    }

    public static function postMetaFilterTemplate($a, $post_id, $key)
    {

        if ($key == '_wp_page_template') {
            $post = get_post($post_id);
            if ($post->post_status == 'auto-draft') {
                return self::$default_template;
            }
        }

    }

    // Used when you need to order by meta_value, meta_value_num, since meta_key needs to be present on all sorted posts
    public static function addPostMeta($post_type, $meta_key, $default = null)
    {

        $posts = get_posts(array(
            'numberposts' => -1,
            'post_type' => $post_type
            ));

        foreach ($posts as $post) {
            add_post_meta($post->ID, $meta_key, $default, true);
        }

    }

    public static function isStartPage()
    {
        if ($_SERVER['REQUEST_URI'] == '/') {
            return true;
        }
        return false;
    }

    public static function addAdminMenuSeparator($position)
    {

        self::$admin_menu_separators[] = $position;

    }

    public static function addAdminMenuSeparatorAction()
    {

        global $menu;

        // debug($menu);

        foreach (self::$admin_menu_separators as $pos) {
            if (!empty($menu[$pos])) {
                debug('add_admin_menu_separator: WARNING! Overwriting '.strip_tags($menu[$pos][0]));
            }

            $menu[$pos] = array(
            0 => '',
            1 => 'read',
            2 => 'separator' . $pos,
            3 => '',
            4 => 'wp-menu-separator'
            );

        }

        if (!empty($menu)) {
            ksort($menu);
        }

    }

    public static function disableEmojis()
    {

        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_styles', 'print_emoji_styles');
        remove_filter('the_content_feed', 'wp_staticize_emoji');
        remove_filter('comment_text_rss', 'wp_staticize_emoji');
        remove_filter('wp_mail', 'wp_staticize_emoji_for_email');

        add_filter('tiny_mce_plugins', array(__CLASS__, 'disableEmojisTinymce'));

    }

    public static function disableEmojisTinymce($plugins)
    {

        if (is_array($plugins)) {
            return array_diff($plugins, array('wpemoji'));
        } else {
            return array();
        }

    }

    public static function load404()
    {

        global $wp_query;
        $wp_query->set_404();
        status_header(404);
        get_template_part(404);
        exit();

    }
}
