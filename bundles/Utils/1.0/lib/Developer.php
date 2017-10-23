<?php

namespace SB\Utils;

use SB\Utils;

Developer::init();

class Developer {

	public static $hooks;

	public static function init()
	{

		add_action('all', array(__CLASS__, 'tracker'));
		// add_action('shutdown', array(__CLASS__, 'timer'), 9998);

	}

	public static function console($data)
	{

		if (is_array($data) || is_object($data)) {
			ob_start();
				print_r($data);
			error_log(ob_get_clean());
		} else {
			error_log($data);
		}

	}

	public static function debug($data, $backtrace = 1)
	{

		if (WP_DEBUG === true) {

			if (Utils::is_ajax_request()) {
				self::console('AJAX:');
			}

			if ($backtrace) {

				if (2 == $backtrace) {
					self::console(debug_backtrace());
				} else {
					$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
					self::console('DEBUG '.basename($backtrace[1]['file']).' @ line '.$backtrace[1]['line']);
				}

			}

			self::console($data);

		}

	}

	public static function tracker()
	{

		$filter = current_filter();
		$func = false;

		if ( ! empty($GLOBALS['wp_filter'][$filter]) ) {

			foreach ( $GLOBALS['wp_filter'][$filter] as $priority => $tag_hooks ) {

				foreach ( $tag_hooks as $hook ) {

					if ( is_array($hook['function']) )  {

						if ( is_object($hook['function'][0]) ) {

							$func = get_class($hook['function'][0]) . '->' . $hook['function'][1];

						} elseif ( is_string($hook['function'][0]) ) {

							$func = $hook['function'][0] . '::' . $hook['function'][1];

						}

					} elseif( $hook['function'] instanceof Closure ) {

						$func = 'a closure';

					} elseif( is_string($hook['function']) ) {

						$func = $hook['function'];

					}

					self::$hooks[] = array($filter => array($priority => $func));

				}
			}
		}

	}

	public static function shutdown()
	{

		debug(self::$hooks);

	}

	public static function timer()
	{

		if (is_user_logged_in() && current_user_can('manage_options')) :

			$queries = get_num_queries();
            $timer = timer_stop(0, 2);

            ?>

            <script type="text/javascript">

            	(function(){

            		console.log('Debug: <?php echo $queries ?> queries');
            		console.log('Debug: <?php echo $timer ?>s process time');

            	})();

            </script>

            <?php

        endif;

	}

}