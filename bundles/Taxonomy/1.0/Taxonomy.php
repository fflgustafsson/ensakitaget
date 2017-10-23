<?php

namespace SB;

use SB\Utils;
use SB\Forms\Fields;
use SB\Taxonomy\MetaData;

class Taxonomy
{

    public static $dependencies = array(
        'Utils' => '2.0',
        'Forms' => '2.0',
        'Media' => '2.0'
        );

    public static $taxonomies = array();
    public static $post_types = array();

    private static $defaults = array(
        'name'          => 'Kategorier',
        'singular'      => 'kategori',
        'slug'          => null,
        'rewrite'       => false,
        'hierarchical'  => false,
        'post_type'     => false,
        'show_all_label' => 'Alla kategorier',
        'hide_columns'  => array(),
        'meta_data'     => array()
        );

    private function __construct($args)
    {

        $args = wp_parse_args($args, self::$defaults);

        foreach ($args as $key => $value) {
            $this->$key = $value;
        }

        if (empty($this->slug)) {
            throw new \Exception('Taxonomy:: slug not set');
        }

        if (!$this->post_type) {
            throw new \Exception('Taxonomy:: post_type not set');
        } elseif (!is_array($this->post_type)) {
            $this->post_type = array($this->post_type);
        }

        foreach ($this->post_type as $post_type) {
            self::$post_types[$post_type] = $this->slug;
        }

        self::$taxonomies[] = $this->slug;

        if (!empty($this->meta_data)) {
            require_once('lib/MetaData.php');
            Taxonomy\MetaData::register($this->slug, $this->meta_data, $this->hide_columns);
        }

        // non-static
        add_action('init', array($this, 'init'));
        add_filter('add_meta_boxes', array($this, 'metabox'));
        add_action('save_post', array($this, 'savePost'));

        add_action('created_'.$this->slug, array($this, 'saveTerm'));
        add_action('edit_'.$this->slug, array($this, 'saveTerm'));

        add_filter('post_type_link', array($this, 'postTypeLink'), 1, 3);
        add_filter('term_link', array($this, 'termLink'), 10, 3);

        add_action('restrict_manage_posts', array($this, 'postTypeFilter'));

        add_filter('manage_edit-'.$this->slug.'_columns', array($this, 'columns'));

        // static
        add_action('admin_print_styles', array(__CLASS__, 'stylesheet'));

        return $this;

    }

    public static function register($args)
    {

        return new self($args);

    }

    public static function stylesheet()
    {

        wp_register_style('sb-taxonomy-css', Utils::getBundleUri('Taxonomy').'/css/taxonomy.css', false, 1);
        wp_enqueue_style('sb-taxonomy-css');

    }

    public function init()
    {



        $labels = array(
            'name'                      => $this->name,
            'singular_name'             => $this->singular,
            'menu_name'                 => $this->name,
            'all_items'                 => mb_strtolower($this->name),
            'edit_item'                 => 'Redigera '.$this->singular,
            'view_item'                 => 'Visa '.$this->singular,
            'update_item'               => 'Uppdatera '.$this->singular,
            'add_new_item'              => 'Lägg till ny '.$this->singular,
            'new_item_name'             => 'Ny '.$this->singular,
            'parent_item'               => null,
            'parent_item_colon'         => null,
            'search_items'              => 'Sök efter '.$this->singular,
            'popular_items'             => null,
            'separate_items_with_commas'=> null,
            'add_or_remove_items'       => null,
            'choose_from_most_used'     => null,
            'not_found'                 => 'Inga '.mb_strtolower($this->name)
            );

        $args = array(
            'labels'                    => $labels,
            'public'                    => true,
            'show_ui'                   => true,
            'show_in_nav_menus'         => true,
            'show_in_quick_edit'        => false,
            'show_tagcloud'             => false,
            'meta_box_cb'               => false,
            'show_admin_column'         => true,
            'hierarchical'              => $this->hierarchical,
            'update_count_callback'     => null,
            'rewrite'                   => $this->rewrite,
            'sort'                      => true,
            );

        // http://codex.wordpress.org/Function_Reference/register_taxonomy
        foreach ($this->post_type as $post_type) {
            register_taxonomy($this->slug, $post_type, $args);
            register_taxonomy_for_object_type($this->slug, $post_type);
        }

    }

    public function metabox()
    {

        foreach ($this->post_type as $post_type) {
            add_meta_box(
                'taxonomy-selector',
                ucfirst($this->singular),
                array($this, 'select'),
                $post_type,
                'side',
                'low'
            );
        }

    }

    public function savePost($post_id)
    {

        $post_type = get_post_type($post_id);
        if (!in_array($post_type, $this->post_type)) {
            return false;
        }

        if (!Forms::savePostSecurity($post_id, '_nonce_taxonomy', basename(__FILE__), $post_type, 'edit_posts')) {
            return false;
        }

        $taxonomy = Utils::postInt('_taxonomy');
        if (empty($taxonomy)) {
            return false;
        }

        $save_ids = array();
        $save_ids[0] = intval($taxonomy);

        $save_terms = wp_set_object_terms($post_id, $save_ids, $this->slug, false);

        if (is_wp_error($save_terms)) {
            Utils::debug("ERROR There was an error somewhere and the terms couldn't be set.");
        } else {
            // Success! The post's categories were set.
        }

    }

