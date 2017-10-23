<?php

namespace SB;

use SB\Utils as Utils;

// SB\Sortable::register(array(
// 	'post_type'			=> 'page',
// 	'sub_pages'			=> true,
// 	));

Sortable::init();

class Sortable {

	public static $sorters = array();

	public static $default = array(
		'post_type'			=> 'page',
		'menu_name'			=> 'Sortera ordning',
		'headline'			=> 'Sortera ordning',
		'description'		=> 'Sortera sidordning nedan genom dra och släpp.',
		'post_type_name'	=> 'Sidor',
		'sub_pages'			=> false,
		'post_status'		=> array('publish', 'private', 'draft', 'pending', 'future'),
		'class'				=> false,
		'by_category'		=> false,
		'taxonomy'			=> 'category',
		'select_label'		=> 'Välj kategori...',
		'date_sort_helper'	=> false
		);

	public static function init()
	{

		add_action('admin_enqueue_scripts', array(__CLASS__, 'javascript'));
		add_action('admin_print_styles', array(__CLASS__, 'stylesheet'));

	}

	public static function register($args)
	{

		add_action('admin_menu', array(__CLASS__, 'add_sort_page'));
		self::$sorters[] = $args;

	}

	public static function add_sort_page()
	{

		foreach (self::$sorters as $sorter) {
			if (empty($sorter['post_type'])) continue;

			$args = wp_parse_args($sorter, self::$default);
			extract($args);
			add_submenu_page('edit.php?post_type='.$post_type, $menu_name, $menu_name, 'edit_others_posts', 'sb_'.$post_type.'_sort', array(__CLASS__, 'form'));

		}

	}

	public static function javascript()
	{
		wp_enqueue_script('jquery-ui-sortable');
		wp_register_script('sb-page-order-js', Utils::get_bundle_uri('Sortable').'/js/sortable.js', 'jquery', '2', true);
		wp_enqueue_script('sb-page-order-js');
	}

	public static function stylesheet()
	{
		wp_register_style('sb-page-order-css', Utils::get_bundle_uri('Sortable').'/css/sortable.css', false, '2');
		wp_enqueue_style('sb-page-order-css');
	}

	public static function get_registered_args($post_type)
	{

		foreach (self::$sorters as $id => $args) {
			if ($args['post_type'] == $post_type) return self::$sorters[$id];
		}

		return false;

	}

	public static function form()
	{

		self::save_order();

		$post_type = Utils::get_string('post_type');
		$args = wp_parse_args(self::get_registered_args($post_type), self::$default);
		extract($args);

		if ($by_category) {
			self::add_category_post_meta($post_type);
		}

		$category_select = ($by_category) ? self::get_category_select($post_type, $select_label, $taxonomy) : false;
		$post_meta = ($by_category) ? '_category_order' : 'menu_order';
		$post_meta_class = ($by_category) ? 'by-category' : false;

		$sort_sub_label = (!empty($sort_sub_label)) ? $sort_sub_label : 'Visa alla undersidor';

		$sort_sub = ($sub_pages == true) ? '<a href="#" id="expand-all" class="button-secondary">'.$sort_sub_label.'</a>' : false;

		$classes = array('wrap', 'sb-sort-post-type', $post_meta_class, $class);

		?>

		<div class="<?php echo implode(' ', $classes) ?>">
			<h2><?php echo $headline; ?></h2>
			<p class="description">
				<?php echo $description; ?>
			</p>
			<form method="post" id="page-order-form" class="options sb-page-order">
				<?php wp_nonce_field('sb_page_order', 'sb_nonce'); ?>
				<input type="hidden" name="post_meta" value="<?php echo $post_meta; ?>">
				<h3><?php echo $post_type_name; ?><?php echo $sort_sub; ?></h3>
				<?php echo $category_select; ?>
				<div class="page-wrapper">
					<?php self::print_list($by_category, $taxonomy); ?>
				</div>
				<?php self::sort_helper(); ?>
				<p class="submit">
					<input id="page-order" class="button-primary" name="save" type="submit" value="Spara ordning" />
					<span class="spinner"></span>
				</p>
			</form>
		</div>

		<?php

	}

	private static $status_data = array(
		'publish' => 'Publicerad',
		'private' => 'Privat',
		'draft'	=> 'Utkast',
		'pending' => 'Väntande',
		'future' => 'Tidsinställd'
		);

	public static function get_pages_hierachy($page_id = 0, $by_category = false, $taxonomy, $category = false)
	{

		$post_type = Utils::get_string('post_type');
		$args = wp_parse_args(self::get_registered_args($post_type), self::$default);

		if (!$args['sub_pages'] && $page_id > 0) return array();

		$query = array(
			'post_type' => $post_type,
			'post_status' => $args['post_status'],
			'post_parent' => $page_id,
			'posts_per_page' => -1
			);

		$query['order'] = 'ASC';
		$query['orderby'] = 'menu_order';

		if ($by_category) {

			if ($category == false) return array();

			$query['orderby'] = 'meta_value_num';
			$query['meta_key'] = '_category_order';
			$query['tax_query'] = array(
					array(
						'taxonomy' => $taxonomy,
						'field' => 'term_id',
						'terms' => $category
					)
				);

		}

		$pages = get_posts($query);

		$return = array();
		foreach ($pages as $i => $page) {
			$return[$page->ID] = array(
				'order' => $i,
				'id' => $page->ID,
				'post_title' => _draft_or_post_title($page->ID),
				'children' => self::get_pages_hierachy($page->ID, $by_category, $taxonomy, $category),
				'post_status' => $page->post_status,
				'_category_order' => get_post_meta($page->ID, '_category_order', true)
				);
		}

		return $return;

	}

