<?php

namespace SB;

use SB\Utils;
use \WP_User_Query;

// SB\Sortable::register(array(
//  'post_type'         => 'page',
//  'sub_pages'         => true,
//  ));

class Sortable
{

    public static $dependencies = array(
        'Utils' => '2.0'
        );

    public static $sorters = array();

    public static $default = array(
        'post_type'         => 'page',
        'menu_name'         => 'Sortera ordning',
        'headline'          => 'Sortera ordning',
        'description'       => 'Sortera sidordning nedan genom dra och släpp.',
        'post_type_name'    => 'Sidor',
        'sub_pages'         => false,
        'post_status'       => array('publish', 'private', 'draft', 'pending', 'future'),
        'class'             => false,
        'by_category'       => false,
        'taxonomy'          => 'category',
        'select_label'      => 'Välj kategori...',
        'date_sort_helper'  => false
        );

    public static function init()
    {

        add_action('admin_enqueue_scripts', array(__CLASS__, 'javascript'));
        add_action('admin_print_styles', array(__CLASS__, 'stylesheet'));

    }

    public static function register($args)
    {

        add_action('admin_menu', array(__CLASS__, 'addSortPage'));
        self::$sorters[] = $args;

    }

    public static function addSortPage()
    {

        foreach (self::$sorters as $sorter) {
            if (empty($sorter['post_type'])) {
                continue;
            }

            $args = wp_parse_args($sorter, self::$default);
            extract($args);

            if ('wp_user' == $post_type) {
                add_users_page(
                    $menu_name,
                    $menu_name,
                    'edit_others_posts',
                    'sb_'.$post_type.'_sort',
                    array(__CLASS__, 'form')
                );
            } else {
                add_submenu_page(
                    'edit.php?post_type='.$post_type,
                    $menu_name,
                    $menu_name,
                    'edit_others_posts',
                    'sb_'.$post_type.'_sort',
                    array(__CLASS__, 'form')
                );
            }

        }

    }

    public static function javascript()
    {
        wp_enqueue_script('jquery-ui-sortable');
        wp_register_script('sb-page-order-js', Utils::getBundleUri('Sortable').'/js/sortable.js', 'jquery', '2', true);
        wp_enqueue_script('sb-page-order-js');
    }

    public static function stylesheet()
    {
        wp_register_style('sb-page-order-css', Utils::getBundleUri('Sortable').'/css/sortable.css', false, '2');
        wp_enqueue_style('sb-page-order-css');
    }

    public static function getRegisteredArgs($post_type)
    {

        foreach (self::$sorters as $id => $args) {
            if ($args['post_type'] == $post_type) {
                return self::$sorters[$id];
            }
        }

        return false;

    }

    public static function getPostTypeFromPage()
    {

        $post_type = Utils::getString('post_type');

        if (empty($post_type) && basename(Utils::serverString('PHP_SELF')) == 'users.php') {
            $post_type = 'wp_user';
        }

        return $post_type;

    }

    public static function form()
    {

        self::saveOrder();

        $post_type = self::getPostTypeFromPage();

        $args = wp_parse_args(self::getRegisteredArgs($post_type), self::$default);
        extract($args);

        if ($by_category) {
            self::addCategoryPostMeta($post_type);
        }

        $category_select = ($by_category) ? self::getCategorySelect($post_type, $select_label, $taxonomy) : false;
        $post_meta = ($by_category) ? '_category_order' : 'menu_order'; // FIXME get from settings?
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
                <input type="hidden" name="post_type" value="<?php echo $post_type; ?>">
                <input type="hidden" name="post_meta" value="<?php echo $post_meta; ?>">
                <h3><?php echo $post_type_name; ?><?php echo $sort_sub; ?></h3>
                <?php echo $category_select; ?>
                <div class="page-wrapper">
                    <?php self::printList($by_category, $taxonomy); ?>
                </div>
                <?php self::sortHelper(); ?>
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
        'draft' => 'Utkast',
        'pending' => 'Väntande',
        'future' => 'Tidsinställd'
        );

