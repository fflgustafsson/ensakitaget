<?php

//
// Example
// Responsive::register_sizes(
// 	array(
// 		'hero'		=> array(
// 			'size'			=> array(1024, 768),
// 			'breakpoints'	=> array('large' => 1024, 'medium' => 768, 'small' => 540),
// 			'crop'			=> true,
// 			)
// 		)
// 	);
// Explanation:
// key in top array represents the image name
// size represents the maximal image size (without retina)
// breakpoints generates the sizes, according to the 'size' ratio
// crop true means hard crop, i.e the size will endup with the same ratio as 'size'
//

namespace SB\Media;

use SB\Utils;

class Responsive {

	public static $sizes = array();

	public static function init()
	{

		add_action('wp_ajax_get_image', array(__CLASS__, 'image'));
		add_action('wp_ajax_nopriv_get_image', array(__CLASS__, 'image'));

	}

	public static function register_sizes($args)
	{

		foreach ($args as $base => $data) {

			$crop = ($data['crop']) ? true : false;

			foreach ($data['breakpoints'] as $size => $width) {

				$name = $base.'_'.$size;
				$ratio = $data['size'][1] / $data['size'][0];

				add_image_size($name, $width, ceil($width * $ratio), true);
				add_image_size($name.'_retina', ($width * 2), ceil(($width * 2) * $ratio), true);

				self::$sizes[$base][$name] = $width;
				self::$sizes[$base][$name.'_retina'] = ($width * 2);

			}

		}

	}

	private static function get_size($width, $size)
	{

		$sizes = self::$sizes[$size];
		asort($sizes);

		foreach ($sizes as $key => $val) {

			$max = max($val, $width);
			if ($max > $width || $width == $val) {
				return $key;
			}

		}

		end($sizes);
		return key($sizes);

	}

	public static function image()
	{

		if (!Utils::is_ajax_request()) return;

		$width = Utils::post_int('width');
		$media = Utils::post_int('media');
		$size = Utils::post_string('size');
		$retina = (Utils::post_string('retina') == 'true') ? true : false;

		// Double width for retina
		$width = ($retina) ? $width * 2 : $width;

		// Get size name
		$size = self::get_size($width, $size);

		$image = wp_get_attachment_image_src($media, $size);
		$alt = get_post_meta($media, '_wp_attachment_image_alt', true);

		Utils::return_JSON(array(
			'tag' => '<img src="'.$image[0].'" alt="'.$alt.'">',
			'src' => $image[0],
			'size' => $size
			));

	}

	public static function get_sizes($image, $size)
	{

		if (empty($image['src'][0])) return false;

		$dir = dirname($image['src'][0]);

		$sizes = self::$sizes[$size];
		asort($sizes);

		$return = array();

		foreach ($sizes as $name => $width) {

			$file = (!empty($image['meta']['sizes'][$name])) ? $dir.'/'.$image['meta']['sizes'][$name]['file'] : $file;
			$return[$width] = array('src' => $file, 'alt' => $image['meta']['post_metadata']['alt']);

		}

		return json_encode($return, JSON_FORCE_OBJECT);

	}

}