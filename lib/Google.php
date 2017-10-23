<?php

use SB\Utils;
use SB\Google;
use SB\Google\Youtube;

$data = array(
		'cache_time' => 43200, // Seconds
		'application_name' => 'Trygg Hansa Hemmet',
		'service_account_name' => '150809009475-r3kuh96fvqhrbr13gk2fuhdo5oblairc@developer.gserviceaccount.com', // Fetched from Application created in Google Developer Console
		'key_file_location' => get_template_directory() . '/key.p12', // Fetched from Application created in Google Developer Console
		'view_id' => 'ga:91909234', // View/profile id from google analytics
		'ga_account' => 'UA-55336736-1', //
		'tm_container_id' => 'GTM-N46GQG', // ID of container from Google Tag Manager
		'data_layer' => array(
			'name' => 'dataLayer',
			'vars' => array(
				'pageTitle' => 'page_title',
				'uaId'	=> 'ga_account'
				)
			)
	);

// Override Social methods. This is optional. Best is to not use these at all. Instead put all social eventlisteners in common javascript file.
// Copy and paste from social.interactions-template.js in bundle to common javascript. Be sure to localize script with correct variables according to code below.
class Analytics extends SB\Google\Analytics {

	public static function social_interactions() {
		add_action('wp_enqueue_scripts', array(__CLASS__, 'include_social_eventlisteners'), 30);

	}


	public static function include_social_eventlisteners() {

		wp_register_script( 'sb-social-interactions', Utils::get_bundle_uri('Google') . '/js/social.interactions-template.js', array(), self::$version, true );

		wp_localize_script( 'sb-social-interactions', 'sbSocial', array('isGaInUse' => self::$is_google_analytics_used ? 1 : 0, 'isTagManagerInUse' => self::$is_tag_manager_used ? 1 : 0) );

		wp_enqueue_script( 'sb-social-interactions' );


	}

}

function render_social_scripts() {

?>
	<!-- Place this tag after the last +1 button tag. -->
	<script type="text/javascript">
	  window.___gcfg = {lang: 'sv'};

	  (function() {
	    var po = document.createElement('script'); po.type = 'text/javascript'; po.async = true;
	    po.src = 'https://apis.google.com/js/platform.js';
	    var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(po, s);
	  })();
	</script>
	



<?php

}

// add_action('wp_footer', 'render_social_scripts', 10);

// Google::register_settings( $data );
// Google::init_service(array('analytics', 'youtube'));

// Analytics::social_interactions();

// Add this method as the first element of the body-tag to
// render Google Analytics Tag Manager script in correct place
// <?php Analytics::tag_manager();
