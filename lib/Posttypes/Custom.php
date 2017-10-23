<?php

namespace SB;

use SB\Forms;
use SB\Forms\Fields;

class Custom
{

    public static $data = array(

        'name'      => 'Custom',
        'singular'  => 'custom',
        'slug'      => 'custom'

        );


    public static function init()
    {

        add_action('init', array(__CLASS__, 'register'));

    }

    public static function register()
    {

        $labels = array(
            'name'               => self::$data['name'], // general name for the post type, usually plural.
            'singular_name'      => self::$data['singular'], // name for one object of this post type.
            'menu_name'          => self::$data['name'], // the menu name text. This string is the name to give menu items.
            'name_admin_bar'     => self::$data['name'], // name given for the "Add New" dropdown on admin bar.
            'all_items'          => self::$data['name'], // the all items text used in the menu.
            'add_new'            => 'Lägg till '.self::$data['singular'], // the add new text. The default is "Add New" for both hierarchical and non-hierarchical post types.
            'add_new_item'       => 'Lägg till '.self::$data['singular'], // the add new item text.
            'edit_item'          => 'Redigera '.self::$data['singular'], // the edit item text. In the UI, this label is used as the main header on the post's editing panel.
            'new_item'           => 'Ny '.self::$data['singular'], // the new item text.
            'view_item'          => 'Visa '.self::$data['singular'], // the view item text.
            'search_items'       => 'Sök '.self::$data['singular'], // the search items text.
            'not_found'          => 'Inga '.self::$data['singular'], // the not found text.
            'not_found_in_trash' => 'Inga '.self::$data['singular'].' i papperskorgen', // the not found in trash text.
            'parent_item_colon'  => 'Förälder', // the parent text.

        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'exclude_from_search'=> false,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_nav_menus'  => true,
            'show_in_menu'       => true,
            'show_in_admin_bar'  => true,
            'menu_position'      => 6,
            'menu_icon'          => 'dashicons-admin-comments', // melchoyce.github.io/dashicons/
            'capability_type'    => 'page',
            'capabilities'       => array(),
            'map_meta_cap'       => true,
            'hierarchical'       => false,
            'supports'           => array('title'), // default fields
            'register_meta_box_cb'  => array(__CLASS__, 'metabox'),
            // 'taxonomies'      => array(),
            'has_archive'        => false,
            'rewrite'            => true,
            'query_var'          => false,
            'can_export'         => false,

        );

        // Supports
        // 'title', 'editor' (content), 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'page-attributes', 'post-formats'

        // http://codex.wordpress.org/Function_Reference/register_post_type
        register_post_type(self::$data['slug'], $args);

    }

    public static function metabox()
    {
        add_meta_box('metabox', 'Innehåll', array(__CLASS__, 'form'), self::$data['slug'], 'normal', 'core');
    }

    public static function form($post)
    {

        echo Fields::text(array(
            'name'  => 'email',
            'label' => 'Email till någon',
            'auto_value' => true
            ));

        echo Fields::textarea(array(
            'name'  => 'textarea',
            'label' => 'Text till expedition',
            'auto_value' => true,
            'html' => true,
            'rows' => 10
            ));

        echo Fields::select(array(
            'name'  => 'select',
            'label' => 'Ett av flera val',
            'auto_value' => true,
            'data' => array(
                'lit'   => 'Liten',
                'sto'   => 'Stor',
                'med'   => 'Medium'
                ),
            'add_empty' => 'Välj...'
            ));

        echo Fields::checkbox(array(
            'name'  => 'check1',
            'label' => 'Visa stort',
            'auto_value' => true,
            ));

        echo Fields::radio(array(
            'name' => 'lingo',
            'label' => 'Språk',
            'data' => array(
                'sv' => 'Svenska',
                'en' => 'Engelska',
                'tjocko' => 'Tjocko'
            ),
            'default'       => 'en',
            'auto_value' => true,
        ));

        echo Fields::editor(array(
            'name'  => 'ingress',
            'label' => 'Ingress',
            'auto_value' => true,
            'textarea_rows' => 3,
            'media_buttons' => false
            ));

        echo Fields::number(array(
            'name'  => 'numberof',
            'label' => 'Ett nummer mellan nåt',
            'auto_value' => true,
            'min'   => 0,
            'max'   => 100,
            'step'  => 5
            ));

        echo Fields::date(array(
            'name' => 'date1',
            'label' => 'Något är giltig till',
            'description' => 'Fyll i ett datum',
            'maxDate' => '+1m',
            'auto_value' => true,
            ));

        echo Fields::color(array(
            'name'  => 'color1',
            'label' => 'Min färg',
            'auto_value' => true,
            'default' => '#DDEE00',
            'palettes' => array('#ddd', '#ef0', '#333', '#560', '#f30')
            ));

        echo Fields::url(array(
            'name'  => 'myfancyurl',
            'label' => 'Länk iväg nånstans',
            'auto_value' => true,
            ));

        echo Fields::url(array(
            'name'  => 'myfancyurl2',
            'label' => 'Länk någonannanstans',
            'auto_value' => true,
            ));

        echo Fields::image(array(
            'name'  => 'image1',
            'label' => 'Utvald bild',
            'image_size' => 'thumbnail',
            'auto_value' => true,
            'multiple' => true,
            ));

        echo Fields::posts(array(
            'name'  => 'posts',
            'label' => 'Utvalda artiklar',
            'auto_value' => true,
            'multiple' => true,
            'post_type' => array('post', 'page'),
            'max_number' => 3
            ));

        echo Fields::multi(array(
            'name' => '_pagefiles',
            'auto_value' => true,
            'label' => 'Filer',
            'add' => 'Lägg till fil',
            'fields' => array(

                '_thumb' => array(
                    'type'          => 'image',
                    'label'         => 'Tumnagel',
                    // 'multiple'       => true,
                ),
                '_file' => array(
                    'type'          => 'image',
                    'label'         => 'Länkad fil',
                    // 'multiple'       => true,
                ),
                '_headline' => array(
                    'type'          => 'text',
                    'label'         => 'Länktitel',
                    'default'       => 'Text'
                )
            )));
    }
}