    public function select($post)
    {

        if (Utils::isAjaxRequest()) {
            return;
        }

        $args = array(
            'orderby'       => 'name',
            'order'         => 'ASC',
            'hide_empty'    => false,
            'parent'        => 0,
            );

        wp_nonce_field(basename(__FILE__), '_nonce_taxonomy');

        $terms = get_terms($this->slug, $args);
        $first = current($terms);

        $data = array();
        foreach ($terms as $term) {
            $data[$term->term_id] = $term->name;
        }

        $saved_term = wp_get_post_terms($post->ID, $this->slug);

        if (empty($saved_term)) {
            $saved_term = $first->term_id;
        } else {
            $saved_term = current($saved_term)->term_id;
        }

        echo Fields::radio(array(
            'name'      => '_taxonomy',
            'label'     => '',
            'data'      => $data,
            'value'     => $saved_term
        ));

    }

    public static function updateTaxonomyObjects()
    {

        $taxonomy_objects = array();

        $args = array(
            'orderby'       => 'name',
            'order'         => 'ASC',
            'hide_empty'    => false,
            'parent'        => 0,
            );

        foreach (self::$taxonomies as $taxonomy) {
            $tax_object = get_taxonomy($taxonomy);
            $terms = get_terms($taxonomy, $args);
            foreach ($terms as $term) {
                $data = array(
                    'slug' => $term->slug,
                    'taxonomy' => $taxonomy,
                    'post_type' => $tax_object->object_type
                    );
                $taxonomy_objects[$term->slug] = $data;
            }
        }

        $taxonomy_objects = apply_filters('SB_taxonomy_objects', $taxonomy_objects);

        update_option('_sb_taxonomy_objects', $taxonomy_objects);

    }

    public function saveTerm($term_id)
    {

        self::updateTaxonomyObjects();

        // More saves?

    }

    public function postTypeLink($post_link, $post = false)
    {

        if (!is_object($post)) {
            return $post_link;
        }

        if (!in_array($post->post_type, $this->post_type)) {
            return $post_link;
        }

        if (Utils::isAjaxRequest() && 'load_more' != Utils::postString('action')) {
            return false; // FIXME this ajax call messes up everything, stupid shit
        }

        if ($post->post_status == 'auto-draft') { // don't change the link before post is "real"
            return $post_link;
        }

        $terms = wp_get_object_terms($post->ID, $this->slug);

        if (empty($terms)) {
            return false;
        }

        $term = current($terms);

        $term_slug = apply_filters('SB_taxonomy_post_type_term_slug', $term->slug, $post, $term);

        if ($this->rewrite && false !== strpos($post_link, $this->slug)) {
            $post_link = str_replace('%'.$this->slug.'%', $term_slug, $post_link);
        }

        if (!$this->rewrite) {
            $post_link = str_replace('?'.$post->post_type.'=', $term_slug.'/', $post_link).'/';
        }

        $post_link = apply_filters('SB_taxonomy_post_type_link', $post_link, $post, $term);

        return $post_link;

    }

    public function termLink($termlink, $term, $taxonomy)
    {

        if ($this->slug != $taxonomy) {
            return $termlink;
        }

        $termlink = str_replace($this->slug.'/', '', $termlink);

        $termlink = apply_filters('SB_taxonomy_term_link', $termlink, $term, $this);

        return $termlink;

    }

    public function postTypeFilter()
    {

        global $typenow;

        if (!in_array($typenow, $this->post_type)) {
            return false;
        }

        $args = array(
            'orderby'       => 'name',
            'order'         => 'ASC',
            'hide_empty'    => true,
            'parent'        => 0,
            );

        $terms = get_terms($this->slug, $args);

        echo '<select name="'.$this->slug.'" id="'.$this->slug.'" class="postform">';
        echo '<option value="">'.$this->show_all_label.'</option>';

        $selected = Utils::getString($this->slug);

        foreach ($terms as $term) {
            echo '<option value="'.$term->slug.'" '.selected($selected, $term->slug).' >';
            echo $term->name.'</option>';
        }

        echo '</select>';

    }

    public static function getMetaValue($term_id, $key, $taxonomy)
    {

        $data = get_option(MetaData::getMetaKey($taxonomy), array());

        if (empty($data[$term_id])) {
            return false;
        }

        if (!empty($data[$term_id][$key])) {
            return $data[$term_id][$key];
        }

        return false;

    }

    public static function getFromPostType($post_type)
    {

        if (!empty(self::$post_types[$post_type])) {
            return self::$post_types[$post_type];
        }

        return false;

    }

    public function columns($columns)
    {

        $new_columns = array();

        foreach ($columns as $id => $label) {
            if (!in_array($id, $this->hide_columns)) {
                $new_columns[$id] = $label;
            }
        }

        return $new_columns;

    }
}
