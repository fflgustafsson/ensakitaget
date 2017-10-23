<?php

namespace SB;

use SB\Utils;

class Head
{

    public static $dependencies = array(
        'Utils' => '2.0'
        );

    public static function init()
    {

        add_action('init', array(__CLASS__, 'removeUnused'));
        //add_action('wp_head', array(__CLASS__, 'htmlClassJs'), 2);
        add_action('wp_head', array(__CLASS__, 'opengraph'));
        //add_action('wp_head', array(__CLASS__, 'icons'));

    }

    public static function title($echo = true)
    {

        $title = wp_title(' – ', false, 'right') . get_bloginfo('name');
        if (!$echo) {
            return $title;
        }

        echo $title;

    }

    public static function htmlClass($class = false)
    {

        $default = array('no-touch no-js wf-loading');
        $default[] = $class;

        return implode(' ', $default);

    }

    public static function opengraph()
    {

        $og_variables = array();

        $og_variables['type'] = 'website';
        $og_variables['locale'] = 'sv_se';
        $og_variables['image'] = get_bloginfo('template_url').'';
        $og_variables['title'] = self::title(false);
        $og_variables['url'] = get_bloginfo('url');
        $og_variables['description'] = get_bloginfo('description');

        if (is_single() || is_page() && !Utils::isStartPage()) {
            global $post;
            setup_postdata($post);

            $og_variables['url'] = get_permalink();

            if (has_post_thumbnail()) {
                $image = wp_get_attachment_image_src(get_post_thumbnail_id($post->ID), 'medium');
                $og_variables['image'] = Utils::wpSiteurl().$image[0];
            }

            $og_variables['description'] = get_the_excerpt();

            wp_reset_postdata();

        }

        $og_output = array();

        foreach ($og_variables as $prop => $content) {
            $og_output[$prop] = "\t\t".'<meta property="og:'.$prop.'" content="'.$content.'">'."\n";
        }


        $og_output = apply_filters('sb_og_output', $og_output);
        
        echo '<meta name="description" content="'.$og_variables['description'].'"/>';

        echo implode('', $og_output);       

    }

    public static function icons()
    {

        ?>

        <link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>" />

        <?php

    }

    // syntax OK
    public static function htmlClassJs()
    {
        ?>
<script>Modernizr={touch:!1,init:function(){var b=document.getElementsByTagName("html")[0],a=b.className,a=a.replace("no-js", "js");if(Modernizr.touch="ontouchstart"in window)a=a.replace("no-touch", "touch");b.className=a}};Modernizr.init();</script>
        <?php
    }

    public static function removeUnused()
    {

        // Display the links to the extra feeds such as category feeds
        remove_action('wp_head', 'feed_links_extra', 3);

        // Display the links to the general feeds: Post and Comment Feed
        remove_action('wp_head', 'feed_links', 2);

        // Display the link to the Really Simple Discovery service endpoint, EditURI link
        remove_action('wp_head', 'rsd_link');

        // Display the link to the Windows Live Writer manifest file.
        remove_action('wp_head', 'wlwmanifest_link');

        // index link
        remove_action('wp_head', 'index_rel_link');

        // prev link
        remove_action('wp_head', 'parent_post_rel_link', 10, 0);

        // start link
        remove_action('wp_head', 'start_post_rel_link', 10, 0);

        // Display relational links for the posts adjacent to the current post.
        remove_action('wp_head', 'adjacent_posts_rel_link', 10, 0);

        // Display the XHTML generator that is generated on the wp_head hook, WP version
        remove_action('wp_head', 'wp_generator');

        // Removes hard coded inline style /wp-includes/default-widgets.php @655
        add_filter('show_recent_comments_widget_style', function () {
            return false;
        }, 1);

    }
}
