<?php

namespace SB;

use SB\Utils;
use SB\Language;
use SB\Forms\Fields;

class Disqus
{

    public static $settings = array(
        'short_name' => false,
        'option_name' => false,
        'add_option' => false,
        'default_active' => false,
        'post_type' => array()
        );

    public static $dependencies = array(
        'Utils' => '2.0',
        );

    public static function register($args)
    {

        self::$settings = wp_parse_args($args, self::$settings);

        if (!empty(self::$settings['option_name'])) {
            self::$settings['short_name'] = get_option(self::$settings['option_name']);
        }

        if (!empty(self::$settings['add_option'])) {
            throw new \Exception('Function not implemented in this version.');
            // FIXME implement add_option to add an options page where admin
            // can edit settings
        }

        if (empty(self::$settings['short_name'])) {
            throw new \Exception('You need to register a short_name');
        }

        add_action('wp_footer', array(__CLASS__, 'script'));
        add_action('add_meta_boxes', array(__CLASS__, 'addMetabox'));

        if (self::$settings['default_active']) {
            add_action('wp_insert_post', array(__CLASS__, 'disqusDefault'), 10, 2);
        }

    }

    public static function render($classes = array())
    {

        global $post;

        if (!get_post_meta($post->ID, '_disqus', true)) {
            return;
        }

        $classes[] = 'disqus-wrapper';
        echo '<div class="'.implode(' ', $classes).'" id="disqus_thread">0</div>';

    }

    public static function isEnabled()
    {

        global $post;
        return get_post_meta($post->ID, '_disqus', true);

    }

    public static function addMetabox()
    {

        if (empty(self::$settings['post_type'])) {
            return;
        }

        foreach (self::$settings['post_type'] as $post_type) {
            add_meta_box(
                'sb_disqus',
                'Disqus',
                array(__CLASS__, 'disqusMetabox'),
                $post_type,
                'side',
                'core'
            );
        }

    }

    public static function disqusDefault($post_id, $post)
    {

        if ($post->post_status == 'auto-draft') {
            update_post_meta($post_id, '_disqus', true);
        }

    }

    public static function disqusMetabox()
    {

        echo Fields::checkbox(array(
            'name'          => '_disqus',
            'label'         => 'Tillåt kommentarer',
            'auto_value'    => true,
        ));

    }

    public static function script()
    {

        global $post;

        $variables = array();

        $post = apply_filters('SB_Disqus_script_post', $post);

        if (!is_object($post)) {
            return false;
        }

        if (!self::isEnabled()) {
            return false;
        }

        $shortname = self::$settings['short_name'];
        $identifier = $post->ID;
        $title = $post->post_title;
        $url = get_permalink($post->ID);
        $category_id = false; // FIXME

        $languages = array(
            'sv' => 'sv_SE',
            'en' => 'en'
            );

        $langcode = $languages[Language::lang()];

        ?>
<script type="text/javascript">

var disqus_shortname = '<?php echo $shortname; ?>',
    disqus_identifier = '<?php echo $identifier; ?>',
    disqus_title = '<?php echo addslashes($title); ?>',
    disqus_url = '<?php echo $url; ?>',
    disqus_category_id = '<?php echo $category_id; ?>',
    disqus_config = function () {
        "use strict";
        this.language = '<?php echo $langcode ?>';
    };
(function () {
    "use strict";
    var dsq = document.createElement('script');
    dsq.type = 'text/javascript';
    dsq.async = true;
    dsq.src = '//' + disqus_shortname + '.disqus.com/embed.js';
    (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(dsq);

    var s = document.createElement('script');
    s.async = true;
    s.type = 'text/javascript';
    s.src = '//' + disqus_shortname + '.disqus.com/count.js';
    (document.getElementsByTagName('head')[0] || document.getElementsByTagName('body')[0]).appendChild(s);

}());
</script>
        <?php
    }
}
