<?php

namespace SB;

class NavMenu {

	public static $nav_items = array();

	public static $metabox_id = 'sb-nav-menu';

	public static $metabox_list_id = 'sb-custom-nav-menu';


	public static function add_nav_item($args)
	{
		$defaults = array(
			'type' => 'uri',
			'addmenulabel' => $args['label']
		);

		$args = wp_parse_args($args, $defaults);

		self::$nav_items[$args['id']] = $args;
	}

	public static function init()
	{

		add_action('admin_enqueue_scripts', array(__CLASS__, 'javascript'));
		add_action('admin_init', array(__CLASS__, 'add_meta_box'));
		add_filter('wp_setup_nav_menu_item', array(__CLASS__, 'setup_nav_item'));
		add_filter('wp_nav_menu_objects', array( __CLASS__, 'make_current' ) );
		add_action('wp_ajax_navmenu_add_custom', array(__CLASS__, 'wp_ajax_navmenu_add_custom'));
	}

	public static function javascript()
	{
		wp_register_script('sb-navmenu', Utils::get_bundle_uri('NavMenu').'/js/navmenu.js', 'jquery', '1', true);
		wp_enqueue_script('sb-navmenu');
	}


	public static function add_meta_box()
	{

		add_meta_box(
			self::$metabox_id,
			'Temaspecifika',
			array( __CLASS__, 'metabox' ),
			'nav-menus',
			'side',
			'high'
		);
	}

	public static function metabox()
	{
		global $nav_menu_selected_id;


		$html = '<ul id="'. self::$metabox_list_id .'">';
        foreach (self::$nav_items as $id => $nav_item) {
			$html .= sprintf(
				'<li><label><input type="checkbox" value ="%s" />&nbsp;%s</label></li>',
				esc_attr( $id ),
				esc_attr( $nav_item['addmenulabel'] )
			);

        }
        $html .= '</ul>';

        // 'Add to Menu' button
        $html .= '<p class="button-controls"><span class="add-to-menu">';
        $html .= '<input type="submit"'. disabled( $nav_menu_selected_id, 0, false ) .' class="button-secondary
                  submit-add-to-menu right" value="Lägg till i menyn"
                  name="add-sb-custom-navmenu" id="add-sb-custom-navmenu" />';
        $html .= '<span class="spinner"></span>';
        $html .= '</span></p>';

        $html .= wp_nonce_field('sb_navmenu', 'sb_nav_menu_nonce', false, false);

        echo $html;
	}

	public static function wp_ajax_navmenu_add_custom()
	{

		$nonce = $_REQUEST['nonce'];

		if (!wp_verify_nonce($nonce, 'sb_navmenu')) {
			console('Can not verify');
			die();
		}

		$nav_items = empty($_POST['nav_menu_items']) ? false : $_POST['nav_menu_items'];
		if (!$nav_items) die();

		$item_ids = array();
		foreach ($nav_items as $nav_item) {
			if (!isset(self::$nav_items[$nav_item])) continue;
			$nav_item_data = self::$nav_items[$nav_item];
			$menu_item_data = false;

			if ('uri' == $nav_item_data['type']) {
				$menu_item_data = array(
					'menu-item-title'  => esc_attr($nav_item_data['label']),
					'menu-item-type'   => 'sb_nav_item',
					'menu-item-object' => esc_attr($nav_item_data['id']),
					'menu-item-url'    => $nav_item_data['uri']
				);
			} elseif ('custom_post_type' == $nav_item_data['type']) {
				$post_type_obj = get_post_type_object( $nav_item_data['id'] );

				if(!$post_type_obj) continue;

				$nav_label = !empty($nav_item_data['label']) ? $nav_item_data['label'] : $post_type_obj->labels->name;

				$menu_item_data= array(
					'menu-item-title'  => esc_attr($nav_label),
					'menu-item-type'   => 'sb_nav_item',
					'menu-item-object' => esc_attr($nav_item_data['id']),
					'menu-item-url'    => get_post_type_archive_link($nav_item_data['id'])
				);
			}

			if (!$menu_item_data) continue;
        	$item_ids[] = wp_update_nav_menu_item( 0, 0, $menu_item_data );
		}

		is_wp_error( $item_ids ) AND die( '-1' );

		foreach ( (array) $item_ids as $menu_item_id ) {
			$menu_obj = get_post( $menu_item_id );
			if ( ! empty( $menu_obj->ID ) ) {
				$menu_obj = wp_setup_nav_menu_item( $menu_obj );
				$menu_obj->label = $menu_obj->title;
				$menu_items[] = $menu_obj;
			}
		}


        require_once ABSPATH.'wp-admin/includes/nav-menu.php';

        if (!empty($menu_items)) {
			$args = array(
				'after'       => '',
				'before'      => '',
				'link_after'  => '',
				'link_before' => '',
				'walker'      => new \Walker_Nav_Menu_Edit
			);
			echo walk_nav_menu_tree($menu_items, 0, (object) $args);
        }

		die();
	}

