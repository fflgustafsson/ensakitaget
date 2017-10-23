<?php

//
// Example
// Responsive::register_sizes(
//  array(
//      'hero'      => array(
//          'size'          => array(1024, 768),
//          'breakpoints'   => array('large' => 1024, 'medium' => 768, 'small' => 540),
//          'crop'          => true,
//          )
//      )
//  );
// Explanation:
// key in top array represents the image name
// size represents the maximal image size (without retina)
// breakpoints generates the sizes, according to the 'size' ratio
// crop true means hard crop, i.e the size will endup with the same ratio as 'size'
//

namespace SB\Media;

use SB\Utils;

class Responsive
{

    public static $sizes = array();
    public static $inline_responsive = false;

    public static function init()
    {

        add_action('wp_ajax_get_image', array(__CLASS__, 'image'));
        add_action('wp_ajax_nopriv_get_image', array(__CLASS__, 'image'));
        add_filter('the_content', array(__CLASS__, 'inlineResponsive'));

    }

    public static function registerSizes($args)
    {

        foreach ($args as $base => $data) {
            $crop = ($data['crop']) ? true : false;

            foreach ($data['breakpoints'] as $size => $width) {
                $name = $base.'_'.$size;
                $ratio = $data['size'][1] / $data['size'][0];

                add_image_size($name, $width, ceil($width * $ratio), $crop);
                add_image_size($name.'_retina', ($width * 2), ceil(($width * 2) * $ratio), $crop);

                self::$sizes[$base][$name] = $width;
                self::$sizes[$base][$name.'_retina'] = ($width * 2);

            }

        }

    }

    private static function getSize($width, $size)
    {

        $sizes = self::$sizes[$size];
        asort($sizes);

        foreach ($sizes as $key => $val) {
            $max = max($val, $width);
            if ($max > $width || $width == $val) {
                return $key;
            }

        }

        end($sizes);
        return key($sizes);

    }

    public static function image()
    {

        if (!Utils::is_ajax_request()) {
            return;
        }

        $width = Utils::post_int('width');
        $media = Utils::post_int('media');
        $size = Utils::post_string('size');
        $retina = (Utils::post_string('retina') == 'true') ? true : false;

        // Double width for retina
        $width = ($retina) ? $width * 2 : $width;

        // Get size name
        $size = self::getSize($width, $size);

        $image = wp_get_attachment_image_src($media, $size);
        $alt = get_post_meta($media, '_wp_attachment_image_alt', true);

        Utils::returnJSON(array(
            'tag' => '<img src="'.$image[0].'" alt="'.$alt.'">',
            'src' => $image[0],
            'size' => $size
            ));

    }

    public static function getSizes($image, $size)
    {

        if (empty($image['src'][0])) {
            return false;
        }

        $dir = dirname($image['src'][0]);

        $sizes = self::$sizes[$size];
        asort($sizes);

        $return = array();

        $file = false;

        foreach ($sizes as $name => $width) {
            $file = (!empty($image['meta']['sizes'][$name])) ? $dir.'/'.$image['meta']['sizes'][$name]['file'] : $file;
            $return[$width] = array('src' => $file, 'alt' => $image['meta']['post_metadata']['alt']);
        }

        return json_encode($return, JSON_FORCE_OBJECT);

    }

    public static function inlineResponsive($content)
    {

        if (!self::$inline_responsive) {
            return $content;
        }

        preg_match_all('/<img\sclass="([^"]*)".*?[^>]*>/', $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $image) {
            list($tag, $classes) = $image;

            $classes = explode(' ', $classes);
            foreach ($classes as $class) {
                if (false !== strpos($class, 'wp-image-')) {
                    $media_id = str_replace('wp-image-', '', $class);
                    unset($classes[$class]);
                }
            }

            if (!is_numeric($media_id)) {
                continue;
            }

            $new_tag = $tag;
            $new_tag = apply_filters('SB_media_inline_responsive', $new_tag, $media_id, $classes);

            if ($new_tag != $tag) {
                $content = str_replace($tag, $new_tag, $content);
            }

        }

        return $content;

    }
}
