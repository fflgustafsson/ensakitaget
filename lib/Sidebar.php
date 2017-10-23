<?php

namespace SB;

    class Sidebar{

        public static function init(){
            add_action('widgets_init', array(__CLASS__, 'unregisterWidgets'));
            add_action('init', array(__CLASS__, 'registerSidebars'));
            add_action('init', array(__CLASS__, 'registerFooter'));
        }

        public static function unregisterWidgets()
        {
            unregister_widget('WP_Widget_Pages');
            unregister_widget('WP_Widget_Calendar');
            unregister_widget('WP_Widget_Archives');
            // unregister_widget('WP_Widget_Links');
            unregister_widget('WP_Widget_Categories');
            //unregister_widget('WP_Widget_Recent_Posts');
            //unregister_widget('WP_Widget_Search');
            unregister_widget('WP_Widget_Tag_Cloud');
            unregister_widget('WP_Widget_RSS');
            // unregister_widget('WP_Widget_Text');
            unregister_widget('WP_Widget_Meta');
            unregister_widget('WP_Widget_Recent_Comments');
            unregister_widget('mypageorder_Widget');
            unregister_widget('mycategoryorder_Widget');
            unregister_widget('WP_Nav_Menu_Widget');
        }

        public static function registerSidebars()
        {
            register_sidebar(array(
                'id' => 'sidebar',
                'name' => 'Sidebar',
                'before_widget' => '',
                'after_widget' => '',
                'before_title' => '',
                'after_title' => '',
            ));
        }

        public static function registerFooter()
        {
            register_sidebar(array(
                'id' => 'socialfooter',
                'name' => 'Socialfooter',
                'before_widget' => '',
                'after_widget' => '',
                'before_title' => '',
                'after_title' => '',
            ));
        }

    }

    //include get_template_directory().'/widgets/example.php';
