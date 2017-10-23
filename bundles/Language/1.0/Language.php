<?php

namespace SB;

use SB\Utils;
use SB\Forms\Fields;

class Language
{

    public static $dependencies = array(
        'Utils' => '2.0',
        'Forms' => '2.0'
        );

    /**
     * An array with variables used while parsing uri.
     */
    private static $vars = array(
        'ignore_lang' => false,
        'url_lang' => null,
    );

    /**
     * Languages in an array('en' => 'Engelska')
     */
    private static $languages = array();

    /**
     * Sets the default language as the code.
     */
    private static $default_language = null;

    /**
     * Array with post_types that uses the langbox.
     */
    private static $langbox_for_post_type = array();

    /**
     * Should modify permlinks and page_num_links
     */
    public static $should_modify_permlinks = false;

    public static $modify_pre_get_posts = false;

    public static $strings = array();

    public static $ignore_posttypes = array();

    public static $meta_key = '_sb_lang';

    public static $add_mx_lang = false;

    public static $post_column_name = 'Språk';

    // Mucke-muck to be changed maybe
    public static $get_lang_from_domain = false;
    public static $domain_mapping = array();

    public static $en_flag = 'GB';

    public static $is_queries = array(
        'is_home',
        'is_single',
        'is_page',
        'is_category',
        'is_feed',
        'is_archive',
    );

    public static function init()
    {

        if (!empty(self::$langbox_for_post_type)) {
            add_action('add_meta_boxes', array(__CLASS__, 'addMetabox'));
            add_action('save_post', array(__CLASS__, 'saveLangMeta'), 10, 2);
            self::addColumns();

        }

        if (self::$should_modify_permlinks) {
            add_filter('post_link', array(__CLASS__, 'modifyLinks'), 10, 3);
            add_filter('page_link', array(__CLASS__, 'modifyLinks'), 10, 3);
            add_filter('post_type_link', array(__CLASS__, 'modifyCustompostLink'), 10, 3);
            add_filter('get_pagenum_link', array(__CLASS__, 'modifyPageNumLink'));
            add_filter('post_type_archive_link', array(__CLASS__, 'modifyArchiveLink'));
            add_filter('menu_item_uri_link', array(__CLASS__, 'modifyNavitemUriLink'));

        }

        if (self::$modify_pre_get_posts) {
            add_filter('pre_get_posts', array(__CLASS__, 'filterQuery'));
        }

        if (self::$get_lang_from_domain) {
            self::setLangFromDomain();
        }

        if (!empty(self::$strings)) {
            require_once('lib/Strings.php');
            Language\Strings::init();
        }

    }

    /**
     * Add language to available languages
     */
    public static function register($language, $name, $default = false, $domain_mapping = array())
    {
        if ($language == 'mx') { // prevent adding of reserved meta_key
            error_log('ERROR language MX reserved');
            die();
        }

        self::$languages[$language] = $name;
        if ($default) {
            self::$default_language = $language;
        }

        self::$domain_mapping[$language] = $domain_mapping;

    }

    /**
     * Add string-code for translation
     */
    public static function addString($code, $string, $language = false)
    {
        if (!$language) {
            $language = self::$default_language;
        }
        self::$strings[$code][$language] = $string;
    }

    /**
     *
     */
    public static function addMetaForPosttype($post_type)
    {
        self::$langbox_for_post_type[] = $post_type;
    }

    public static function filterQuery($query)
    {
        $do_check = false;
        foreach (self::$is_queries as $is) {
            if ($query->$is) {
                $do_check = true;
            }
        }

        if (self::$vars['ignore_lang'] || empty(self::$vars['url_lang'])) {
            return $query;
        }

        if (!$do_check) {
            return $query;
        }

        if ($query->is_attachment) {
            return $query;
        }

        //Maybe check above post_types if they exist in $query->query_vars?
        if (!empty($query->query_vars['post_type'])) {
            $post_type = $query->query_vars['post_type'];

            if ('nav_menu_item' == $post_type) {
                return $query;
            }
            if (in_array($post_type, self::$ignore_posttypes)) {
                return $query;
            }

        }

        //Maybe check above post_types if they exist in $query->queried_object->post_type?
        if (isset($query->queried_object) &&
            isset($query->queried_object->post_type) &&
            'attachment' == $query->queried_object->post_type) {
            return $query;
        }

        $query->set('meta_key', self::$meta_key); // FIXED changed metakey

        // check for mx, change meta_value
        $query_lang = (self::$add_mx_lang) ? array(self::$vars['url_lang'], 'mx') : self::$vars['url_lang'];
        $query->set('meta_value', $query_lang); // FIXED QUERY

        return $query;
    }


