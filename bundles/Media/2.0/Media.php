<?php

namespace SB;

use SB\Utils;

class Media
{

    public static $dependencies = array(
    'Utils' => '2.0'
    );

    public static $featured_image_post_meta = array();
    public static $image_sizes = array();
    // public static $chooser_sizes = array();

    public static function init()
    {

        require_once('lib/Regenerate.php');
        require_once('lib/Responsive.php');

        Media\Responsive::init(); // FIXME
        Media\Regenerate::init();

        // Filters / Actions
        add_theme_support('post-thumbnails');
        add_action('after_setup_theme', array(__CLASS__, 'themeImageSizes'));

        add_action('manage_media_columns', array(__CLASS__, 'addPostsColumns'));
        add_action('manage_media_custom_column', array(__CLASS__, 'addPostColumnData'), 10, 2);

        add_filter('sanitize_file_name', array(__CLASS__, 'sanitizeFileName'));
        add_filter('wp_get_attachment_metadata', array(__CLASS__, 'metadata'), 10, 2);

        // add_filter('image_size_names_choose', array(__CLASS__, 'imageChoserSizes'));

    }

    public static function metadata($data, $post_id)
    {

        $meta = get_post($post_id);

        $data['post_metadata'] = array();
        $data['post_metadata']['title'] = $meta->post_title;
        $data['post_metadata']['heading'] = $meta->post_excerpt;
        $data['post_metadata']['alt'] = get_post_meta($post_id, '_wp_attachment_image_alt', true);
        $data['post_metadata']['description'] = $meta->post_content;

        return $data;
    }

    public static function get($media_id, $size = 'large')
    {

        $media = array();

        if (empty($media_id)) {
            return false;
        }

        if (is_array($media_id)) {
            Utils::debug('Notice: Media::get() does not accept arrays as media_id');
            return;
        }

        $mime_type = get_post_mime_type($media_id);

        if (strpos('image', $mime_type) !== false) {
            $media['src'] = wp_get_attachment_image_src($media_id, $size);
            $media['type'] = 'image';
        } elseif (strpos('video', $mime_type) !== false) {
            $media['src'] = wp_get_attachment_image_src($media_id, $size);
            $media['type'] = 'video';
        } else {
            $media['src'] = wp_get_attachment_image_src($media_id, $size, true);
            $media['type'] = 'document';
        }

        $media['file'] = wp_get_attachment_url($media_id);
        $media['meta'] = wp_get_attachment_metadata($media_id, false);

        return $media;

    }

    public static function sanitizeFileName($file)
    {

        return preg_replace('/[^a-zA-Z0-9._-]/', '', $file);

    }

    public static function registerImageSizes($image_sizes)
    {

        self::$image_sizes = $image_sizes;

    }

    public static function registerChooserSizes($chooser_sizes)
    {

        self::$chooser_sizes = $chooser_sizes;

    }

    public static function themeImageSizes()
    {

        if (empty(self::$image_sizes)) {
            return;
        }

        foreach (self::$image_sizes as $name => $data) {
            // false: soft proportional crop mode, true: hard crop mode.
            $crop = (!empty($data[2]) && $data[2] == 'soft') ? false : true;
            add_image_size($name, $data[0], $data[1], $crop);

        }

    }

    public static function getPostThumbnailData($post_id, $size = 'large')
    {

        if (!has_post_thumbnail($post_id)) {
            return false;
        }

        $image = array();

        $image_id = get_post_thumbnail_id($post_id);
        $image['src'] = wp_get_attachment_image_src($image_id, $size);
        $image['alt'] = get_post_meta($image_id, '_wp_attachment_image_alt', true);
        $image['meta'] = get_post($image_id);

        return $image;

    }

    public static function getPostThumbnailElement($post_id, $size = 'large')
    {

        $image = self::getPostThumbnailData($post_id);

        if (!$image) {
            return false;
        }

        return '<figure><img src="'.$image['src'].'" alt="'.$image['alt'].'"><figcaption>'.$image['meta']->post_excerpt.'</figcaption></figure>';

    }

    public static function getPostThumbnail($post_id, $size = 'large')
    {

        if (!has_post_thumbnail($post_id)) {
            return false;
        }

        $image_id = get_post_thumbnail_id($post_id);
        $image_src = wp_get_attachment_image_src($image_id, $size);
        $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true);
        $image_meta = get_post($image_id);
        return '<figure><img src="'.$image_src[0].'" alt="'.$image_alt.'"><figcaption>'.$image_meta->post_excerpt.'</figcaption></figure>';

    }

    public static function addPostsColumns($columns)
    {

        $new_columns = array();
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key == 'title') {
                $new_columns['dimensions'] = 'Original';
            }
        }

        return $new_columns;
    }

    public static function addPostColumnData($column, $post_id)
    {

        switch ($column) {
            case 'dimensions':
                $image = wp_get_attachment_image_src($post_id, 'full');
                echo $image[1].'x'.$image[2];
                break;

            default:
                break;
        }

    }

    public static function registerFeaturedImageColumn($args)
    {

        self::$featured_image_post_meta = $args;
        foreach ($args as $post_type => $post_meta) {
            add_action('manage_'.$post_type.'_posts_columns', array(__CLASS__, 'addFeaturedImageColumn'));
            add_action('manage_'.$post_type.'_posts_custom_column', array(__CLASS__, 'addFeaturedImageColunmData'), 10, 2);
        }

    }

    public static function addFeaturedImageColumn($columns)
    {

        $new_columns = array();
        foreach ($columns as $key => $value) {
            $new_columns[$key] = $value;
            if ($key == 'cb') {
                $new_columns['featured-image'] = _x('Image', 'Post format');
            }
        }

        return $new_columns;
    }

    public static function addFeaturedImageColunmData($column, $post_id)
    {

        $post_meta = self::$featured_image_post_meta;

        switch ($column) {
            case 'featured-image':
                $post_type = get_post_type($post_id);

                if ($post_meta[$post_type] == '_thumbnail_id') {
                    $image = get_post_thumbnail_id($post_id);
                    $image = wp_get_attachment_image_src($image, 'thumbnail');
                } else {
                    $image_id = get_post_meta($post_id, $post_meta[$post_type], true);
                    if (is_array($image_id)) {
                        $image_id = $image_id[0];
                    }
                    $image = wp_get_attachment_image_src($image_id, 'thumbnail');
                }

                if (!empty($image)) {
                    echo '<img src="'.$image[0].'" alt="featured image">';
                } else {
                    echo '<div class="empty-featured-image"></div>';
                }

                break;

            default:
                break;
        }

    }
}
