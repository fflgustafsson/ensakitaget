<?php

namespace SB;

/*
Examples:

SB\Rewrite::addRule(array(
    'uri' => 'who-we-are/?',
    'template' => 'whoweare',
    'page'  => array(
        'title'     => 'Who we are',
        'body_class'    => 'who-we-are'
        )
));

SB\Rewrite::addRule(array(
    'uri' => 'bullo/([0-9]{4})/([^/]*)/?',
    'template' => 'listor-a-till-o',
    'path' => 'index.php?page=tomte&yo=$matches[1]&mama=$matches[2]',
));

*/

class Rewrite
{

    public static $dependencies = array();

    public static $rewrite_tags = array();
    public static $rewrite_rules = array();
    public static $page_data = array();

    public static function init()
    {
        self::doRewriteTags();
        self::doRewrites();
        add_filter('template_include', array(__CLASS__, 'templateInclude'));
    }

    public static function addRewriteRule($args)
    {
        error_log('Deprecated:: addRewriteRule, use addRule');
        return self::addRule($args);
    }

    public static function addRule($args)
    {
        $defaults = array(
            'uri' => '',
            'path' => '',
            'page' => '',
            'template' => '',
            'is_archive' => false,
            'after' => 'bottom' // This can either be 'top' or 'bottom'. 'top' will take precedence over
            // WordPress's existing rules, where 'bottom' will check all other rules match first. Default: "bottom"
        );
        $args = wp_parse_args($args, $defaults);

        if (empty($args['path'])) {
            $args['path'] = 'index.php?template='.$args['template'];

        }

        self::$rewrite_rules[] = $args;

    }

    public static function addTag($tag)
    {
        self::$rewrite_tags[$tag] = '([^&]+)';
    }

    private static function doRewrites()
    {
        foreach (self::$rewrite_rules as $rewrite_rule) {
            \add_rewrite_rule(
                $rewrite_rule['uri'],
                $rewrite_rule['path'],
                $rewrite_rule['after']
            );
        }
    }


    /**
     * Extracts tags from rewrite_rule['path']
     */
    private static function doRewriteTags()
    {
        foreach (self::$rewrite_tags as $tag => $reg) {
            \add_rewrite_tag("%$tag%", '([^&]+)');
        }
    }

    /**
     * Find template for rewrite and output
     */
    public static function templateInclude($template)
    {

        global $wp, $wp_query;

        // Try to match matched rule to rewrite_rule['uri']
        if (!empty($wp->matched_rule)) {
            foreach (self::$rewrite_rules as $rewrite_rule) {
                if ($rewrite_rule['uri'] == $wp->matched_rule && !empty($rewrite_rule['template'])) {
                    $new_template = locate_template(array($rewrite_rule['template'].'.php'));

                    // manipulate title and body
                    if (!empty($rewrite_rule['page'])) {
                        self::$page_data = $rewrite_rule;
                        add_filter('wp_title', array(__CLASS__, 'wpTitle'), 10, 3);
                        add_filter('body_class', array(__CLASS__, 'bodyClass'));
                    }

                    if (!empty($new_template)) {
                        if ($rewrite_rule['is_archive']) {
                            $wp_query->is_archive = true;
                            $wp_query->is_post_type_archive = true;
                        }
                        return $new_template;
                    }
                }
            }
        }

        return $template;
    }

    /**
     * Manipulate wp_title with page data
     */
    public static function wpTitle($title, $sep, $seplocation)
    {
        if (empty(self::$page_data['page']['title'])) {
            return $title;
        }
        return self::$page_data['page']['title'].$sep;
    }

    /**
     * Manipulate body_class with page data
     */
    public static function bodyClass($classes)
    {

        $remove = array('home', 'blog');
        foreach ($classes as $key => $value) {
            if (in_array($value, $remove)) {
                unset($classes[$key]);
            }
        }

        $template_class = str_replace('_', '-', strtolower(self::$page_data['template']));
        $page_classes = array('page', 'page-template-'.$template_class);

        if (!empty(self::$page_data['page']['body_class'])) {
            $page_classes[] = self::$page_data['page']['body_class'];
        }

        $classes = array_merge($classes, $page_classes);

        return $classes;

    }

    public static function customTemplate()
    {

        if (empty(self::$page_data['template'])) {
            return false;
        }

        return self::$page_data['template'];

    }

    public static function isCustomTemplate($template)
    {

        if (empty(self::$page_data['template'])) {
            return false;
        }

        if ($template == self::$page_data['template']) {
            return true;
        }

        return false;
    }
}
