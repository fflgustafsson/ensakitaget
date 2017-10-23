<?php

namespace SB\Forms;

use SB\Utils;

class Multi
{

    // max number
    // labels

    public static $data = array();
    public static $defaults = array(
        'class' => false,
        'max_number' => false,
        'add' => 'Lägg till'
        );

    public static function defaultData($fields)
    {

        $data = array();

        foreach ($fields as $name => $args) {
            $value = ($args['type'] == 'image') ? array(-1) : null;
            $value = (!empty($args['default'])) ? $args['default'] : $value;
            $data[$name] = $value;
        }

        return array(0 => $data);

    }

    public static function load($value, $fields)
    {

        self::$data = self::defaultData($fields);

        $return = array();

        if (!empty($value)) {
            foreach ($value as $key => $array) {
                foreach ($array as $index => $content) {
                    $return[$index][$key] = $content;
                }
            }
        }

        foreach ($return as $i => $r) {
            $return[$i] = wp_parse_args($r, self::$data[0]);
        }

        return $return;

    }

    public static function getValues($post_id, $meta_key = false)
    {

        if (is_numeric($post_id) && $meta_key) {
            $value = get_post_meta($post_id, $meta_key, true);
        } else {
            $value = get_option($post_id);
        }

        $return = array();

        if (empty($value)) {
            return false;
        }

        foreach ($value as $key => $array) {
            foreach ($array as $index => $content) {
                $return[$index][$key] = $content;
            }

        }

        return $return;

    }

    public static function addValues($basename, $args, $value)
    {

        $key = $args['local-name'];
        $args['value'] = $value[$key];
        return $args;

    }

    public static function handleTypeUrl($data_array)
    {

        $construct = $return = array();

        foreach ($data_array as $data) {
            $key = key($data);

            switch ($key) {
                case 'id':
                    if (isset($construct['id'])) {
                        $return[] = $construct;
                        $construct = array(); // clear array
                    }
                    $value = (empty($data[$key])) ? false : $data[$key];
                    $construct['id'] = $value;
                    break;

                case 'url':
                case 'target':
                case 'text':
                    $value = (empty($data[$key])) ? false : $data[$key];
                    $construct[$key] = $value;
                    break;

                default:
                    break;

            }

        }

        $return[] = $construct;

        return $return;

    }

    public static function form($args, $value)
    {

        $data = self::load($value, $args['fields']);
        // debug($data, 0);

        $args = wp_parse_args($args, self::$defaults);
        extract($args);

        $wrapper_class = 'sb-multi-'.str_replace(array('_', '-'), '', $name);
        $class = str_replace(array('_', '-'), '', $class);
        $wrapper_classes = array('sb-multi-wrapper', $wrapper_class, $class);

        $tag = array();

        $tag[] = '<div class="'.implode(' ', $wrapper_classes).'">';

        // script tag
        $tag[] = self::script($name, $fields, $remove, self::$data[0]);

        $tag[] = '<div class="sb-form-wrapper sb-forms-sortable" id="'.$name.'-wrapper" data-sort-wrapper=".sb-field-wrapper" data-max-number="3">';

        foreach ($data as $key => $values) {
            $tag[] = self::fields($name, $fields, $remove, $values);
        }

        $tag[] = '</div>';

        $tag[] = '<div class="action">';
        $tag[] = '<a class="button add-form-element" data-template="#template-'.$name.'" data-wrapper="#'.$name.'-wrapper">'.$add.'</a>';
        $tag[] = '</div>';

        $tag[] = '</div>';

        return $tag;

    }

    public static function fields($basename, $fields, $remove, $value)
    {

        $output = array();

        foreach ($fields as $name => $args) {
            $method = $args['type'];
            $args['name'] = $basename.'['.$name.'][]';
            $args['local-name'] = $name;
            $args['class'] = (!empty($args['class'])) ? $args['class'].' sb-multi-'.$name : 'sb-multi-'.$name;

            // handle value
            if (!empty($value)) {
                $args = self::addValues($name, $args, $value);
            }

            if (!in_array($method, get_class_methods('SB\Forms\Fields'))) {
                debug($name.': No method to handle '.$method);
            } else {
                $output[] = Fields::$method($args);
            }

        }

        $remove = '<a href="javascript:void(0)" class="remove-form-element remove-selected" data-wrapper=".sb-field-wrapper">'.$remove.'</a>';

        return '<div class="sb-field-wrapper" data-name="'.$basename.'">'.$remove."\n\t".implode("\n\t", $output)."\n".'</div>';

    }

    public static function script($basename, $fields, $remove, $value)
    {

        $script = array();

        $script[] = '<script id="template-'.$basename.'" type="sb/template">';
        $script[] = self::fields($basename, $fields, $remove, $value);
        $script[] = '</script>';

        return implode("\n", $script);

    }
}
