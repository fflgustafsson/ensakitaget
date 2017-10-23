<?php

namespace SB;

use SB\Utils as Utils;
use SB\Forms;
use SB\Forms\Fields;
use SB\Sortable;
use SB\Modules;

class Page
{

    public static function init()
    {

        add_action('add_meta_boxes', array(__CLASS__, 'addMetaBox'));
        add_action('admin_init', array(__CLASS__, 'categorySupport'));

        // This is how you add sorting to post_types
        // A lot more functionality can be reached by
        // more parameters // Christoffer
        Sortable::register(array(
            'post_type' => 'page',
            ));

    }

    public static function categorySupport()
    {

        register_taxonomy_for_object_type('category', 'page');
        add_post_type_support('page', 'category');

    }

    public static function addMetaBox()
    {

    	Utils::addTemplateMetaBox(
            'lasupp_youtube', // id
            'Video', // title
            array(__CLASS__, 'youtube'), // callback function
            'page', // post_type
            'normal', // context 'normal', 'advanced', or 'side'
            'core',
            null,
            array('page-home')
        );

       
        Utils::addTemplateMetaBox(
            'lasupp_slideshow', // id
            'Slideshow', // title
            array(__CLASS__, 'slideshow'), // callback function
            'page', // post_type
            'normal', // context 'normal', 'advanced', or 'side'
            'core',
            null,
            array('page-home')
        );

        Utils::addTemplateMetaBox(
            'lasupp_aboutapp', // id
            'Books', // title
            array(__CLASS__, 'appbook'), // callback function
            'page', // post_type
            'normal', // context 'normal', 'advanced', or 'side'
            'core',
            null,
            array('page-about-app')
        );

    }

    public static function youtube($post)
    {

       echo Fields::text(array(
            'name'  => '_youtube',
            'label' => 'YoutubeID',
            'auto_value' => true
            ));

    }

    public static function slideshow($post)
    {

        echo Fields::multi(array(
            'name' => '_slideshow',
            'auto_value' => true,
            'label' => 'Slideshow',
            'add' => 'Add slide',
            'fields' => array(                
                '_slide_image' => array(
                    'type'          => 'image',
                    'label'         => 'Slide image',
                    'placeholder'   => ''
                ),
                '_slide_desc' => array(
                    'type' => 'text',
                    'label' => 'Description' 
                )


            )));
     

    }

    public static function appbook($post)
    {

        echo Fields::text(array(
            'name'  => '_appbook_title',
            'label' => 'Section title',
            'auto_value' => true
            ));

        echo Fields::multi(array(
            'name' => '_book',
            'auto_value' => true,
            'label' => 'Book',
            'add' => 'Add book',
            'fields' => array(                
                '_book_title' => array(
                    'type'          => 'text',
                    'label'         => 'Title',
                    'placeholder'   => ''
                ),
                '_book_author' => array(
                    'type'          => 'text',
                    'label'         => 'Author',
                    'placeholder'   => ''
                ),
                '_book_publisher' => array(
                    'type'          => 'text',
                    'label'         => 'Publisher',
                    'placeholder'   => ''
                ),
                '_publisher_link' => array(
                    'type'          => 'text',
                    'label'         => 'Publisher link',
                    'placeholder'   => ''
                )

            )));
     

    }

}
