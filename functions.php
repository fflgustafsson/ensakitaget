<?php

error_reporting(E_ALL);
date_default_timezone_set('Europe/Stockholm');

// Get things rolling
include(TEMPLATEPATH.'/bundles/Utils/2.0/Utils.php');
use SB\Utils;

Utils::init();

// Bundles to load
Utils::load('bundle', 'Forms', '2.0', 'init');
Utils::load('bundle', 'Media', '2.0', 'init');
Utils::load('bundle', 'Sortable', '2.0', 'init');
Utils::load('bundle', 'Rewrite', '2.0');
//Utils::load('bundle', 'Google', '1.0');
//Utils::load('bundle', 'SiteMaps', '1.0');
//Utils::load('bundle', 'Release', '2.0', 'init');
//Utils::load('bundle', 'Modules', '2.0', 'init');
//Utils::load('bundle', 'Mums', '1.0', 'init');

// Debug all loaded bundles
// Utils::debug(Utils::$loaded_bundles);

// Local Classes and bundle extensions to load
// Consider these as templates to be modified
Utils::load('lib', 'Head', null, 'init');
Utils::load('lib', 'Statics', null, 'init');
Utils::load('lib', 'Admin', null, 'init');
Utils::load('lib', 'Media');
Utils::load('lib', 'Utils');
//Utils::load('lib', 'Render');
//Utils::load('lib', 'Rewrite');
//Utils::load('lib', 'Options');
Utils::load('lib', 'Sidebar', null, 'init');
Utils::load('lib', 'Menu', null, 'init');
// Utils::load('lib', 'SiteMaps');
//Utils::load('lib', 'Theme');

// Utils::load('lib', 'Google'); // Template
// Utils::load('lib', 'LoadMore'); // Example
// Utils::load('lib', 'Modules'); // Examples

// Additions to base post_types and custom post_types to load
// Consider these as templates to be modified
// Utils::load('post_type', 'Post', null, 'init'); // Examples
Utils::load('post_type', 'Page', null, 'init'); // Examples

// Track all filters/actions to error_log
// Utils::tracker();

// Debug all Forms to error_log
// use SB\Forms;
// Forms::debug();

// Get CsookieDisclaimer going
//use SB\Mums\CookieDisclaimer;
//use SB\LocalUtils;

//CookieDisclaimer::register();


add_action('init', 'html5wp_pagination'); // Add our HTML5 Pagination

// Pagination for paged posts, Page 1, Page 2, Page 3, with Next and Previous Links, No plugin
function html5wp_pagination()
{
    global $wp_query;
    $big = 999999999;
    echo paginate_links(array(
        'base' => str_replace($big, '%#%', get_pagenum_link($big)),
        'format' => '?paged=%#%',
        'current' => max(1, get_query_var('paged')),
        'total' => $wp_query->max_num_pages
    ));
}

add_theme_support( 'post-thumbnails' );
set_post_thumbnail_size( 300, 300, array( 'center', 'center')  ); 

add_action('init', 'create_post_type_services'); // Add our HTML5 Blank Custom Post Type
function create_post_type_services()
{
    register_post_type('services', // Register Custom Post Type
        array(
        'labels' => array(
            'name' => __('Tjänster', 'services'), // Rename these to suit
            'singular_name' => __('Tjänst', 'services'),
            'add_new' => __('Lägg till', 'services'),
            'add_new_item' => __('Lägg till nytt', 'services'),
            'edit' => __('Ändra', 'services'),
            'edit_item' => __('Ändra tjänst', 'services'),
            'new_item' => __('Ny tjänst', 'services'),
            'view' => __('Visa', 'services'),
            'view_item' => __('Visa tjänst', 'services'),
            'search_items' => __('Sök tjänst', 'services'),
            'not_found' => __('Ingen tjänst funnen', 'services'),
            'not_found_in_trash' => __('Ingen tjänst i papperskorgen', 'services')
        ),
        'public' => true,
        'hierarchical' => true, // Allows your posts to behave like Hierarchy Pages
        'has_archive' => false,
        'supports' => array(
            'title',
            'editor',
            'excerpt',
            'thumbnail',
            'custom-fields'
        ), // Go to Dashboard Custom HTML5 Blank post for supports
        'can_export' => true, // Allows export in Tools > Export
        'taxonomies'  => array(),
        'menu_position' => 5
    ));
}

