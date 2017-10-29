<?php

namespace SB;

use SB\Utils;

class Admin
{

    public static function init()
    {

        // add_action('admin_menu', array(__CLASS__, 'removeMenuItems'));
        add_action('admin_print_styles', array(__CLASS__, 'printStyles'));
        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueueScripts'));
        // add_action('admin_init', array(__CLASS__, 'filterEditor'));
        add_action('wp_dashboard_setup', array(__CLASS__, 'removeDashboardWidgets'));
        // add_action( 'admin_init',  array(__CLASS__,'aifi_add_editor_styles' ));

        
        // add_action('admin_init', array(__CLASS__, 'userAddRedirect'));

        // no admin-bar
        // add_filter( 'show_admin_bar', '__return_false' );

    }

    public static function removeMenuItems()
    {

        if (Utils::isAjaxRequest() || !current_user_can('delete_posts')) {
            return;
        }

        remove_submenu_page('edit.php', 'edit-tags.php?taxonomy=post_tag');
        // remove_submenu_page('edit.php', 'edit-tags.php?taxonomy=category');

        remove_menu_page('link-manager.php');
        remove_menu_page('edit-comments.php');

        // Move media-menu under custom post types
        global $menu, $submenu;

        $menu[50] = $menu[70]; //Users
        $menu[40] = $menu[10]; //Media
        unset($menu[10]); //Media
        unset($menu[70]); //Users

        $menu[5][0] = 'Nyheter';
        $submenu['edit.php'][5][0] = 'Nyheter';
        $submenu['edit.php'][10][0] = 'Lägg till nyhet';

        // remove_meta_box('categorydiv', 'post', 'normal');
        remove_meta_box('tagsdiv-post_tag', 'post', 'normal');

        // foreach (array('post', 'page') as $post_type) {
        //  remove_meta_box('slugdiv', $post_type, 'normal');
        // }

    }

    public static function printStyles()
    {

        wp_enqueue_style('thickbox');
        wp_register_style('admin_styling', get_template_directory_uri().'/css/admin.css');
        wp_enqueue_style('admin_styling');

    }

    // public static function aifi_add_editor_styles() {
    //     add_editor_style( get_template_directory_uri().'/css/editor.css');
    // }
    

    public static function enqueueScripts()
    {

        wp_enqueue_media();
        wp_enqueue_script('jquery-ui-sortable');

        wp_register_script('sb-admin', get_template_directory_uri().'/js/admin.js', 'jquery', '2', true);
        wp_enqueue_script('sb-admin');

    }

    public static function filterEditor()
    {

        if (Utils::isAjaxRequest()) {
            return;
        }

        remove_submenu_page('themes.php', 'theme-editor.php'); // Remove link-menu
        remove_menu_page('tools.php');

        $current_user = wp_get_current_user();
        if ($current_user->roles[0] == 'editor') {
            remove_menu_page('themes.php');
            add_menu_page('Annonser', 'Annonser', 'edit_theme_options', 'widgets.php', false, false, 9);
            add_menu_page('Menyer', 'Menyer', 'edit_theme_options', 'nav-menus.php', false, false, 10);

            global $pagenow;
            $disallowed = array('themes.php', 'theme-editor.php');
            if (in_array($pagenow, $disallowed)) {
                wp_redirect('/wp-admin/');
                exit;
            }
        }

    }

    public static function removeDashboardWidgets()
    {

        // remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
        remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');
        remove_meta_box('dashboard_plugins', 'dashboard', 'normal');
        remove_meta_box('dashboard_primary', 'dashboard', 'side');
        remove_meta_box('dashboard_secondary', 'dashboard', 'normal');
        remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
        remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side');
        remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
        remove_meta_box('dashboard_activity', 'dashboard', 'normal');

    }

    public static function userAddRedirect()
    {
        global $pagenow;
        if (Utils::get_string('update') == 'add'
            && $pagenow == 'users.php'
            && is_numeric(Utils::get_string('id'))) {
            wp_redirect(admin_url('/user-edit.php?user_id='.Utils::get_string('id')));
        }
    }
}
