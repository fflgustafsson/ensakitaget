<?php

namespace SB;

use SB\Utils;
use SB\Forms\Fields;

Modules::init();

class Modules {

	public static $version = 1;

	public static $cache = array();

	public static $config = array(
		'add_meta_box'		=> false, // false == don't add it, true == all post_types but modules || array (post_types)
		'show_handle_page'	=> true,
		'show_visiblity'	=> true,
		'meta_box_title'	=> 'Moduler'
		);

	public static $data = array(
		'name'		=> 'Moduler',
		'singular'	=> 'Modul',
		'slug'		=> 'module'
		);

	public static function init()
	{

		add_action('init', array(__CLASS__, 'register'));
		add_action('admin_menu', array(__CLASS__, 'handle_page'));
		add_filter('post_row_actions', array(__CLASS__, 'remove_view_link'));

		add_action('admin_head-post-new.php', array(__CLASS__, 'hide_preview'));
		add_action('admin_head-post.php', array(__CLASS__, 'hide_preview'));

		add_action('manage_'.self::$data['slug'].'_posts_columns', array(__CLASS__, 'add_posts_columns'));
		add_action('manage_'.self::$data['slug'].'_posts_custom_column', array(__CLASS__, 'add_post_column_data'), 10, 2);

		add_action('wp_ajax_save_new_module_set', array(__CLASS__, 'save_set_ajax'));
		add_action('wp_ajax_delete_module_set', array(__CLASS__, 'delete_set_ajax'));

		// Frontend
		add_action('template_redirect', array(__CLASS__, 'set_active_types'));
		add_action('wp_enqueue_scripts', array(__CLASS__, 'enqueue_scripts'));

		// Admin statics
		add_action('admin_enqueue_scripts', array(__CLASS__, 'javascript'));
		add_action('admin_print_styles', array(__CLASS__, 'stylesheet'));

		add_action('add_meta_boxes', array(__CLASS__, 'add_meta_box'));

	}

	public static function config($args)
	{

		self::$config = wp_parse_args($args, self::$config);

	}

	public static function set_active_types()
	{

		global $post, $_active_modules;

		if (empty($post->ID)) return;

		$post_modules = get_post_meta($post->ID, '_modules', true);

		if (!empty($post_modules)) {
			foreach ($post_modules as $post_module) {
				$module = get_post($post_module);
				$_active_modules[] = str_replace('module-metabox-', '', get_post_meta($module->ID, '_module_type', true));
			}

			self::$cache = $post_modules;

		} else {

			$modules = self::get_modules();
			$modules = apply_filters('SB_Modules_array', $modules);

			foreach ($modules as $module) {
				$_active_modules[] = str_replace('module-metabox-', '', get_post_meta($module->ID, '_module_type', true));
			}

			self::$cache = $modules;

		}

		if (empty($_active_modules)) return;

		$_active_modules = array_unique($_active_modules);

	}

	public static function javascript()
	{

		wp_register_script('sb-modules', Utils::get_bundle_uri('Modules').'/js/modules.js', array('jquery'), self::$version, true);
		wp_enqueue_script('sb-modules');

		global $_module_types;
		wp_localize_script('sb-modules', 'types', $_module_types);

		if ('module' == get_post_type()) {
			wp_dequeue_script('autosave');
		}

	}

	public static function stylesheet()
	{

		wp_register_style('sb-modules', Utils::get_bundle_uri('Modules').'/css/modules.css', false, self::$version);
		wp_enqueue_style('sb-modules');

	}

	public static function enqueue_scripts()
	{

		global $_module_types, $_active_modules;

		foreach ($_module_types as $module) {

			if (in_array($module['slug'], $_active_modules) && !empty($module['javascript'])) {

				$javascript = (is_array($module['javascript'])) ? $module['javascript'] : false;

				if (!is_array($module['javascript'])) {
					debug('You must supply the javascript parameter with a valid set for https://codex.wordpress.org/Function_Reference/wp_enqueue_script as a non-associative array in the correct order: array( $handle, $src, $deps, $ver, $in_footer )');
					continue;
				}

				if (!is_array($javascript[0])) {
					$javascript = array();
					$javascript[] = $module['javascript'];
				}

				foreach ($javascript as $id => $variables) {

					if (!is_array($variables)) {
						$variables = $javascript;
					}

					$defaults = array('sb-modules-'.$module['slug'].'-'.$id, 'src', array('jquery'), '1', true);
					$args = array_intersect_key($variables + $defaults, $defaults);

					list($handle, $src, $deps, $ver, $in_footer) = $args;

					wp_register_script($handle, $src, $deps, $ver, $in_footer);
					wp_enqueue_script($handle);

				}

			}

		}

	}

