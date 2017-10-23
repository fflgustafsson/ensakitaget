<?php

// SBTheme::init();

class SBTheme {

    public static function init()
    {

        global $pagenow;
        if (is_admin() && 'themes.php' == $pagenow && isset($_GET['activated'])) {

            // run functions here
            // add_action('admin_init', array(__CLASS__, 'set_permalink_structure'));
            // self::set_options();

        }

    }

    public static function setOptions()
    {

        // update_option('thumbnail_size_h', '100');
        // update_option('thumbnail_size_w', '100');
        // update_option('medium_size_h', '0');
        // update_option('medium_size_w', '220');
        // update_option('large_size_h', '0');
        // update_option('large_size_w', '640');
        // update_option('embed_size_h', '0');
        // update_option('embed_size_w', '640');

        // $start = get_page_by_title('Start');
        // update_option('page_on_front',$start->ID);

        // $template_page = get_page_by_title('template_page');
        // update_post_meta($template_page->ID, '_wp_page_template', 'template.php');

        // update_option('blogname', 'Tema');
        // update_option('show_on_front','page');

    }

    public static function createPages()
    {

        $the_content = 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.';
        $the_pages = array();

        // foreach ($the_pages as $page => $content) {
        //  if (false == doesPageExist($page)) {
        //      $new_page = array(
        //          'post_title' => $page,
        //          'post_content' => $the_content,
        //          'post_status' => 'publish',
        //          'post_type' => 'page',
        //          'post_author' => 1,
        //          );
        //      $post_id = wp_insert_post($new_page);
        //  }
        //  if (!empty($content[0])) {
        //      foreach ($content[0] as $page) {
        //          if (false == doesPageExist($page)) {
        //              $new_page = array(
        //                  'post_title' => $page,
        //                  'post_content' => $the_content,
        //                  'post_status' => 'publish',
        //                  'post_type' => 'page',
        //                  'post_author' => 1,
        //                  'post_parent' => $post_id,
        //              );
        //              $sub_page_id = wp_insert_post($new_page);
        //              update_post_meta($sub_page_id, '_wh_lang', $content[1]);
        //          }
        //      }
        //  }
        // }

    }

    public static function createMenues()
    {

        // $menu_exists = wp_get_nav_menu_object($menu_name);
        //  if (!$menu_exists) {
        //      $menu_id = wp_create_nav_menu($menu_name);
        //      $menu_pos = 1;
        //      foreach ($the_pages as $page => $content) {
        //          if ($content[1] != $lang) continue;

        //          $page = get_page_by_title($page, 'OBJECT', 'page');

        //          $post_title = $page->post_title;
        //          if ($page->post_title == 'Start (en)')
        //              $post_title = 'Start';

        //          $parent_id = wp_update_nav_menu_item($menu_id, 0, array(
        //              'menu-item-object-id' =>  $page->ID,
        //              'menu-item-parent-id' => 0,
        //              'menu-item-position' => $menu_pos++,
        //              'menu-item-object' => 'page',
        //              'menu-item-type' => 'post_type',
        //              'menu-item-status' => 'publish',
        //              'menu-item-title' => $post_title)
        //          );
        //          if (!empty($content[0])) {
        //              foreach ($content[0] as $sub_page) {
        //                  $page = get_page_by_title($sub_page, 'OBJECT', 'page');
        //                  wp_update_nav_menu_item($menu_id, 0, array(
        //                      'menu-item-object-id' =>  $page->ID,
        //                      'menu-item-parent-id' => 0,
        //                      'menu-item-position' => $menu_pos++,
        //                      'menu-item-object' => 'page',
        //                      'menu-item-type' => 'post_type',
        //                      'menu-item-status' => 'publish',
        //                      'menu-item-parent-id' => $parent_id)
        //                  );
        //              }
        //          }
        //      }
        //  }
        //  if (!has_nav_menu ('huvudmeny_'.$lang)){
        //      global $wpdb;
        //      $menu_id = $wpdb->get_var($wpdb->prepare("SELECT term_id FROM $wpdb->terms WHERE name = 'Huvudmeny ".$lang."';"));
        //      $locations = get_theme_mod('nav_menu_locations');
        //      $locations['huvudmeny_'.$lang] = $menu_id;
        //      set_theme_mod('nav_menu_locations', $locations);
        //  }
        // }
    }

    public static function doesPageExist($page_title)
    {
        $check = get_page_by_title($page_title, 'OBJECT', 'page');
        if ($check) {
            return true;
        }
        return false;
    }
}
