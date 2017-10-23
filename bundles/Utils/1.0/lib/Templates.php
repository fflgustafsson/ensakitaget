<?php

namespace SB\Utils;

class Templates {

	public static $templates = array();

	public static function show_name($post_type = 'page')
	{

		if (empty($post_type) || 'page' == $post_type) {

			$add_function = 'manage_edit-page_columns';
			$value_function = 'manage_pages_custom_column';

		} else {

			// FIXME check if post_type has template capability

			$add_function = 'manage_'.$post_type.'_posts_columns';
			$value_function = 'manage_'.$post_type.'_posts_custom_column';

		}

		add_action($add_function, array(__CLASS__, 'add_template_column'));
		add_action($value_function, array(__CLASS__, 'print_column_value'), 10, 2);

	}

	public static function add_template_column($columns)
	{

		$columns['template'] = __('Template');
		return $columns;

	}

	public static function print_column_value($column_name, $post_id)
	{

		if ('template' == $column_name) {

			self::$templates = (empty(self::$templates)) ? array_flip(get_page_templates()) : self::$templates;
			$template_name = get_post_meta($post_id, '_wp_page_template', true);

			if (!empty(self::$templates[$template_name])) {
				echo self::$templates[$template_name];
			}

			if ('default' == $template_name) {
				echo __('Default Template');
			}

		}

	}

	/**
	 * Replacement for Wordpress add_meta_box
	 * This function should be called from the add_meta_boxes_{post_type} or 'add_meta_boxes' action. The former is preferable as it avoids triggering needless callbacks for other post types. These actions were introduced in Version 3.0; in prior versions, use 'admin_init' instead.
	 * It works just like add_meta_box, but you can add an array of template slugs to the $templates array.
	 * Leaving it empty makes the metabox visible on all templates
	 * @return none
	 */

	public static function add_template_meta_box($id, $title, $callback, $post_type, $context = 'advanced', $priority = 'default', $callback_args = null, $templates = array())
	{

		global $post, $_template_meta_boxes;

		if (empty($post->post_type)) return false;

		$pto = get_post_type_object($post->post_type);

		if ('page' != $pto->capability_type) return false;

		$filter = 'postbox_classes_'.$post_type.'_'.$id;

		add_meta_box($id, $title, $callback, $post_type, $context, $priority, $callback_args);
		add_filter($filter, array(__CLASS__, 'add_class_to_meta_box'));

		if (empty($_template_meta_boxes)) {
			$_template_meta_boxes = array();
		}

		$_template_meta_boxes[$filter] = array('post_type' => $post_type, 'id' => $id, 'templates' => $templates);

		return true;

	}

	public static function add_class_to_meta_box($classes)
	{

		global $_template_meta_boxes;
		$current_filter = current_filter();

		if (empty($_template_meta_boxes)) return $classes;
		if (empty($_template_meta_boxes[$current_filter])) return $classes;

		if (empty($_template_meta_boxes[$current_filter]['templates'])) return $classes;
		if (!is_array($_template_meta_boxes[$current_filter]['templates'])) return $classes;

		array_push($classes, 'sb-template-meta-box');

		foreach ($_template_meta_boxes[$current_filter]['templates'] as $template) {

			$template = str_replace('_', '-', $template);
			array_push($classes, 'sb-'.$template);

		}

		return $classes;

	}

}

// Example
//
// 1. Create three templates called First, Second, Third
//
// 2. Add actions to add meta box:
//
// add_action('add_meta_boxes_page', 'my_first_metabox');
// add_action('add_meta_boxes_page', 'my_second_metabox');
//
// 3. Add the action and run the add_template_meta_box adding a template array with the template names.
//
// function my_first_metabox()
// {
//
// 	Utils::add_template_meta_box('my_first_metabox', 'Metabox 1', 'my_first_form', 'page', 'normal', 'high', null, array('first', 'third'));
//
// }
//
// function my_second_metabox()
// {
//
// 	Utils::add_template_meta_box('my_second_metabox', 'Metabox 2', 'my_second_form', 'page', 'normal', 'high', null, array('second', 'third'));
//
// }
//
// use SB\Forms as Forms;
// use SB\Forms\Fields as Fields;

// function my_first_form()
// {
//
// 	echo Fields::text(array(
// 		'name'	=> '_first',
// 		'label' => 'First',
// 		'auto_value' => true,
// 		'type' => 'text'
// 		));
//
// }
//
// function my_second_form()
// {
//
// 	echo Fields::text(array(
// 		'name'	=> '_second',
// 		'label' => 'Second',
// 		'auto_value' => true,
// 		'type' => 'text'
// 		));
//
// }
//