	public static function register()
	{

		$lc_single = strtolower(self::$data['singular']);

		$labels = array(
			'name' 				 => self::$data['name'], // general name for the post type, usually plural.
			'singular_name' 	 => self::$data['singular'], // name for one object of this post type.
			'menu_name'			 => self::$data['name'], // the menu name text. This string is the name to give menu items.
			'name_admin_bar'	 => self::$data['name'], // name given for the "Add New" dropdown on admin bar.
			'all_items'			 => 'Alla '.self::$data['name'], // the all items text used in the menu.
			'add_new'			 => 'Lägg till '.$lc_single, // the add new text. The default is "Add New" for both hierarchical and non-hierarchical post types.
			'add_new_item'		 => 'Lägg till '.$lc_single, // the add new item text.
			'edit_item'			 => 'Redigera '.$lc_single, // the edit item text. In the UI, this label is used as the main header on the post's editing panel.
			'new_item'			 => 'Ny '.$lc_single, // the new item text.
			'view_item'			 => 'Visa '.$lc_single, // the view item text.
			'search_items'		 => 'Sök '.$lc_single, // the search items text.
			'not_found'			 => 'Inga '.$lc_single, // the not found text.
			'not_found_in_trash' => 'Inga '.$lc_single.' i papperskorgen', // the not found in trash text.
			'parent_item_colon'  => 'Förälder', // the parent text.

  		);

		$args = array(
			'labels' 			 => $labels,
			'public' 			 => true,
			'exclude_from_search'=> false,
			'publicly_queryable' => true,
			'show_ui' 			 => true,
			'show_in_nav_menus'  => false,
			'show_in_menu' 		 => true,
			'show_in_admin_bar'	 => false,
			'menu_position' 	 => 2,
			'menu_icon'			 => 'dashicons-screenoptions', // melchoyce.github.io/dashicons/
			'capability_type'    => 'page',
			'capabilities' 		 => array(),
			'map_meta_cap' 		 => true,
			'hierarchical' 		 => false,
			'supports' 			 => array('title'), // default fields
			'register_meta_box_cb'	=> array(__CLASS__, 'metabox'),
			// 'taxonomies'		 => array(),
			'has_archive' 		 => false,
			'rewrite' 			 => false,
			'query_var' 		 => false,
			'can_export'		 => false,

		);

		// Supports
		// 'title', 'editor' (content), 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'revisions', 'page-attributes', 'post-formats'

		// http://codex.wordpress.org/Function_Reference/register_post_type
		register_post_type(self::$data['slug'], $args);

	}

	public static function add_meta_box()
	{

		if (!self::$config['add_meta_box']) return;

		global $post;
		if (empty($post->post_type)) return;
		if ('module' == $post->post_type) return;

		$add_meta_box = false;

		if (is_bool(self::$config['add_meta_box'])) {

			$add_meta_box = true;

		} elseif (is_array(self::$config['add_meta_box'])) {

			if (in_array($post->post_type, self::$config['add_meta_box'])) {
				$add_meta_box = true;
			}

		}

		if (!$add_meta_box) return;

		add_meta_box(
			'sb_set_modules', // id
			self::$config['meta_box_title'], // title
			array(__CLASS__, 'meta_box_form'), // callback function
			$post->post_type, // post_type
			'normal', // context 'normal', 'advanced', or 'side'
			'core' // priority 'high', 'core', 'default' or 'low'
		);

	}

	public static function meta_box_form($post)
	{

		echo Fields::posts(array(
			'name'	=> '_modules',
			'label' => '',
			'auto_value' => true,
			'post_type' => array('module'),
			'meta_key' => '_module_description',
			'multiple' => true
			));

	}

	public static function hide_preview()
	{

		global $post_type;

		if (self::$data['slug'] != $post_type) return;
		echo '<style type="text/css">#post-preview, #view-post-btn, .misc-pub-post-status, .misc-pub-visibility { display: none; }</style>';

	}

	public static function remove_view_link($action)
	{

		unset($action['inline hide-if-no-js']);
		// unset($action['view']);
		return $action;

	}

