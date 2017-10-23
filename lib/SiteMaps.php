<?php

use \SB\Sitemaps;

$options = array(
	'location' => WP_CONTENT_DIR.'/sitemaps',
	'images' => array(
		'post_types' => array( 'article' => array('_image' => 'single', '_facts' => 'content'),
			'post' => array(),
			'page' => array(),
		),
		'change_frequency' => 'weekly'

	),
	'videos' => array(
		'post_types' => array( 'article' => array('_youtube_meta' => 'single', '_inline_youtube_meta' => 'content'),
			'post' => array(),
			'page' => array(),
		),
		'change_frequency' => 'weekly'
	)
);

Sitemaps::register_settings($options);

// add_action('save_post', 'generate_sitemaps', 100);

// function generate_sitemaps() {
// 	SiteMaps::image_sitemap();
// 	SiteMaps::video_sitemap();
// }
