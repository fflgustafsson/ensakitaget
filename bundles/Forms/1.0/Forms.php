<?php

namespace SB;

use SB\Utils as Utils;

Forms::init();

class Forms {

	static $version = 10;

	static $post_meta_to_save = array();
	static $save_debug = array();
	static $save_debug_field = array();
	static $color_settings = array();
	static $date_settings = array();
	static $date_default = array(
		'closeText'			=> 'Stäng',
		'prevText'			=> '&laquo;Förra',
		'nextText'			=> 'Nästa&raquo;',
		'currentText'		=> 'Idag',
		'monthNames'		=> array('Januari', 'Februari', 'Mars', 'April', 'Maj', 'Juni',  'Juli', 'Augusti', 'September', 'Oktober', 'November', 'December'),
		'monthNamesShort'	=> array('Jan', 'Feb', 'Mar', 'Apr', 'Maj', 'Jun',  'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dec'),
		'dayNamesShort'		=> array('Sön', 'Mån', 'Tis', 'Ons', 'Tor', 'Fre', 'Lör'),
		'dayNames'			=> array('Söndag', 'Måndag', 'Tisdag', 'Onsdag', 'Torsdag', 'Fredag', 'Lördag'),
		'dayNamesMin'		=> array('Sö', 'Må', 'Ti', 'On', 'To', 'Fr', 'Lö'),
		'weekHeader'		=> 'Ve',
		'firstDay'			=> 1,
		'isRTL'				=> false,
		'showMonthAfterYear'=> false,
		'yearSuffix'		=> '',
		'dateFormat'		=> 'yy-mm-dd',
		'showAnim'			=> 'fadeIn'
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
		add_action('in_admin_footer', array(__CLASS__, 'color_settings'));
		add_action('in_admin_footer', array(__CLASS__, 'date_settings'));

		// Other actions
		add_action('save_post', array(__CLASS__, 'post_meta_save'));

	}

	public static function javascript()
	{

		wp_register_script('sb-forms', Utils::get_bundle_uri('Forms').'/js/forms.js', 'jquery', self::$version, true);

		wp_enqueue_media();
		wp_enqueue_script('wp-color-picker');
		wp_enqueue_script('jquery-ui-sortable');
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_script('sb-forms');

	}

	public static function stylesheet()
	{

		wp_register_style('sb-forms', Utils::get_bundle_uri('Forms').'/css/forms.css', false, self::$version);
		wp_register_style('sb-datepicker', Utils::get_bundle_uri('Forms').'/css/datepicker.min.css');

		wp_enqueue_style('thickbox');
		wp_enqueue_style('sb-forms');
		wp_enqueue_style('sb-datepicker');

	}

	public static function color_settings()
	{
		if (empty(self::$color_settings)) return;
	    wp_localize_script('sb-forms', 'colorFields', self::$color_settings);
	}

	public static function date_settings()
	{
		if (empty(self::$date_settings)) return;
		wp_localize_script('sb-forms', 'dateDefaults', self::$date_default);
	    wp_localize_script('sb-forms', 'dateFields', self::$date_settings);
	}

	public static function save_data_to_session($post)
	{

		$_SESSION[$post->post_type] = self::$post_meta_to_save;

	}

	public static function save_post_security($post_id, $nonce_name = false, $action = false, $post_type, $user_cap)
	{

		if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return false;
		if (empty($_POST)) return false;

		if ($post_type != Utils::post_string('post_type')) return false;
		if (!current_user_can($user_cap, $post_id)) return false;

		if ($nonce_name && $action) {
	        if (!Utils::post_string($nonce_name)) return false;
	        if (!wp_verify_nonce(Utils::post_string($nonce_name), $action)) return false;
		}

		return true;

	}

	public static function register_post_meta($post, $name, $method = false)
	{

		// Single differs from get_post_meta, if single == false process an array
		// Fields that needs to be saved as an array; image, posts, url
		// Possible to refer to a method, useful when pre- and post-processing a value

		if (empty(self::$post_meta_to_save)) {
			wp_nonce_field(basename(__FILE__), '_nonce_post_meta');
			self::$post_meta_to_save['post_type'] = $post->post_type;
		}

		self::$post_meta_to_save[$name] = $method;
		self::save_data_to_session($post);

		return get_post_meta($post->ID, $name, true);

	}

	public static function auto_register($auto_value, $value, $name, $method = false)
	{

		if (!$auto_value) return $value;

		global $post;
		self::register_post_meta($post, $name, $method);

		return get_post_meta($post->ID, $name, true);

	}

	public static function post_meta_save($post_id)
	{

		$post_type = get_post_type($post_id);

		if (!self::save_post_security($post_id, '_nonce_post_meta', basename(__FILE__), $post_type, 'edit_posts')) {
			return false;
		}

		if (empty($_SESSION[$post_type])) return false;

		foreach ($_SESSION[$post_type] as $key => $method) {

			if ($key == 'post_type') continue;
			if ($key == '_nonce_post_meta') continue;

			if (is_bool($method)) {

				$value = ($method) ? Utils::post_array($key, false) : Utils::post_string($key, false);

			} else {

				if (is_callable($method)) {
					$value = call_user_func($method, Utils::post_var($key), $key);
				} else {
					debug('ERROR '.$method.' is not callable, field not processed.');
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

		if (empty($_POST)) return false;
		if (empty(self::$save_debug)) return false;

		debug(self::$save_debug);

	}

}
