<?php

namespace SB\Utils;

class Bundles {

	public static function load($type, $object, $version, $parent = true)
	{

		$path = array(
			'lib' => 'lib',
			'post_type' => 'lib/Posttypes',
			'bundle' => 'bundles'.'/'.$object.'/'.$version
			);

		if (!isset($path[$type])) {
			debug('ERROR unknown load type '.$type);
			return;
		}

		if ($type == 'bundle' && !$version) {
			debug('ERROR no bundle version specified for '.$object);
			return;
		}

		$base_path = ($parent) ? TEMPLATEPATH : STYLESHEETPATH;

		if (file_exists($base_path.'/'.$path[$type].'/'.$object.'.php')) {
			include_once($base_path.'/'.$path[$type].'/'.$object.'.php');

			if ($type == 'bundle') {

				\SB\Utils::$bundle_versions[$object] = $version;

			}

		} else {
			debug('ERROR loading '.$object);
		}

	}

	public static function get_bundle_uri($bundle)
	{

		$version = (!empty(\SB\Utils::$bundle_versions[$bundle])) ? \SB\Utils::$bundle_versions[$bundle] : false;

		if (empty($bundle)) return false;
		return get_template_directory_uri().'/bundles/'.$bundle.'/'.$version;

	}

}