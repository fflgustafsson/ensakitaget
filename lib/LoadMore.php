<?php

// Examples

function printPuff($post, $class, $echo = true)
{

    $classes = array('puff', $class);

    $puff = array();
    $puff[] = '<div class="'.implode(' ', $classes).'" data-id="'.$post->ID.'">';
    $puff[] = $post->post_title;
    $puff[] = '</div>';

    if ($echo) {
        echo implode('', $puff);
        return;
    }

    return implode('', $puff);

}

add_filter('SB_Load_More', 'ajaxPrintPuff', 10, 2);

function ajaxPrintPuff($posts, $config_name)
{

    $return = array();

    // if ($config_name == 'article') {

    foreach ($posts as $post) {
        Utils::debug($post);
        // $return[] = printPuff($post, 'hidden', false);
    }

    // }

    return $return;

}



add_action('SB_save_option_common_settings_chosen_posts', 'updateChosenOrder', 10);

function updateChosenOrder($value) // array(med id)
{

    $meta_key = '_selected';
    $post_type = 'post';

    \SB\Utils::addPostMeta($post_type, $meta_key, 0);

    foreach ($value as $order => $id) {
        update_post_meta($id, $meta_key, $order);
    }

    end($value);
    $order = key($value);
    $order++;

    $query = new WP_Query(array(
        'post_type' => $post_type,
        'post__not_in' => $value,
        'posts_per_page' => -1,
        'orderby' => 'post_date',
        'order' => 'DESC'
    ));

    foreach ($query->posts as $post) {
        update_post_meta($post->ID, $meta_key, $order++);
    }

}


add_action('save_post', 'postMetaSave');

function postMetaSave($post_id)
{

    $post = get_post($post_id);
    if ($post->post_type != 'post') {
        return;
    }

    $chosen_posts = get_option('chosen_posts');
    updateChosenOrder($chosen_posts);

}
