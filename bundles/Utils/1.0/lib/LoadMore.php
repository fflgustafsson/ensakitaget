<?php

namespace SB\Utils;

use SB\Utils;
use \WP_Query;

class LoadMore {

	public static $config = array();

	public static function init($config)
	{

		add_action('wp_ajax_load_more', array(__CLASS__, 'request'));
		add_action('wp_ajax_nopriv_load_more', array(__CLASS__, 'request'));

		foreach ($config as $name => $data) {
			self::$config[$name] = $data;
		}

	}

	public static function get_config($config)
	{

		if (empty(self::$config[$config])) return false;

		return self::$config[$config];

	}

	public static function request()
	{

		if (!Utils::is_ajax_request()) return;

		$config_name = Utils::post_string('config');
		$config = self::get_config($config_name);
		$paged = Utils::post_string('paged');
		$meta_value = Utils::post_string('meta_value');
		$category = Utils::post_string('category');

		if (!$config) return;

		$config['paged'] = (is_numeric($paged)) ? $paged : 2;

		$query = self::query($config, $meta_value, $category);
		$posts = (empty($query['posts'])) ? array() : $query['posts'];
		$posts = apply_filters('SB_Load_More', $posts, $config_name);

		$json = array(
			'nextPage' => $config['paged'] + 1,
			'noMore' => $query['no_more'],
			'posts' => $posts,
			);

		return Utils::return_JSON($json);

	}

	public static function query($config, $meta_value = false, $category_name = false)
	{

		$default = array(
			'posts_per_page' => get_option('posts_per_page'),
			'orderby' => 'post_date',
			'order' => 'DESC',
			'post_type' => 'post',
			'category' => false,
			'category_name' => false,
			'paged' => 1,
			'subsets' => false,
			'ignore_sticky_posts' => true,
			'post_status' => 'publish',
			'debug' => false
			);

		$args = (is_array($config)) ? $config : self::get_config($config);

		if ($category_name) {
			$args['category_name'] = $category_name;
		}

		if (!empty($args['meta_key'])) {
			$args['meta_value'] = $meta_value;
		}

		$args = wp_parse_args($args, $default);
		extract($args);

		if ($category) {
			$args['category'] = false;
			$args['category_name'] = false;
			$args['tax_query'] = array(
					array(
						'taxonomy' => $taxonomy,
						'field' => 'slug',
						'terms' => $category_name
					)
				);
		}

		// If you need to order by meta_value or meta_value_num, make sure you add the post_meta to all posts first.
		// Utils::post_meta($post_type, $meta_key, $default_value);

		if ($orderby == 'meta_value_num') {
			unset($args['meta_value']);
		}

        $args = apply_filters('SB_Load_More_Query_Args', $args, $config);

		if ($args['debug']) {
			console($args);
		}

		$query = new WP_Query($args);

		if ($args['debug']) {
			console($query->request);
		}

		$posts = $query->posts;
		$found_posts = $query->found_posts;
		$shown_posts = ($paged * $posts_per_page);
		$no_more = ($query->max_num_pages == $paged) ? true : false;
		$total_posts_left = ($found_posts - $shown_posts);

		if ($subsets && ($total_posts_left < $posts_per_page)) {

			$args['paged'] = $paged + 1;
			$query = new WP_Query($args);
			$posts = array_merge($posts, $query->posts);
			$no_more = true;

		}

		// Just one more sanity check
		if (0 == $found_posts) {
			$no_more = true;
		}

		return array(
			'no_more' => $no_more,
			'found_posts' => $found_posts,
			'meta_value' => $meta_value,
			'posts' => $posts
		);

	}

}
