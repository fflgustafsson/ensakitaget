<?php

// Examples
namespace SB;


class Menu
{

    public static function init()
    {

        // Actions
        add_action('init', array(__CLASS__, 'addMenu'));

        // Filters
        // add_filter('wp_nav_menu_objects', array(__CLASS__, 'addClassToItems'));
        // add_filter('wp_nav_menu_items', array(__CLASS__, 'addSpecialItem'), 10, 2);

    }

    public static function addMenu()
    {
        register_nav_menus(array(
            'main_menu'     => 'Main-menu'
        ));
    }

    public static function addClassToItems($items)
    {
        foreach ($items as $id => $data) {
            $page = get_post($data->object_id);
            if ($page) {
                $data->classes[] = 'page-'.$page->post_name;
            }
        }
        return $items;
    }

    public static function addSpecialItem($items, $args)
    {
        if ($args->menu_class == 'mobile-menu') {
            $items .= '<li><a href="#">Special</a></li>';
        }
        return $items;
    }
}