    public static function addMetabox()
    {

        foreach (self::$langbox_for_post_type as $post_type) {
            add_meta_box(
                'sb_lang_metabox',
                __('Språk'),
                array(__CLASS__, 'langMetabox'),
                $post_type,
                'side',
                'core'
            );
        }

    }

    public static function getFlag($code)
    {

        $flag_code = ($code == 'en') ? Language::$en_flag : $code;
        if (file_exists(Utils::getBundlePath('Language').'/flags/'.strtoupper($flag_code).'.png')) {
            $src = Utils::getBundleUri('Language').'/flags/'.strtoupper($flag_code).'.png';
            return '<img class="sb-lang-flag" src="'.$src.'" alt="'.$code.'">';
        }

        return false;

    }

    public static function langMetabox()
    {
        global $post;
        $_sb_lang_meta = get_post_meta($post->ID, self::$meta_key, true); // FIXED changed metakey

        if (empty($_sb_lang_meta)) {
            $_sb_lang_meta = self::$default_language;
        }

        wp_nonce_field(plugin_basename(__FILE__), '_sb_lang_meta_box_noncename');

        $data_lang = self::$languages;

        if (self::$add_mx_lang) {
            $data_lang['mx'] = ucfirst(strtolower(implode(', ', self::$languages)));
        }

        foreach ($data_lang as $code => $label) {
            $flag = self::getFlag($code);
            $data_lang[$code] = $flag.$label;
        }

        echo Fields::radio(array(
            'name'      => 'post_lang',
            'label'     => '',
            'data'      => $data_lang,
            'value'     => $_sb_lang_meta,
        ));
    }

