<?php

namespace SB\Taxonomy;

use SB\Utils;
use SB\Forms\Fields;
use SB\Media;

class MetaData
{

    public static $registered_meta_data = array();

    public function __construct($taxonomy, $meta_data, $hide_columns)
    {

        $this->taxonomy = $taxonomy;
        $this->meta_data = $meta_data;
        $this->hide_columns = $hide_columns;

        add_filter('manage_edit-'.$taxonomy.'_columns', array($this, 'columns'));
        add_action('manage_'.$taxonomy.'_custom_column', array($this, 'columnsData'), 10, 3);

        add_action($taxonomy.'_edit_form_fields', array($this, 'form'), 10, 2);
        add_action($taxonomy.'_add_form_fields', array($this, 'form'), 10, 2);

        add_action('created_'.$taxonomy, array($this, 'save'), 10, 2);
        add_action('edit_'.$taxonomy, array($this, 'save'), 10, 2);

        add_filter('get_term', array($this, 'withMeta'), 10, 2);
        add_filter('get_terms', array($this, 'allWithMeta'), 10, 2);
        add_filter('get_the_terms', array($this, 'postTermWithMeta'), 10, 1);

    }

    public static function register($taxonomy, $meta_data, $hide_columns)
    {

        return new self($taxonomy, $meta_data, $hide_columns);

    }

    public static function getMetaKey($taxonomy)
    {

        $key = (is_object($taxonomy)) ? $taxonomy->taxonomy : $taxonomy;

        return 'taxonomy-'.$key.'-meta-data';

    }

    public function columns($columns)
    {

        $new_columns = array();

        foreach ($columns as $id => $label) {
            if (!in_array($id, $this->hide_columns)) {
                $new_columns[$id] = $label;
            }

            if ($id == 'slug') {
                foreach ($this->meta_data as $name => $arg) {
                    $new_columns[$name] = $arg['label'];
                }
            }

        }

        return $new_columns;

    }

    public function columnsData($deprecated, $key, $term_id)
    {

        if (array_key_exists($key, $this->meta_data)) {
            $default = (!empty($this->meta_data[$key]['default'])) ? $this->meta_data[$key]['default'] : false;
            $value = self::getValue($term_id, $key, $default);

            switch ($this->meta_data[$key]['type']) {
                case 'color':
                    $value = '<span class="color-box" style="width: 27px; background-color: '.$value.'"></span>';
                    break;

                case 'image':
                    if (!is_array($value)) {
                        break;
                    }
                    foreach ($value as $media_id) {
                        $media = Media::get($media_id, 'thumbnail');
                        $value = '<img class="taxonomy-image" src="'.$media['src'][0].'">';
                    }
                    break;

                default:
                    break;
            }

            echo $value;

        }

    }

    public function form($taxonomy)
    {

        // loadData
        $term_id = Utils::getInt('tag_ID');
        // $meta_key = self::getMetaKey($taxonomy);
        $wrapper = (is_numeric($term_id)) ? 'table' : 'div';

        foreach ($this->meta_data as $name => $data) {
            $args = $data;
            $default = (!empty($data['default'])) ? $data['default'] : false;

            $args['name'] = $name;
            $args['value'] = self::getValue($term_id, $name, $default);
            $args['wrapper'] = $wrapper;

            $method = $data['type'];

            switch ($method) {
                case 'image':
                    if (empty($args['value'])) {
                        $args['value'] = -1;
                    }
                    break;

                default:
                    break;
            }

            if (!in_array($method, get_class_methods('SB\Forms\Fields'))) {
                Utils::debug($name.': No method to handle '.$data['type'], 0);
            } else {
                echo Fields::$method($args);
            }

        }

    }

    public function save($term_id)
    {

        $data = self::load();

        //FIXME remove all other names, to prevent old data from being
        // left behind

        foreach ($this->meta_data as $key => $args) {
            $data[$term_id][$key] = Utils::postVar($key);
        }

        update_option(self::getMetaKey($this->taxonomy), $data);

    }

    public function load()
    {

        return get_option(self::getMetaKey($this->taxonomy), array());

    }

    public function getValue($term_id, $key, $default = false)
    {

        $data = self::load();

        if (empty($data[$term_id][$key])) {
            return $default;
        }

        return $data[$term_id][$key];

    }

    public function withMeta($object, $taxonomy, $all = false)
    {

        if (Utils::isAjaxRequest()) {
            return $object;
        }

        if (is_array($taxonomy)) {
            $taxonomy = current($taxonomy);
        }

        if ($this->taxonomy != $taxonomy) {
            return $object;
        }

        $data = self::load();
        if (!empty($data[$object->term_id])) {
            $object->meta_data = $data[$object->term_id];
        }

        return $object;

    }

    public function allWithMeta($objects, $taxonomy)
    {

        if (Utils::isAjaxRequest()) {
            return $objects;
        }

        $return = array();

        foreach ($objects as $object) {
            $return[] = self::withMeta($object, $taxonomy, true);
        }

        return $return;

    }

    public function postTermWithMeta($terms)
    {

        $data = self::load();

        foreach ($terms as $i => $term) {
            if (!empty($data[$term->term_id])) {
                $term->meta_data = $data[$term->term_id];
                $term = apply_filters('SB_taxonomy_get_the_terms', $term);
                $terms[$i] = $term;
            }
        }

        return $terms;
    }
}
