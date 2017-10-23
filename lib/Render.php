<?php

namespace SB;

use SB\LocalLanguage;
use SB\Forms\Common;
use SB\Forms\Multi;
use SB\Media;
use SB\Media\Responsive;
use SB\Utils\Wordpress;
use \Mexitek\PHPColors\Color;

class Render
{

    public static function init()
    {

        add_filter('SB_media_inline_responsive', array(__CLASS__, 'inlineImages'), 10, 3);
        add_filter('SB_Load_More', array(__CLASS__, 'loadMore'), 10, 2);

    }

    // FIXME mucke-much... will probably never use post
    public static function image($media_id, $size = 'large', $post = false, $class = false, $echo = true, $element = 'figure')
    {

        if (empty($media_id)) {
            return false;
        }

        $classes = array('responsive-image', $size, $class);
        $missing = false;
        $object = array();

        $image = Media::get($media_id);
        $no_script_image = wp_get_attachment_image_src($media_id, 'large');
        $no_script_image = (!empty($no_script_image[0])) ? $no_script_image[0] : false;

        if (empty($image['src'])) {
            Utils::debug('NOTICE: post object missing media '.$post->ID);
            $classes = array('missing-image');
            $missing = true;
        }

        $media = Responsive::getSizes($image, $size);

        // http://schema.org/ImageObject
        $object[] = '<'.$element.' class="'.implode(' ', $classes).'" '.$missing.' data-media="'.esc_attr($media).'" itemscope itemtype="http://schema.org/ImageObject">';

        if (!$missing) {
            $object[] = '<meta itemprop="name" content="'.$image['meta']['post_metadata']['title'].'" />';
            $object[] = '<meta itemprop="width" content="'.$image['src'][1].'" />';
            $object[] = '<meta itemprop="height" content="'.$image['src'][2].'" />';
            $object[] = '<meta itemprop="caption" content="'.$image['meta']['post_metadata']['description'].'" />';
            $object[] = '<meta itemprop="contentUrl" content="'.$image['src'][0].'" />';
        }

        // noscript
        $object[] = '<noscript><img style="display: block !important;" src="'.$no_script_image.'" alt="'.$image['meta']['post_metadata']['alt'].'"></noscript>';

        $object[] = '</'.$element.'>';

        if ($echo) {
            echo implode("\n", $object);
        } else {
            return implode("\n", $object);
        }

    }    
}