	public static function add_posts_columns($columns)
	{

		$new_columns = array();
		foreach ($columns as $key => $value) {
			$new_columns[$key] = $value;
			if ($key == 'title') {
				$new_columns['desc'] = 'Beskrivning';
				$new_columns['type'] = 'Typ';

				// show_visiblity
				if (self::$config['show_visiblity']) {
					$new_columns['show'] = 'Synlighet';
				}

			}
		}

	    return $new_columns;

	}

	public static function add_post_column_data($column, $post_id)
	{

		global $_module_types;

		switch ($column) {

			case 'desc':
				echo get_post_meta($post_id, '_module_description', true);
				break;

			case 'show':
				if (1 == get_post_meta($post_id, '_module_visibility', true)) {
					echo '<span class="dot active"></span>';
				} else {
					echo '<span class="dot"></span>';
				}
				break;

			case 'type':
				$type = get_post_meta($post_id, '_module_type', true);

				$select = self::get_select_modules();

				if (!empty($select[$type])) {
					echo $select[$type];
				}

				break;

			default:
				break;
		}

	}

	public static function handle_page()
	{

		if (self::$config['show_handle_page']) {
			add_submenu_page('edit.php?post_type='.self::$data['slug'], 'Hantera '.strtolower(self::$data['name']), 'Hantera '.strtolower(self::$data['name']), 'edit_others_posts', 'handle_modules', array(__CLASS__, 'handle_form'));
		}

	}

	public static function metabox()
	{

		add_meta_box('module-settings', 'Inställningar', array(__CLASS__, 'form'), self::$data['slug'], 'normal', 'core');

		global $_module_types;

		foreach ($_module_types as $type) {

			$id = 'module-metabox-'.$type['slug'];
			add_meta_box($id, $type['name'], $type['form'], self::$data['slug'], 'normal', 'core', $type);

		}

	}

	public static function get_select_modules()
	{

		global $_module_types;

		if (empty($_module_types) && !is_array($_module_types)) {
			debug('ERROR: You need to call register_type');
			return false;
		}

		$select = array();
		foreach ($_module_types as $type) {
			$select['module-metabox-'.sanitize_title($type['name'])] = $type['name'];
		}

		return $select;

	}

	public static function form($post)
	{

		global $_module_types;

		$select = self::get_select_modules();

		echo '<div class="right">';

		if (self::$config['show_visiblity']) {

			echo Fields::toggle(array(
				'name' => '_module_visibility',
				'label' => 'Synlighet',
				'auto_value' => true,
				'on' => 'På',
				'off' => 'Av',
				'class' => 'module-visibility'
				));

		}

		echo '</div><div class="left">';

		echo Fields::textarea(array(
			'name'	=> '_module_description',
			'label'	=> 'Beskrivning',
			'auto_value' => true,
			'rows' => 2,
			'class' => 'module-description'
			));

		echo Fields::select(array(
			'name'	=> '_module_type',
			'label'	=> 'Typ',
			'auto_value' => true,
			'data' => $select,
			'add_empty' => 'Välj...',
			'class' => 'module-type',
			'description' => '&nbsp;'
			));

		echo '</div>';

	}

	public static function save_set_ajax()
	{

		if (!Utils::is_ajax_request()) return false;

		$name = Utils::post_string('name');
		$save_set = Utils::post_array('set');

		if (empty($save_set)) return false;

		$new = array();

		foreach ($save_set as $module) {
			$new[][$module['id']] = $module['visible'];
		}

		$data = get_option('_sb_modules_saved_sets');

		if (!is_array($data)) {
			$data = array();
		}

		$new_object = array(trim($name) => $new);
		array_push($data, $new_object);

		$save = update_option('_sb_modules_saved_sets', $data);

		$json_set = json_encode($new, JSON_FORCE_OBJECT);

		if ($save) {
			Utils::return_JSON(array('status' => 'OK', 'message' => 'Sparat.', 'set' => $json_set));
		} else {
			Utils::return_JSON(array('status' => 'ERROR', 'message' => 'Något gick snett.'));
		}

	}

	public static function delete_set_ajax()
	{

		if (!Utils::is_ajax_request()) return false;

		$name = Utils::post_string('name');

		if (empty($name)) return false;

		$data = get_option('_sb_modules_saved_sets');

		foreach ($data as $id => $set) {
			if (key($set) == $name) {
				unset($data[$id]);
			}
		}

		$save = update_option('_sb_modules_saved_sets', $data);

		if ($save) {
			Utils::return_JSON(array('status' => 'OK', 'message' => 'Raderat.'));
		} else {
			Utils::return_JSON(array('status' => 'ERROR', 'message' => 'Något gick snett.'));
		}

	}

