<?php

namespace SB;

use SB\Utils;

class Forms
{

    public static $version = 11;

    public static $dependencies = array(
        'Utils' => '2.0'
        );

    public static $post_meta_to_save = array();
    public static $save_debug = array();
    public static $save_debug_field = array();
    public static $color_settings = array();
    public static $date_settings = array();
    public static $date_default = array(
        'closeText'         => 'Stäng',
        'prevText'          => '&laquo;Förra',
        'nextText'          => 'Nästa&raquo;',
        'currentText'       => 'Idag',
        'monthNames'        => array('Januari', 'Februari', 'Mars', 'April', 'Maj', 'Juni',  'Juli', 'Augusti',
                                'September', 'Oktober', 'November', 'December'),
        'monthNamesShort'   => array('Jan', 'Feb', 'Mar', 'Apr', 'Maj', 'Jun',  'Jul', 'Aug', 'Sep', 'Okt',
                                'Nov', 'Dec'),
        'dayNamesShort'     => array('Sön', 'Mån', 'Tis', 'Ons', 'Tor', 'Fre', 'Lör'),
        'dayNames'          => array('Söndag', 'Måndag', 'Tisdag', 'Onsdag', 'Torsdag', 'Fredag', 'Lördag'),
        'dayNamesMin'       => array('Sö', 'Må', 'Ti', 'On', 'To', 'Fr', 'Lö'),
        'weekHeader'        => 'Ve',
        'firstDay'          => 1,
        'isRTL'             => false,
        'showMonthAfterYear'=> false,
        'yearSuffix'        => '',
        'dateFormat'        => 'yy-mm-dd',
        'showAnim'          => 'fadeIn'
        );

    public static function init()
    {

        require_once('lib/Fields.php');
        require_once('lib/Options.php');
        require_once('lib/Common.php');
        require_once('lib/Ajax.php');
        require_once('lib/Multi.php');

        Forms\Ajax::init();

        // Statics
        add_action('admin_enqueue_scripts', array(__CLASS__, 'javascript'));
        add_action('admin_print_styles', array(__CLASS__, 'stylesheet'));

        // Javascript data
        add_action('in_admin_footer', array(__CLASS__, 'colorSettings'));
        add_action('in_admin_footer', array(__CLASS__, 'dateSettings'));

        // Other actions
        add_action('save_post', array(__CLASS__, 'postMetaSave'));

    }

    public static function javascript()
    {

        wp_register_script('sb-forms', Utils::getBundleUri('Forms').'/js/forms.min.js', 'jquery', self::$version, true);

        wp_enqueue_media();
        wp_enqueue_script('wp-color-picker');
        wp_enqueue_script('jquery-ui-sortable');
        wp_enqueue_script('jquery-ui-datepicker');
        wp_enqueue_script('sb-forms');

    }

    public static function stylesheet()
    {

        wp_register_style('sb-forms', Utils::getBundleUri('Forms').'/css/forms.css', false, self::$version);
        wp_register_style('sb-datepicker', Utils::getBundleUri('Forms').'/css/datepicker.min.css');

        wp_enqueue_style('thickbox');
        wp_enqueue_style('sb-forms');
        wp_enqueue_style('sb-datepicker');

    }

    // Legacy
    public static function color_settings()
    {
        Utils::deprecated('color_settings', 'colorSettings');
        return self::colorSettings();
    }

    public static function date_settings()
    {
        Utils::deprecated('date_settings', 'dateSettings');
        return self::dateSettings();
    }

    public static function colorSettings()
    {
        wp_localize_script('sb-forms', 'colorFields', self::$color_settings);
    }

    public static function dateSettings()
    {
        wp_localize_script('sb-forms', 'dateDefaults', self::$date_default);
        wp_localize_script('sb-forms', 'dateFields', self::$date_settings);
    }