	public static function setup_nav_item( $menu_item ) {

		if ($menu_item->type !== 'sb_nav_item')
			return $menu_item;

		$nav_item = self::$nav_items[$menu_item->object];
		if ('uri' == $nav_item['type']) {
			$menu_item->url = $nav_item['uri'];
			$menu_item->url = apply_filters('sb_menu_item_uri_link', $menu_item->url);

		} elseif ('custom_post_type' == $nav_item['type']) {

			$post_type_obj = get_post_type_object( $nav_item['id'] );
			if(!$post_type_obj) continue;

			$menu_item->url = get_post_type_archive_link($nav_item['id']);

		}

		$menu_item->type_label = $nav_item['addmenulabel'];
		return $menu_item;
	}

	public static function make_current( $items ) {

		foreach ( $items as $item ) {
			if ($item->type !== 'sb_nav_item')	continue;

			if (!isset(self::$nav_items[$item->object])) continue;
			$nav_item = self::$nav_items[$item->object];
			$has_current = false;

			if ('custom_post_type' == $nav_item['type']) {
				if (is_post_type_archive($nav_item['id']) OR is_singular($nav_item['id'])) {
					$item->current = true;
					$item->classes[] = 'current-menu-item';
					$has_current = true;
				}
			} elseif('uri' == $nav_item['type']) {
				if ($nav_item['uri'] == $_SERVER['REQUEST_URI']) {
					$item->current = true;
					$item->classes[] = 'current-menu-item';
					$has_current = true;
				}
			}

			if ($has_current) {
				$active_anc_item_ids = self::get_item_ancestors( $item );

				foreach ( $items as $key => $parent_item ) {
					$classes = (array) $parent_item->classes;

					// If menu item is the parent
					if ( $parent_item->db_id == $item->menu_item_parent ) {
						$classes[] = 'current-menu-parent';
						$items[ $key ]->current_item_parent = true;
					}

					// If menu item is an ancestor
					if ( in_array( intval( $parent_item->db_id ), $active_anc_item_ids ) ) {
						$classes[] = 'current-menu-ancestor';
						$items[ $key ]->current_item_ancestor = true;
					}

					$items[ $key ]->classes = array_unique( $classes );
				}
			}
		}

		return $items;
	}

	public static function get_item_ancestors( $item ) {
		$anc_id = absint( $item->db_id );

		$active_anc_item_ids = array();
		while (
			$anc_id = get_post_meta( $anc_id, '_menu_item_menu_item_parent', true )
			AND ! in_array( $anc_id, $active_anc_item_ids )
		)
		$active_anc_item_ids[] = $anc_id;

		return $active_anc_item_ids;
	}

	public static function get_custom_item_data( $item_id )
	{

		if (empty(self::$nav_items)) return false;

		foreach (self::$nav_items as $data) {
			if ($item_id == $data['id']) return $data;
		}

		return $false;

	}

}