	public static function handle_save()
	{

		if (empty($_POST['sb_nonce'])) return;
		if (!wp_verify_nonce($_POST['sb_nonce'], '_sb_modules')) return '<div class="error"><p><strong>Något gick snett.</strong></p></div>';
		if (empty($_POST['modules'])) return;

		$modules = Utils::post_array('modules');

		foreach ($modules as $order => $data) {

			$id = key($data);
			$is_visible = $data[$id];

			wp_update_post(array('ID' => $id, 'menu_order' => $order));
			update_post_meta($id, '_module_visibility', $is_visible);

		}

		return '<div id="message" class="fade updated below-h2"><p>Sparat.</p></div>';

	}

	public static function handle_form()
	{

		$message = self::handle_save();

		$classes = array('wrap', 'sortable-modules');

		?>

		<div class="<?php echo implode(' ', $classes) ?>">
			<h2>Hantera moduler</h2>
			<?php echo $message; ?>
			<p class="description">
				Sortera, aktivera och inaktivera moduler nedan.<br />
			</p>
			<form method="post" id="page-order-form" class="options sb-modules">
				<?php wp_nonce_field('_sb_modules', 'sb_nonce'); ?>
				<div class="modules-wrapper">
					<ul class="sort-modules">
					<?php

						$modules = self::get_modules(false);

						foreach ($modules as $module) {

							$edit_url = admin_url('post.php?post='.$module->ID.'&action=edit');

							echo '<li data-id="'.$module->ID.'"><div>';
							echo '<a class="post_title" href="'.$edit_url.'">'.$module->post_title.'</a>';

							if (self::$config['show_visiblity']) {

								echo Fields::toggle(array(
									'name' => 'modules[]['.$module->ID.']',
									'label' => '',
									'value' => $module->is_visible,
									'class' => 'module-visibility',
									'theme' => 'mini',
									'print_label' => false
								));

							}

						}

					?>
					</ul>
				</div>
				<p class="submit">
					<input id="modules-order" class="button button-secondary" disabled="enabled" name="save" type="submit" value="Publicera" />
					<span class="spinner"></span>
				</p>
			</form>
			<div class="module-sets">

				<h3>Uppsättningar</h3>
				<?php

					$data = get_option('_sb_modules_saved_sets');
					$output = array();

					if (!empty($data)) :

						foreach ($data as $set) {

							$output[] = '<li data-id="'.key($set).'" data-set="'.esc_attr(json_encode(current($set), JSON_FORCE_OBJECT)).'">';
							$output[] = '<a href="javascript:void(0)" class="load-module-set">'.key($set).'</a>';
							$output[] = '<span><a class="delete delete-module-set" href="javascript:void(0)">Radera</a></span>';
							$output[] = '<span><a class="show" href="/?preview_set='.urlencode(key($set)).'">Visa</a></span>';
							$output[] = '</li>';

						}

					endif;

					$classes = array('module-set-list');
					$classes[] = (empty($data)) ? 'hidden' : false;

				?>
				<script id="new-module-set" type="sb/template">
					<li data-id="{{id}}">
						<a href="javascript:void(0)" class="load-module-set">{{name}}</a>
						<span>
							<a class="delete delete-module-set" href="javascript:void(0)">Radera</a>
						</span>
						<span>
							<a class="show" href="/?preview_set={{url}}">Visa</a>
						</span>
					</li>
				</script>
				<ul class="<?php echo implode(' ', $classes); ?>">
					<?php echo implode("\n", $output); ?>
				</ul>
				<div class="form-wrapper">
					<input class="set-name" id="module-set-name" type="text" value="" name="module-set-name" placeholder="Namn">
					<span id="new-module-save" class="spinner outside"></span>
					<a id="save-module-set" href="javascript:void(0)" class="button button-secondary save-new">Spara ny uppsättning</a>
				</div>

			</div>
		</div>

		<?php

	}