    public static function saveLangMeta($post_id)
    {
        if (empty($_POST['post_type'])) {
            return;
        }

        if (!in_array($_POST['post_type'], self::$langbox_for_post_type)) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (empty($_POST['_sb_lang_meta_box_noncename'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['_sb_lang_meta_box_noncename'], plugin_basename(__FILE__))) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        update_post_meta($post_id, self::$meta_key, $_POST['post_lang']); // FIXED changed metakey
    }

    /**
     * Modifies uri for front-end.
     */
    public static function processURI()
    {

        self::$vars['original_url'] = $_SERVER['REQUEST_URI'];
        self::$vars['do_admin_translate'] = false;

        if ('/wp-admin' == substr(self::$vars['original_url'], 0, 9)
            || '/robots.txt' == self::$vars['original_url']) {
            self::$vars['ignore_lang'] = true;
            return;
        }

        if (false !== strpos(self::$vars['original_url'], '.php')) {
            self::$vars['ignore_lang'] = true;

            // THIS PART MAKES ENGLISH LANGUAGE POSSIBLE ON /en/ using admin such as login.
            if (!empty($_REQUEST['lang']) && isset(self::$languages[trim($_REQUEST['lang'])])) {
                self::$vars['url_lang'] = trim($_REQUEST['lang']);

                self::$vars['do_admin_translate'] = true;
            }

            return;
        }

        if (!empty($_GET['lang']) && isset(self::$languages[trim($_GET['lang'])])) {
            setcookie('lang', $_GET['lang'], time()+2592000, '/');
            header('location: /'.$_GET['lang'].'/');

            die();
        }


        $langs = implode('|', array_keys(self::$languages));

        if (preg_match('#^/(('.$langs.')/?)#', self::$vars['original_url'], $match)) {
            self::$vars['url_lang'] = $match[2];
            $_SERVER['REQUEST_URI'] = substr(self::$vars['original_url'], strlen($match[1]));
        } else {
            if (isset($_COOKIE['lang']) && isset(self::$languages[$_COOKIE['lang']])) {
                self::$vars['url_lang'] = $_COOKIE['lang'];
            } else {
                self::$vars['url_lang'] = self::$default_language;
            }

            header('location: /'.self::$vars['url_lang'].self::$vars['original_url']);
            die();
        }
        self::lang();
    }

    public static function lang()
    {
        return self::$vars['url_lang'];
    }

    public static function isLang($code)
    {
        return self::$vars['url_lang'] == $code;
    }

    public static function addColumns()
    {
        foreach (self::$langbox_for_post_type as $post_type) {
            add_filter('manage_edit-'.$post_type.'_columns', array(__CLASS__, 'registerColumn'));
            add_action('manage_'.$post_type.'_posts_custom_column', array(__CLASS__, 'printColumn'), 10, 2);
        }

        add_action('restrict_manage_posts', array(__CLASS__, 'restrictManageLanguage'));
        add_filter('parse_query', array(__CLASS__, 'sortByLanguage'));

    }

    public static function restrictManageLanguage()
    {
        global $typenow;
        if (isset($typenow)) {
            if (in_array($typenow, self::$langbox_for_post_type)) {
                $lang = empty($_GET['language']) ? '' : $_GET['language'];
                echo '<select name="language">'
                    .'<option value="">Alla språk</options>';

                foreach (self::$languages as $language => $string) {
                    echo '<option value="'.$language.'" '.selected($language, $lang, false).'>'.$string.'</options>';
                }
                echo '</select>';
            }
        }
    }

    public static function sortByLanguage($query)
    {
        global $pagenow;
        if (is_admin() && $pagenow=='edit.php' && !empty($_GET['language'])) {
            $query->query_vars['meta_key'] = self::$meta_key; // FIXED changed metakey

            // check to modify meta_value
            $query_lang = (self::$add_mx_lang) ? array($_GET['language'], 'mx') : $_GET['language'];
            $query->query_vars['meta_value'] = $query_lang; // FIXED QUERY

        }
    }

    public static function registerColumn($columns)
    {
        $columns['lang'] = self::$post_column_name;
        return $columns;
    }

    public static function printColumn($column_name, $post_id)
    {
        if ('lang' != $column_name) {
            return;
        }

        $lang = get_post_meta($post_id, self::$meta_key, true); // FIXED changed metakey

        $languages = self::getLanguages();

        if (empty($lang)) {
            return;
        }

        if ('mx' == $lang) {
            throw new \Exception('Not implemented, check data');
        }

        echo (isset($languages[$lang])) ? self::getFlag($lang).$languages[$lang] : $lang;

    }

    /**
     * Modify permlinks for post and page, adding lang
     */
    public static function modifyLinks($permalink, $post)
    {
        if (is_numeric($post)) {
            $post = get_post($post);
        }

        $lang = get_post_meta($post->ID, self::$meta_key, true); // FIXED changed metakey
        if ('mx' == $lang) { // check if there is a requested lang, prioritize before default
            $lang = lang();
        }
        if ('' == $lang) {
            $lang = self::$default_language;
        }
        $uri_pos = strpos($permalink, '/', 7);
        $permalink = substr_replace($permalink, '/'.$lang.'/', $uri_pos, 1);
        return $permalink;
    }


    /**
     * Modify permlinks for custom post type, adding lang
     */
    public static function modifyCustompostLink($permalink, $post)
    {

        if (in_array($post->post_type, self::$langbox_for_post_type)) {
            $lang = get_post_meta($post->ID, self::$meta_key, true); // FIXED changed metakey
            if ('mx' == $lang) { // check if there is a requested lang, prioritize before default
                $lang = lang();
            }
            if ('' == $lang) {
                $lang = self::$default_language;
            }
            $uri_pos = strpos($permalink, '/', 7);
            $permalink = substr_replace($permalink, '/'.$lang.'/', $uri_pos, 1);

        }

        return $permalink;
    }

    /**
     * Modify paginator links, adding lang
     */
    public static function modifyPageNumLink($link)
    {
        $uri_pos = strpos($link, '/', 7);
        $link = substr_replace($link, '/'.self::lang().'/', $uri_pos, 1);
        return $link;
    }

    /**
     * Modify post_type_archive_link, adding lang
     */
    public static function modifyArchiveLink($link)
    {
        $uri_pos = strpos($link, '/', 7);
        $link = substr_replace($link, '/'.self::lang().'/', $uri_pos, 1);
        return $link;
    }

    /**
     * Modify navitem uri url, adding lang
     */
    public static function modifyNavitemUriLink($link)
    {
        $link = '/'.self::lang().$link;
        return $link;
    }

    public static function getLanguages()
    {
        return self::$languages;
    }

    public static function getDefault()
    {
        return self::$default_language;
    }

    public static function getPostLang($post_id)
    {
        return get_post_meta($post_id, self::$meta_key, true);
    }

    public static function setLangFromDomain()
    {

        if (empty(self::$domain_mapping)) {
            return false;
        }

        foreach (self::$domain_mapping as $lang => $domains) {
            $lang_code = array_search(Utils::domainName(), $domains);
            if ($lang_code) {
                self::$vars['url_lang'] = $lang;
            }
        }

    }
}
