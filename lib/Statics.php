<?php

namespace SB;

use \SB\Utils;

class Statics
{

    public static $version = 0.72;

    public static function init()
    {

        // Use only if supporting IE8
        // add_action('wp_head', array(__CLASS__, 'html5shiv'), 1);

        // add_action('wp_head', array(__CLASS__, 'favicon'));
        //add_action('wp_head', array(__CLASS__, 'fontloader'));

        // Use only if supporting IE8
        // add_action('wp_head', array(__CLASS__, 'ieResponsive'));

        add_action('wp_enqueue_scripts', array(__CLASS__, 'stylesheet'));
        add_action('wp_enqueue_scripts', array(__CLASS__, 'javascript'));
        //add_action('wp_footer', array(__CLASS__, 'googleAnalytics'));

    }

    public static function javascript()
    {

        wp_deregister_script('jquery');

        $deps = array('jquery'); // order of scripts

        wp_register_script('jquery', get_template_directory_uri().'/js/jquery-2.1.4.min.js', 'jquery', '2.1.3', true);
        wp_register_script('fastclick', get_template_directory_uri().'/js/fastclick.js', 'jquery', '1', true);
        wp_register_script('scripts', get_template_directory_uri().'/js/scripts.js', $deps, self::$version, true);

        wp_enqueue_script('jquery');
        wp_enqueue_script('fastclick');
        wp_enqueue_script('scripts');

        // Only included as template, should be copied and moved to common.js
        // wp_register_script('sb-responsive', Utils::getBundleUri('Media').'/js/responsive-template.js', $deps, self::$version, true);
        // wp_enqueue_script('sb-responsive');

        // Only included as template, should be copied and moved to common.js
        // wp_register_script('sb-load-more',  Utils::getBundleUri('Utils').'/1.0/js/load-more-template.js', $deps, self::$version, true);
        // wp_enqueue_script('sb-load-more');

        // Add ajax path to front end
        // wp_localize_script('sb-common', 'data', array(
        //  'ajaxurl' => admin_url('admin-ajax.php'),
        //  ));

    }

    public static function stylesheet()
    {

        wp_register_style('sb-style', get_bloginfo('stylesheet_url'), false, self::$version, 'all');
        wp_enqueue_style('sb-style');

    }

    public static function fontLoader()
    {

        // https://github.com/typekit/webfontloader
        ?>
        <script type="text/javascript">

            WebFontConfig = {
                custom: {
                    families: ['FuturaBT-Light'],
                    urls: ['<?php echo get_bloginfo('template_url'); ?>/css/fonts.css']
                },
                custom: {
                    families: ['FuturaBT-Bold'],
                    urls: ['<?php echo get_bloginfo('template_url'); ?>/css/fonts.css']
                },/*
                custom: {
                    families: ['socicon'],
                    urls: ['<?php echo get_bloginfo('template_url'); ?>/css/fonts.css']
                },*/
                timeout: 3000
            };

            (function() {
                var wf = document.createElement('script');
                wf.src = ('https:' == document.location.protocol ? 'https' : 'http') +
                '://ajax.googleapis.com/ajax/libs/webfont/1.5.18/webfont.js';
                wf.type = 'text/javascript';
                wf.async = 'true';
                var s = document.getElementsByTagName('script')[0];
                s.parentNode.insertBefore(wf, s);
            })();

        </script>
        <?php

    }

    public static function html5shiv()
    {
        ?>
    <!--[if lt IE 9]>
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <script src="<?php bloginfo('template_url'); ?>/js/html5shiv.js"></script>
    <![endif]-->
        <?php
    }

    public static function ieResponsive()
    {
        ?>
<!--[if lt IE 9]>
            <script src="<?php bloginfo('template_url'); ?>/js/respond.min.js"></script>
        <![endif]-->
        <?php
    }

    public static function googleAnalytics()
    {

        $google_analytics_id = 'UA-70234631-1';

        ?>
        <script type="text/javascript">

            (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
            })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

            ga('create', '<?php echo $google_analytics_id ?>', 'auto');
            ga('send', 'pageview');

        </script>
        <?php

    }

}