	public static function get_modules($hide_inactive = true)
	{

		if (!empty(self::$cache)) return self::$cache;

		$args = array(
			'numberposts'	=> -1,
			'order'			=> 'ASC',
			'orderby'		=> 'menu_order',
			'post_type'		=> 'module',
			);

		if ($hide_inactive) {
			$args = array_merge($args, array('meta_query' => array(array('key' => '_module_visibility', 'value' => 1))));
		}

		// preview
		$preview_set = Utils::get_string('preview_set');
		if (!empty($preview_set)) {

			$data = get_option('_sb_modules_saved_sets');
			foreach ($data as $set) {

				if ($preview_set == strtolower(urlencode(key($set)))) {
					$preview_set = $set;
					break;
				}

			}

			if (is_array($preview_set) && is_user_logged_in()) {

				$modules = array();

				foreach (current($preview_set) as $module) {

					if (current($module) == 1) {
						$modules[] = get_post(key($module));
					}

				}

			}

		} else {

			$modules = get_posts($args);

		}

		if (empty($modules)) return false;

		self::$cache = $modules;

		foreach ($modules as $module) {
			$module->is_visible = get_post_meta($module->ID, '_module_visibility', true);
			$module->module_type = str_replace('module-metabox-', '', get_post_meta($module->ID, '_module_type', true));
		}

		return $modules;

	}

	public static function render()
	{

		$modules = self::get_modules();
		$modules = apply_filters('SB_Modules_array', $modules);

		// loop render
		foreach ($modules as $module) {

			$status = self::render_module($module);

			if (!$status) {
				error_log('ERROR: Module was unable to render');
			}

		}

	}

	public static function render_page($post_id = false)
	{

		if (!$post_id) {
			global $post;
			if (is_numeric($post->ID)) {
				$post_id = $post->ID;
			} else {
				return false;
			}
		}

		if (empty(self::$cache)) {
			$modules = get_post_meta($post_id, '_modules', true);
		} else {
			$modules = self::$cache;
		}

		foreach ($modules as $module) {
			$obj = get_post($module);
			if (empty($obj)) return false;
			self::render_module($obj);
		}

	}

	public static function render_module($module) // object
	{

		global $_module_types, $_active_modules;

		if (get_class($module) != 'WP_Post') return false;

		if (empty($module->module_type)) {
			$module->module_type = str_replace('module-metabox-', '', get_post_meta($module->ID, '_module_type', true));
		}

		$method = false;

		foreach ($_module_types as $type) {
			if ($module->module_type == $type['slug']) {
				$method = $type['template'];
				break;
			}
		}

		if (!empty($method)) {

			if (is_callable($method)) {
				call_user_func($method, $module);
				return true;
			}

			if (file_exists($method)) {
				include($method);
				return true;
			}

		}

		return false;

	}

	public static function validate_args($args)
	{

		if (empty($args['name'])) {
			debug('ERROR: Module must be named');
			return false;
		}

		if (empty($args['template'])) {

			if (is_callable(array($args['name'], 'render'))) {
				$args['template'] = array($args['name'], 'render');
			}

			if (is_callable(__NAMESPACE__.'\\'.$args['name'].'::render')) {
				$args['template'] = __NAMESPACE__.'\\'.$args['name'].'::render';
			}

			if (file_exists(TEMPLATEPATH.'/modules/template-'.strtolower($args['name']).'.php')) {
				$args['template'] = TEMPLATEPATH.'/modules/template-'.strtolower($args['name']).'.php';
			}

			if (empty($args['template'])) {
				debug('ERROR: Module '.$args['name'].' has no way of rendering');
				return false;
			}

		}

		if (empty($args['init'])) {

			$args['init'] = false;

			if (is_callable(array($args['name'], 'init'))) {
				$args['init'] = array($args['name'], 'init');
			}

			if (is_callable(__NAMESPACE__.'\\'.$args['name'].'::init')) {
				$args['init'] = __NAMESPACE__.'\\'.$args['name'].'::init';
			}

		}

		if (empty($args['form'])) {

			if (is_callable(array($args['name'], 'form'))) {
				$args['form'] = array($args['name'], 'form');
			}

			if (is_callable(__NAMESPACE__.'\\'.$args['name'].'::form')) {
				$args['form'] = __NAMESPACE__.'\\'.$args['name'].'::form';
			}

			if (empty($args['form'])) {
				debug('ERROR: Module '.$args['name'].' has no form');
				return false;
			}

		}

		if (empty($args['description'])) {
			$args['description'] = false;
		}

		$args['slug'] = sanitize_title($args['name']);

		return $args;

	}

	public static function register_type($args)
	{

		global $_module_types;

		$args = self::validate_args($args);

		if (!$args) return false;

		// calling init if callable
		if ($args['init']) {
			call_user_func($args['init']);
		}

		if (empty($_module_types)) {
			$_module_types = array();
		}

		$_module_types[] = $args;

	}

}