    public static function getPagesHierachy($page_id = 0, $by_category = false, $taxonomy = false, $category = false)
    {

        $post_type = self::getPostTypeFromPage();
        $args = wp_parse_args(self::getRegisteredArgs($post_type), self::$default);

        if (!$args['sub_pages'] && $page_id > 0) {
            return array();
        }

        $query = array(
            'post_type' => $post_type,
            'post_status' => $args['post_status'],
            'post_parent' => $page_id,
            'posts_per_page' => -1
            );

        $query['order'] = 'ASC';
        $query['orderby'] = 'menu_order';

        if ($by_category) {
            if ($category == false) {
                return array();
            }

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

        if ($post_type == 'wp_user') {
            return self::getWpUsers();
        } else {
            $pages = get_posts($query);
        }

        $return = array();
        foreach ($pages as $i => $page) {
            $return[$page->ID] = array(
                'order' => $i,
                'id' => $page->ID,
                'post_title' => _draft_or_post_title($page->ID),
                'children' => self::getPagesHierachy($page->ID, $by_category, $taxonomy, $category),
                'post_status' => $page->post_status,
                '_category_order' => get_post_meta($page->ID, '_category_order', true)
                );
        }

        return $return;

    }

    public static function printList($by_category, $taxonomy)
    {

        $category = Utils::getString('term_id');

        $pages = self::getPagesHierachy(0, $by_category, $taxonomy, $category);
        echo '<ul class="page-order base">';
        foreach ($pages as $id => $page) {
            echo self::listElement($page);
        }
        echo '</ul>';

    }

    public static function listElement($page)
    {

        $has_children = (!empty($page['children'])) ? '<a class="child-arrow" href="#"></a>' : false;

        $page_title = apply_filters(
            'sb_sortable_post_title',
            $page['post_title'],
            $page['id'],
            get_post_type($page['id'])
        );

        $data = array();
        $data[] = 'data-id="'.$page['id'].'"';
        $data[] = 'data-date="'.get_the_date('U', $page['id']).'"';
        $data[] = 'data-order="'.$page['order'].'"';
        $data = apply_filters('sb_sortable_post_data', $data, $page['id'], get_post_type($page['id']));

        $element  = '<li '.implode(' ', $data).'>';
        $element .= '<div class="page"><a class="post_title" href="'.admin_url('post.php?post='.$page['id'].'&action=edit').'">'.$page_title.'</a>'.$has_children;
        $element .= '<span>'.self::postMeta($page).'</span>';
        $element .= '<input type="hidden" name="page[]" value="'.$page['id'].'"></div>';

        if (!empty($page['children'])) {
            $element .= '<ul class="page-order children">';
            foreach ($page['children'] as $subpage) {
                $element .= self::listElement($subpage);
            }
            $element .= '</ul>';
        }

        $element .= '</li>';
        return $element;
    }

    public static function saveOrder()
    {

        if (empty($_POST['sb_nonce'])) {
            return;
        }

        if (!wp_verify_nonce($_POST['sb_nonce'], 'sb_page_order')) {
            return '<div class="error"><p><strong>Något gick snett.</strong></p></div>';
        }

        $post_array = Utils::postArray('page');
        if (empty($post_array)) {
            return;
        }

        $post_meta = Utils::postString('post_meta');
        $post_type = Utils::postString('post_type');

        if ($post_type == 'wp_user') {
            foreach ($post_array as $order => $user_id) {
                update_user_meta($user_id, '_sb_user_order', intval($order));
            }
        } else {
            foreach ($post_array as $order => $post_id) {
                if ($post_meta == '_category_order') {
                    update_post_meta($post_id, '_category_order', $order);
                } else {
                    wp_update_post(array('ID' => $post_id, 'menu_order' => $order));
                }
            }
        }

        return '<div class="updated fade message"><p><strong>Ordningen sparad.</strong></p></div>';

    }

    public static function postMeta($page)
    {

        $post_type = Utils::getString('post_type');
        $args = wp_parse_args(self::getRegisteredArgs($post_type), self::$default);

        if (in_array($page['post_status'], self::$status_data)) {
            $return = array(self::$status_data[$page['post_status']]);
        } else {
            $return = array($page['post_status']);
        }

        $return = apply_filters('sb_sortable_post_meta', $return, $page['id'], $post_type);

        return implode(', ', $return);

    }

    public static function addCategoryPostMeta($post_type)
    {

        $pages = get_posts(array(
            'numberposts' => -1,
            'post_type' => $post_type
            ));

        foreach ($pages as $page) {
            add_post_meta($page->ID, '_category_order', null, true);
        }

    }

    public static function getCategorySelect($post_type, $select_label, $taxonomy)
    {

        // $categories = get_categories(array(
        //  'post_type'     => $post_type,
        //  'taxonomy'      => $taxonomy,
        //  'hierarchical' => false
        //  ));

        $categories = get_terms(
            $taxonomy,
            array('parent' => 0)
        );

        if (empty($categories)) {
            return false;
        }

        $selected = Utils::getString('term_id');
        $base_url = admin_url('edit.php?post_type='.$post_type.'&page=sb_'.$post_type.'_sort');

        $return = array();
        $return[] = '<select class="category-select" data-base-url="'.$base_url.'">';
        $return[] = '<option value="" '.selected($selected, '', false).'>'.$select_label.'</option>';

        foreach ($categories as $category) {
            $return[] = '<option value="'.$category->term_id.'" '.selected($selected, $category->term_id, false).'>';
            $return[] = $category->name.'</option>';
        }

        $return[] = '</select>';

        return implode("\n", $return);

    }

    public static function sortHelper()
    {

        $post_type = Utils::getString('post_type');
        $args = wp_parse_args(self::getRegisteredArgs($post_type), self::$default);
        extract($args);

        if (!$args['date_sort_helper']) {
            return;
        }

        if ($args['sub_pages']) {
            return; // nested pages will mess up the sorter (for now)
        }

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

    public static function getWpUsers()
    {

        $users = get_users(array(
            'orderby' => 'meta_value_num',
            'meta_key' => '_sb_user_order' // FIXME should be customizable
            ));

        $return = array();

        foreach ($users as $user) {
            $order = get_user_meta($user->ID, '_sb_user_order', true);

            $return[$user->ID] = array(
                'order' => $order,
                'id'    => $user->ID,
                'post_title' => $user->display_name,
                'children' => array(),
                'post_status' => translate_user_role(ucfirst(current($user->roles))),
                '_category_order' => false
                );

        }

        return $return;

    }
}
