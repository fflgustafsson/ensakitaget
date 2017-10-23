<?php

namespace SB;

use SB\Media;
use SB\Utils;

class SiteMaps {

	protected static $options = array(
			'location' => ABSPATH,
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


	public static function register_settings( $settings ){

		self::$options = array_replace_recursive(self::$options, $settings);

		if (!file_exists(self::$options['location'])) {

			if (is_writable(self::$options['location'])) {
			    mkdir(self::$options['location'], 0777, true);
			}

		}

		add_filter('robots_txt', array(__CLASS__, 'robots_txt'), 10, 2);

	}

	public static function image_sitemap() {

		$file_name = self::$options['location'] . '/sitemap-image.xml';

		if (!is_writable($file_name)) {
			debug('ERROR cant write file to path: '.self::$options['location']);
			return;
		}

		$output = self::header('image');

		$post_types = array();
		foreach (self::$options['images']['post_types'] as $key => $value) {
			array_push($post_types, $key);
		}

		$query_params = array(
				'post_type' => $post_types
			);

		$query = new \WP_Query($query_params);

		while( $query->have_posts() ){
			$post = $query->next_post();

			$output .= self::image_item($post, self::$options['images']['post_types'][$post->post_type]);
		}

		// Close tag
		$output .= "\n" . '</urlset>';

		$file = fopen($file_name, 'w');
		if( $file ){
			fwrite($file, $output);
			fflush($file);
			fclose($file);
		}

	}


	protected static function image_item( $post, $meta_fields ) {
		$images = array();

		// Look for thumbnail
		$media_id = get_post_thumbnail_id($post->ID);
		if( !empty($media_id) ){

			$image = Media::get($media_id);
			array_push($images, $image);
		}

		// Look for inline images in content
		// Fetch classes and load attachment from class
		$content = $post->post_content;
		$matches = array();
		preg_match('/<img.+?class=[\\"\'](.+?)[\\"\'].*?>/i', $content, $matches);

		if( is_array($matches) && count( $matches) > 0 ){
			$classes = explode(' ', $matches[1]);
			foreach ($classes as $class) {

				if( strpos($class, 'wp-image-') !== false ){

					list($t, $m, $media_id) = explode('-', $class);

					$image = Media::get($media_id);

					array_push($images, $image);

				}
			}
		}

		// Look for images in meta fields
		if( is_array($meta_fields) ){
			foreach( $meta_fields as $field => $type) {

				$values = get_post_meta($post->ID, $field, true);

				// If there's no value continue
				if( (is_array($values) && count( $values ) === 0) || (!is_array($values) && strlen($values) === 0) ){
					continue;
				}

				if( $type == 'content' ){


					$matches = array();
					preg_match('/<img.+?class=[\\"\'](.+?)[\\"\'].*?>/i', $values, $matches);

					if( is_array($matches) && count( $matches) > 0 ){
						$classes = explode(' ', $matches[1]);
						foreach ($classes as $class) {

							if( strpos($class, 'wp-image-') !== false ){

								list($t, $m, $media_id) = explode('-', $class);

								$image = Media::get($media_id);
								array_push($images, $image);

							}
						}
					}

				} else if( $type == 'single' ){
					foreach( $values as $value ){

						$image = Media::get($value);
						array_push($images, $image );

					}
				}
			}
		}

		if( empty($images) ){
			return '';
		}

		$location = get_permalink($post->ID);
		$image_item = "\n\t" . '<url>' . "\n\t\t" . '<loc>' . $location . '</loc>';
		$image_item .= "\n\t\t" . '<lastmod>' . get_post_modified_time('Y-m-d', false, $post) . '</lastmod>';
		$image_item .= "\n\t\t" . '<changefreq>' . self::$options['images']['change_frequency'] . '</changefreq>';
		foreach ($images as $image) {
			$image_item .= "\n\t\t" . '<image:image>' . "\n\t\t\t" . '<image:loc>' . site_url() . $image['src'][0] . '</image:loc>';
			$meta_data = $image['meta']['post_metadata'];

			if( !empty($meta_data['description']) ){
				$image_item .= "\n\t\t\t" . '<image:caption>' . htmlspecialchars($meta_data['description']) . '</image:caption>';
			}
			if( !empty($meta_data['title']) ){
				$image_item .= "\n\t\t\t" . '<image:title>' . htmlspecialchars($meta_data['title']) . '</image:title>';
			}

			$image_item .= "\n\t\t" . '</image:image>';
		}

   		$image_item .= "\n\t" . '</url>';

   		return $image_item;
	}

	public static function video_sitemap() {

		$file_name = self::$options['location'] . '/sitemap-video.xml';

		if (!is_writable($file_name)) {
			debug('ERROR cant write file to path: '.self::$options['location']);
			return;
		}

		$output = self::header('video');

		$post_types = array();
		foreach (self::$options['videos']['post_types'] as $key => $value) {
			array_push($post_types, $key);
		}

		$query_params = array(
			'post_type' => $post_types,
			'posts_per_page' => -1,
			);

		$query = new \WP_Query($query_params);

		while( $query->have_posts() ){

			$post = $query->next_post();

			// console($post->post_title);
			$output .= self::video_item($post, self::$options['videos']['post_types'][$post->post_type]);

		}

		// Close tag
		$output .= "\n" . '</urlset>';

		$file = fopen($file_name, 'w');
		if( $file ){
			fwrite($file, $output);
			fflush($file);
			fclose($file);
		}

	}

	protected static function video_item( $post, $meta_fields ) {
		$videos = array();

		// Check for videos
		foreach( $meta_fields as $meta_field => $value){

			$inline_videos = get_post_meta( $post->ID, $meta_field, true);

			if( !empty( $inline_videos) ){
				$videos = array_merge($videos, $inline_videos->items);
			}

		}

		if( empty($videos) ){
			return;
		}

		$location = get_permalink($post->ID);
		$video_item = "\n\t" .'<url>' . "\n\t\t" . '<loc>' . $location . '</loc>';
		$video_item .= "\n\t\t" . '<lastmod>' . get_post_modified_time('Y-m-d', false, $post) . '</lastmod>';
		$video_item .= "\n\t\t" . '<changefreq>' . self::$options['videos']['change_frequency'] . '</changefreq>';

		foreach ($videos as $video) {
			$duration_data = $video->contentDetails['duration'];
			$duration_time = new \DateInterval( $duration_data );
			$duration = $duration_time->h * 60 * 60 + $duration_time->i * 60 + $duration_time->s;

			$video_item .= "\n\t\t" .'<video:video>' . "\n\t\t\t" . '<video:thumbnail_loc>' . $video->snippet['thumbnails']['high']['url'] . '</video:thumbnail_loc>';
			$video_item .= "\n\t\t\t" .'<video:title>' . htmlspecialchars($video->snippet['title']) . '</video:title>';
			$video_item .= "\n\t\t\t" .'<video:description>' . htmlspecialchars($video->snippet['description']) . '</video:description>';
			$video_item .= "\n\t\t\t" .'<video:player_loc>https://www.youtube.com/embed/' . $video->id . '</video:player_loc>';
			$video_item .= "\n\t\t\t" .'<video:duration>' . $duration . '</video:duration>' . "\n\t\t" . '</video:video>';
		}

   		$video_item .= "\n\t" . '</url>';

   		return $video_item;
	}

	protected static function header( $type = 'image' ) {
		if( $type === 'image' ){
			return '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"
	xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
	xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . "\n";
	  	}

	  	if( $type === 'video' ){
	  		return '<?xml version="1.0" encoding="UTF-8"?>
<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
	xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd"
	xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"
	xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">' . "\n";

	  	}
	}

	public static function robots_txt($output, $public)
	{

		$sitemaps = array();
		$sitemaps[] = 'Sitemap: ' . Utils::wp_siteurl() . '/sitemap-image.xml';
		$sitemaps[] = 'Sitemap: ' . Utils::wp_siteurl() . '/sitemap-video.xml';

		return $output . implode("\n", $sitemaps);

	}

}