	public static function print_list($by_category, $taxonomy)
	{

		$category = Utils::get_string('term_id');

		$pages = self::get_pages_hierachy(0, $by_category, $taxonomy, $category);
		echo '<ul class="page-order base">';
		foreach ($pages as $id => $page) {
			echo self::list_element($page);
		}
		echo '</ul>';

	}

	public static function list_element($page)
	{

		$has_children = (!empty($page['children'])) ? '<a class="child-arrow" href="#"></a>' : false;

		$page_title = apply_filters('sb_sortable_post_title', $page['post_title'], $page['id'], get_post_type($page['id']));

		$data = array();
		$data[] = 'data-id="'.$page['id'].'"';
		$data[] = 'data-date="'.get_the_date('U', $page['id']).'"';
		$data[] = 'data-order="'.$page['order'].'"';
		$data = apply_filters('sb_sortable_post_data', $data, $page['id'], get_post_type($page['id']));

		$element  = '<li '.implode(' ', $data).'>';
		$element .= '<div class="page"><a class="post_title" href="'.admin_url('post.php?post='.$page['id'].'&action=edit').'">'.$page_title.'</a>'.$has_children;
		$element .= '<span>'.self::post_meta($page).'</span>';
		$element .= '<input type="hidden" name="page[]" value="'.$page['id'].'"></div>';

		if (!empty($page['children'])) {
			$element .= '<ul class="page-order children">';
			foreach ($page['children'] as $subpage) {
				$element .= self::list_element($subpage);
			}
			$element .= '</ul>';
		}

		$element .= '</li>';
		return $element;
	}

	public static function save_order()
	{

		if (empty($_POST['sb_nonce'])) return;
		if (!wp_verify_nonce($_POST['sb_nonce'], 'sb_page_order')) return '<div class="error"><p><strong>Något gick snett.</strong></p></div>';

		$post_array = Utils::post_array('page');
		if (empty($post_array)) return;

		$post_meta = Utils::post_string('post_meta');

		foreach ($post_array as $order => $post_id) {

			if ($post_meta == '_category_order') {

				update_post_meta($post_id, '_category_order', $order);

			} else {

				wp_update_post(array('ID' => $post_id, 'menu_order' => $order));

			}

		}

		return '<div class="updated fade message"><p><strong>Ordningen sparad.</strong></p></div>';

	}

	public static function post_meta($page)
	{

		$post_type = Utils::get_string('post_type');
		$args = wp_parse_args(self::get_registered_args($post_type), self::$default);

		$return = array(self::$status_data[$page['post_status']]);
		$return = apply_filters('sb_sortable_post_meta', $return, $page['id'], $post_type);

		return implode(', ', $return);

	}

	public static function add_category_post_meta($post_type)
	{

		$pages = get_posts(array(
		    'numberposts' => -1,
		    'post_type' => $post_type
			));

		foreach ($pages as $page) {
			add_post_meta($page->ID, '_category_order', null, true);
		}

	}

	public static function get_category_select($post_type, $select_label, $taxonomy)
	{

		// $categories = get_categories(array(
		// 	'post_type'		=> $post_type,
		// 	'taxonomy'		=> $taxonomy,
		// 	'hierarchical' => false
		// 	));

		$categories = get_terms(
			$taxonomy,
			array('parent' => 0)
		);

		if (empty($categories)) return false;

		$selected = Utils::get_string('term_id');
		$base_url = admin_url('edit.php?post_type='.$post_type.'&page=sb_'.$post_type.'_sort');

		$return = array();
		$return[] = '<select class="category-select" data-base-url="'.$base_url.'">';
		$return[] = '<option value="" '.selected($selected, '', false).'>'.$select_label.'</option>';

		foreach ($categories as $category) {
			$return[] = '<option value="'.$category->term_id.'" '.selected($selected, $category->term_id, false).'>'.$category->name.'</option>';
		}

		$return[] = '</select>';

		return implode("\n", $return);

	}

	public static function sort_helper()
	{

		$post_type = Utils::get_string('post_type');
		$args = wp_parse_args(self::get_registered_args($post_type), self::$default);
		extract($args);

		if (!$args['date_sort_helper']) return;
		if ($args['sub_pages']) return; // nested pages will mess up the sorter (for now)

		echo '<div class="sort-helper">';
		echo '<h3>Sorteringshjälp</h3>';

		$option = array();
		$option[] = '<select id="sort-helper-options" class="sort-help-select">';
		$option[] = '<option value="0" data-method="sortByDate" data-order="ASC">Efter datum (äldsta först)</option>';
		$option[] = '<option value="1" data-method="sortByDate" data-order="DESC">Efter datum (nyaste först)</option>';
		$option[] = '<option value="2" data-method="savedOrder" data-order="ASC">Efter sparad ordning</option>';

		$option = apply_filters('sb_sortable_sort_helper_methods', $option, $args);
		$option[] = '</select>';

		echo implode("\n", $option);
		echo '<a id="sort-helper-sort" class="button button-primary" href="javascript:void(0)">Sortera</a>';

		echo '</div>';

	}

}