    public static function save_data_to_session($post)
    {
        Utils::deprecated('save_data_to_session', 'saveDataToSession');
        return self::saveDataToSession($post);
    }

    public static function saveDataToSession($post)
    {

        $_SESSION[$post->post_type] = self::$post_meta_to_save;

    }

    public static function save_post_security($post_id, $nonce_name = false, $action = false, $post_type = false, $user_cap = false)
    {

        Utils::deprecated('save_post_security', 'savePostSecurity');
        return savePostSecurity($post_id, $nonce_name, $action, $post_type, $user_cap);

    }

    public static function savePostSecurity(
        $post_id,
        $nonce_name = false,
        $action = false,
        $post_type = false,
        $user_cap = false
    ) {

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return false;
        }

        if (empty($_POST)) {
            return false;
        }

        if ($post_type != Utils::postString('post_type')) {
            return false;
        }

        if (!current_user_can($user_cap, $post_id)) {
            return false;
        }

        if ($nonce_name && $action) {
            if (!Utils::postString($nonce_name)) {
                return false;
            }
            if (!wp_verify_nonce(Utils::postString($nonce_name), $action)) {
                return false;
            }
        }

        return true;

    }

    public static function register_post_meta($post, $name, $method = false)
    {
        Utils::deprecated('save_data_to_session', 'saveDataToSession');
        return registerPostMeta($post, $name, $method);
    }

    public static function registerPostMeta($post, $name, $method = false)
    {

        // Single differs from get_post_meta, if single == false process an array
        // Fields that needs to be saved as an array; image, posts, url
        // Possible to refer to a method, useful when pre- and post-processing a value

        if (empty(self::$post_meta_to_save)) {
            wp_nonce_field(basename(__FILE__), '_nonce_post_meta');
            self::$post_meta_to_save['post_type'] = $post->post_type;
        }

        self::$post_meta_to_save[$name] = $method;
        self::saveDataToSession($post);

        return get_post_meta($post->ID, $name, true);

    }

    public static function auto_register($auto_value, $value, $name, $method = false)
    {
        Utils::deprecated('auto_register', 'auto_register');
        return autoRegister($auto_value, $value, $name, $method);
    }

    public static function autoRegister($auto_value, $value, $name, $method = false)
    {

        if (!$auto_value) {
            return $value;
        }

        global $post;
        self::registerPostMeta($post, $name, $method);

        return get_post_meta($post->ID, $name, true);

    }

    public static function post_meta_save($post_id)
    {
        Utils::deprecated('post_meta_save', 'postMetaSave');
        return postMetaSave($post_id);
    }

    public static function postMetaSave($post_id)
    {

        $post_type = get_post_type($post_id);

        if (!self::savePostSecurity($post_id, '_nonce_post_meta', basename(__FILE__), $post_type, 'edit_posts')) {
            return false;
        }

        if (empty($_SESSION[$post_type])) {
            return false;
        }

        foreach ($_SESSION[$post_type] as $key => $method) {
            if ($key == 'post_type') {
                continue;
            }
            if ($key == '_nonce_post_meta') {
                continue;
            }
            if (is_bool($method)) {
                $value = ($method) ? Utils::postArray($key, false) : Utils::postString($key, false);
            } else {
                if (is_callable($method)) {
                    $value = call_user_func($method, Utils::postVar($key), $key);
                } else {
                    Utils::debug('ERROR '.$method.' is not callable, field not processed.', 0);
                    continue;
                }
            }

            self::$save_debug[] = array($key => $value);
            update_post_meta($post_id, $key, $value);

        }

        unset($_SESSION[$post_type]);

    }

    public static function debug()
    {

        add_action('shutdown', array(__CLASS__, 'shutdown'), 9999);

    }

    public static function shutdown()
    {

        if (empty($_POST)) {
            return false;
        }

        if (empty(self::$save_debug)) {
            return false;
        }

        Utils::debug(self::$save_debug, 0);

    }
}
