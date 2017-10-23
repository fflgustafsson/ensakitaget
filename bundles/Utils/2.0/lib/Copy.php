<?php

namespace SB\Utils;

use SB\Utils;

class Copy
{

    public static $post_types = array();

    public static function init($post_types)
    {

        add_filter('post_row_actions', array(__CLASS__, 'rowAction'), 10, 2);
        add_filter('page_row_actions', array(__CLASS__, 'rowAction'), 10, 2);

        self::$post_types = $post_types;

        add_action('wp_ajax_duplicate_post', array(__CLASS__, 'duplicate'));

    }

    public static function currentUrl($post_type)
    {
        return admin_url('edit.php?post_type='.$post_type);
    }

    public static function rowAction($actions, $post)
    {

        $post_types = self::$post_types;
        if (!in_array($post->post_type, $post_types)) {
            return $actions;
        }

        $duplicate = array();
        $duplicate[] = '<a href="" class="duplicate" data-location="'.self::currentUrl($post->post_type).'" ';
        $duplicate[] = 'data-post-id="'.$post->ID.'">Duplicera</a>';

        $actions['duplicate'] = implode('', $duplicate);

        return $actions;

    }

    public static function duplicate()
    {

        if (!Utils::isAjaxRequest()) {
            return;
        }

        $post_id = Utils::postInt('post_id');
        if (empty($post_id)) {
            return;
        }

        $post = get_post($post_id, 'ARRAY_A');
        $post_meta_keys = get_post_custom_keys($post_id);

        $post['ID'] = false;
        $post['post_title'] = $post['post_title'] . ' — kopia';
        $post['post_status'] = 'draft';

        $inserted_post = wp_insert_post($post, true);

        $return = 'ERROR';

        if (!is_wp_error($inserted_post)) {
            foreach ($post_meta_keys as $key) {
                add_post_meta($inserted_post, $key, get_post_meta($post_id, $key, true));
            }
            $return = 'OK';
        }

        header('Content-Type: application/json');
        echo json_encode($return);
        exit;

    }
}
