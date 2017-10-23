<?php

namespace SB\Forms;

use SB\Utils;
use \stdClass;

class Ajax
{

    public static function init()
    {
        // AJAX
        add_action('wp_ajax_get_attachment_element', array(__CLASS__, 'getMediaElementAjax'));
        add_action('wp_ajax_get_post_search_result', array(__CLASS__, 'getPostSearchResult'));
        add_action('wp_ajax_get_user_search_result', array(__CLASS__, 'getUserSearchResult'));
        add_action('wp_ajax_get_post_element', array(__CLASS__, 'getPostElementAjax'));
        add_action('wp_ajax_get_user_element', array(__CLASS__, 'getUserElementAjax'));
    }

    public static function getMediaElementAjax()
    {

        if (!Utils::isAjaxRequest()) {
            return false;
        }

        $media = Utils::postArray('media_id');
        $size = Utils::postString('media_size');
        $remove = Utils::postString('remove');
        $name = Utils::postString('name');

        $element = array();
        foreach ($media as $media_id) {
            $element[] = Common::getMediaElement($media_id, $size, false, $remove, $name);
        }

        header('Content-Type: application/json');
        echo json_encode($element);
        exit;

    }

    public static function getPostSearchResult()
    {

        if (!Utils::isAjaxRequest()) {
            return false;
        }

        $field_name = Utils::postString('field');
        $post_type = Utils::postArray('post_type');
        $meta_key = Utils::postString('meta_key');
        $is_list = Utils::postString('list');
        $limit = Utils::postInt('limit');
        $search = Utils::postString('search');
        $custom_template = false;
        $get_post_meta = false;

        $limit = ($limit) ? $limit : 10;
        $limit = ($is_list == 'true') ? '' : 'LIMIT '.$limit;

        $std_post_values = array(
            'post_author',
            'post_date',
            'post_date_gmt',
            'post_content',
            'post_excerpt',
            'post_name',
            'post_modified',
            'post_modified_gmt'
        );

        if (is_numeric(($key = array_search('custom_template', $post_type)))) {
            unset($post_type[$key]);
            $custom_template = true;
        }

        $query_pts = array();
        foreach ($post_type as $pt) {
            $query_pts[] = "post_type = '$pt'";
        }
        $query_pts = implode(' OR ', $query_pts);

        if (in_array($meta_key, $std_post_values)) {
            $meta_query = $meta_key.' as meta_value,';
        } else {
            $meta_query = '';
            $get_post_meta = true;
        }

        global $wpdb;
        $return = $wpdb->get_results($wpdb->prepare("SELECT ID, $meta_query post_title, post_type, DATE_FORMAT(post_date_gmt, '%%Y-%%m-%%d %%H:%%i:%%s') as post_date from $wpdb->posts where post_title like %s AND post_status = 'publish' AND ($query_pts) ORDER BY post_date DESC $limit", array("%$search%")));

        if (!empty($return)) {
            foreach ($return as $post) {
                $pt_obj = get_post_type_object($post->post_type);
                $post->post_type = $pt_obj->labels->singular_name;
                $img = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'thumbnail');
                if (!empty($img)) {
                    $post->post_thumbnail = $img[0];
                }
                $post->permalink = str_replace(Utils::wpSiteurl(), '', get_permalink($post->ID));
                $post->post_title = _draft_or_post_title($post->ID);
                $post->short_title = Utils::shorten(_draft_or_post_title($post->ID), 35);

                if (!empty($post->meta_value)) {
                    $post->meta_value = Utils::shorten(strip_tags(html_entity_decode($post->meta_value)), 150, '...');
                }

                if ($get_post_meta) {
                    $post->meta_value = Utils::shorten(
                        strip_tags(html_entity_decode(get_post_meta($post->ID, $meta_key, true))),
                        150,
                        '...'
                    );
                }

            }
        }

        if ($custom_template) {
            // add registered nav_items to results
            if (class_exists('SB\NavMenu')) {
                if (!empty(SB\NavMenu::$nav_items)) {
                    foreach (SB\NavMenu::$nav_items as $targets) {
                        $find = stripos($targets['addmenulabel'], $search);
                        if ($find !== false) {
                            $post = new stdClass();
                            $post->ID = $targets['id'];
                            $post->post_title = $targets['addmenulabel'];
                            $post->short_title = Utils::shorten($post->post_title, 35);
                            $post->post_date = date('Y-m-d H:i:s');
                            if ($targets['type'] == 'uri') {
                                $post->permalink = $targets['uri'];
                                $post->post_type = 'Special';
                            }
                            if ($targets['type'] == 'custom_post_type') {
                                $post->permalink = str_replace(
                                    Utils::wpSiteurl(),
                                    '',
                                    get_post_type_archive_link($targets['id'])
                                );
                                $post->post_type = 'Arkiv';
                            }
                            $return[] = $post;
                        }
                    }
                }
            }
        }

        $return = apply_filters('SB_Post_Search_Results', $return, $field_name);

        header('Content-Type: application/json');
        echo json_encode($return);
        exit;

    }

    public static function getUserSearchResult()
    {


        if (!Utils::isAjaxRequest()) {
            return false;
        }

        $field_name = Utils::postString('field');
        $post_type = Utils::postArray('post_type');
        $meta_key = Utils::postString('meta_key');
        $search = Utils::postString('search');

        global $wpdb;
        $result = $wpdb->get_results($wpdb->prepare(
            "SELECT ID, user_login, user_email, display_name FROM $wpdb->users WHERE user_login LIKE %s OR user_email LIKE %s OR display_name LIKE %s ORDER BY display_name DESC",
            array(
                "%$search%",
                "%$search%",
                "%$search%"
                )
        ));

        $return = array();

        if (!empty($result)) {
            foreach ($result as $item) {
                $user = new stdClass();

                $user->ID = $item->ID;
                $user->post_title = $item->display_name.' ('.$item->user_login.')';
                $user->permalink = admin_url('user-edit.php?user_id='.$item->ID);
                $user->meta_value = $item->user_email;

                $return[] = $user;
            }
        }

        $return = apply_filters('SB_User_Search_Results', $return, $field_name);

        header('Content-Type: application/json');
        echo json_encode($return);
        exit;

    }

    public static function getPostElementAjax()
    {

        if (!Utils::isAjaxRequest()) {
            return false;
        }

        // if (!Utils::is_ajax_request() || $load_data_via_ajax == true) {
        // FIXME usage in widgets??

        $post_id = Utils::postString('post_id');
        $remove = Utils::postString('remove');
        $name = Utils::postString('name');
        $meta_key = Utils::postString('meta');
        $image_meta = Utils::postString('image');

        $element[] = Common::getPostElement($post_id, $remove, $name, false, $meta_key, $image_meta);

        header('Content-Type: application/json');
        echo json_encode($element);
        exit;

    }

    public static function getUserElementAjax()
    {

        if (!Utils::isAjaxRequest()) {
            return false;
        }

        // if (!Utils::is_ajax_request() || $load_data_via_ajax == true) {
        // FIXME usage in widgets??

        $post_id = Utils::postString('post_id');
        $remove = Utils::postString('remove');
        $name = Utils::postString('name');
        $meta_key = Utils::postString('meta');
        $image_meta = Utils::postString('image');

        $element[] = Common::getUserElement($post_id, $remove, $name, false);

        header('Content-Type: application/json');
        echo json_encode($element);
        exit;

    }
}
