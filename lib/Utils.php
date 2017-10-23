<?php

namespace SB;

use SB\Utils;

LocalUtils::init();

class LocalUtils extends Utils
{

    public static function init()
    {

        $default_path = str_replace('wordpress', '', $_SERVER['DOCUMENT_ROOT']);

        // Declare all development urls
        self::$dev_domains = array(
            self::wpSiteurl(),
            // 'http://sticky.dev',
            );

        // Declare possible content paths, no trailing slash on real webroots
        self::$content_paths = array(
            $default_path,
            // '/public_html/sticky'
            );

        // Add copy function
        self::registerCopy();

        // Add Load More function
        // Post query via ajax // Christoffer
        // self::registerLoadMore(array(

        //  'article' => array(
        //      'posts_per_page' => 3,
        //      'post_type' => 'post',
        //      'subsets' => true,
        //      ),

        //  'homepage' => array(
        //      'posts_per_page' => 4,
        //      'post_type' => 'post',
        //      'meta_key' => '_selected',
        //      'orderby' => 'meta_value_num',
        //      'order' => 'ASC',
        //      'subsets' => true,
        //      ),

        //  'editor' => array(
        //      'posts_per_page' => 3,
        //      'post_type' => 'post',
        //      'meta_key' => 'email',
        //      'subsets' => false,
        //      ),

        //  'category' => array(
        //      'posts_per_page' => 3,
        //      'post_type' => 'post',
        //      'category_name' => 'kanelbullar',
        //      // 'subsets' => true,
        //      )

        //  ));

        // Actions

        // Filters

        // NOTE: This functions does not work on modified upload paths.
        add_filter('wp_get_attachment_url', array(__CLASS__, 'removePerm'));

        // add_filter('body_class', array(__CLASS__, 'post_name_as_class'));
        // add_filter('post_class', array(__CLASS__, 'post_name_as_class'));

    }
}
