<?php

namespace SB\Forms;

use SB\Utils;
use SB\Forms\Multi;

class Fields
{

    // FIXME Utils::Debug value consoles possible args

    public static function text($args)
    {

        $args = Common::validateArgs(__FUNCTION__, $args);
        extract($args);

        $value = \SB\Forms::autoRegister($auto_value, $value, $name, false);

        $value = (empty($value)) ? $default : $value;
        $placeholder = (!empty($placeholder)) ? $placeholder : null;
        $disabled = Common::isDisabled($disabled);
        $required = Common::isRequired($required);
        $type = (empty($type)) ? 'text' : $type;

        $types = array('text', 'email', 'hidden', 'password');
        $type = (in_array($type, $types)) ? $type : 'text';

        $max_count = (!empty($max_count)) ? ' data-max="'.intval($max_count).'"' : false;
        $count = (!empty($count)) ? '<span class="sb-text-count" '.$max_count.'></span>' : false;

        $maxlength = (!empty($maxlength)) ? 'maxlength="'.$maxlength.'"' : false;

        $tag = sprintf(
            '%s<input id="%s" type="%s" name="%s" class="regular-text" value="%s" placeholder="%s" %s %s %s />',
            $count,
            $id,
            $type,
            $name,
            esc_attr($value),
            $placeholder,
            $disabled,
            $required,
            $maxlength
        );
        return Common::wrapper($args, $tag);

        // to-do
        // force default

    }

    public static function textarea($args)
    {

        $args = Common::validateArgs(__FUNCTION__, $args);
        extract($args);

        $value = \SB\Forms::autoRegister($auto_value, $value, $name, false);

        $value = (empty($value)) ? $default : $value;
        $value = (isset($html) && $html == true) ? stripcslashes($value) : strip_tags($value);

        $placeholder = (!empty($placeholder)) ? $placeholder : null;
        $rows = (isset($rows) && is_numeric($rows)) ? $rows : 3;
        $disabled = Common::isDisabled($disabled);
        $required = Common::isRequired($required);

        $max_count = (!empty($max_count)) ? ' data-max="'.intval($max_count).'"' : false;
        $count = (!empty($count)) ? '<span class="sb-text-count" '.$max_count.'></span>' : false;

        $tag = sprintf('%s<textarea id="%s" name="%s" class="large-text" rows="%d" placeholder="%s" %s>%s</textarea>', $count, $id, $name, $rows, $placeholder, $disabled, $value);

        return Common::wrapper($args, $tag);

        // to-do
        // auto-expand

    }

    public static function select($args)
    {

        $args = Common::validateArgs(__FUNCTION__, $args);
        extract($args);

        $value = \SB\Forms::autoRegister($auto_value, $value, $name, false);

        $disabled = Common::isDisabled($disabled);
        $value = (empty($value)) ? $default : $value;
        $add_empty = (empty($add_empty)) ? false : $add_empty;

        $tag = array();
        $tag[] = sprintf('<select id="%s" name="%s" %s>', $id, $name, $disabled);

        if ($add_empty) {
            $tag[] = '<option value="" '.selected('', $value, false).'>'.$add_empty.'</option>';
        }

        foreach ($data as $key => $text) {
            $selected = selected($key, $value, false);
            $tag[] = sprintf('<option value="%s" %s>%s</option>', $key, $selected, $text);
        }

        $tag[] = '</select>';

        return Common::wrapper($args, $tag);

        // to-do
        // default

    }

    public static function checkbox($args)
    {

        $args = Common::validateArgs(__FUNCTION__, $args);
        extract($args);

        $value = \SB\Forms::autoRegister($auto_value, $value, $name, false);

        $unique = (!empty($unique)) ? 'sb-multi-unique' : false;
        $args['class'] = $class .' '.$unique;

        $disabled = Common::isDisabled($disabled);
        $checked = checked(1, $value, false);

        $tag = sprintf('<input %s type="checkbox" name="%s" value="1" %s %s />', $id, $name, $checked, $disabled);

        return Common::wrapper($args, $tag);

        // to-do
        // differ text/label?
        // id
        // default

    }

    public static function radio($args)
    {

        $args = Common::validateArgs(__FUNCTION__, $args);
        extract($args);

        $value = \SB\Forms::autoRegister($auto_value, $value, $name, false);

        $disabled = Common::isDisabled($disabled);
        $value = empty($value) ? $default : $value;

        $tag = array();
        foreach ($data as $key => $text) {
            $checked = checked($key, $value, false);
            $tag[] = sprintf('<input %s type="radio" name="%s" value="%s" %s %s> %s<br />', $id, $name, $key, $checked, $disabled, $text);
        }

        return Common::wrapper($args, $tag);

    }

    public static function editor($args)
    {

        // textarea_rows
        // media_buttons (boolean)

        $args = Common::validateArgs(__FUNCTION__, $args);
        extract($args);

        $value = \SB\Forms::autoRegister($auto_value, $value, $name, false);

        ob_start();
        wp_editor($value, $name, $args);
        $tag = ob_get_clean();

        return Common::wrapper($args, $tag);

    }

    public static function number($args)
    {

        $args = Common::validateArgs(__FUNCTION__, $args);
        extract($args);

        $value = \SB\Forms::autoRegister($auto_value, $value, $name, false);

        $default = (is_numeric($default)) ? $default : 1;
        $value = (!is_numeric($value)) ? 'value="'.intval($default).'"' : 'value="'.intval($value).'"';
        $disabled = Common::isDisabled($disabled);

        $min = (!isset($min)) ? 'min="1"' : 'min="'.$min.'"';
        $max = (!isset($max)) ? false : 'max="'.$max.'"';
        $step = (!isset($step)) ? 'step="1"' : 'step="'.$step.'"';

        $tag = sprintf('<input class="small-text" name="%s" type="number" %s %s %s %s %s />', $name, $step, $min, $max, $disabled, $value);

        return Common::wrapper($args, $tag);

    }

    public static function date($args)
    {

        // dateFormat
        // http://jqueryui.com/datepicker/#date-formats
        // If you plan to save date as timestamp, use simple format, ISO 8601 for example

        $args = Common::validateArgs(__FUNCTION__, $args);
        extract($args);

        $timestamp = (isset($timestamp) && $timestamp) ? true : false;
        $format = (!empty($args['dateFormat'])) ? $args['dateFormat'] : \SB\Forms::$date_default['dateFormat'];

        if ($timestamp) {
            // use convert method
            $value = \SB\Forms::autoRegister($auto_value, $value, $name, 'SB\Forms\Common::dateToTimestamp');
            // default
            $value = (!empty($default) && empty($value)) ? $default : $value;
            // convert
            $value = Common::timestampToDate($value, $format);
        } else {
            $value = \SB\Forms::autoRegister($auto_value, $value, $name, false);
            $value = (!empty($default) && empty($value)) ? $default : $value;
        }

        $settings = Common::removeBaseArgs($args);
        \SB\Forms::$date_settings[$class] = $settings;

        $icon = '<span class="calendar-icon dashicons dashicons-calendar-alt"></span>';

        $tag = sprintf(
            '<input %s type="text" name="%s" class="date-picker" value="%s" />%s',
            $id,
            $name,
            $value,
            $icon
        );

        return Common::wrapper($args, $tag);

    }

    public static function color($args)
    {

        $args = Common::validateArgs(__FUNCTION__, $args);
        extract($args);

        $value = \SB\Forms::autoRegister($auto_value, $value, $name, false);

        $default = (isset($default)) ? $default : 'false';
        $palettes = (empty($palettes)) ? 'false' : $palettes;

        \SB\Forms::$color_settings[$class] = array('default' => $default, 'palettes' => $palettes);

        $value = strtolower($value);
        $value = ('false' == $value) ? '' : $value;

        $value = empty($value) ? $default : $value;

        $tag = array();
        $tag[] = '<div class="color-wrapper">';

        $tag[] = ($default != 'false') ? '<button class="button-secondary default-color" data-default="'.$default.'">Förvald</button>' : false;
        $tag[] = '<div class="'.$class.'-current-color color-box" style="background-color: '.$value.'"></div>';
        $tag[] = sprintf('<input %s type="text" name="%s" value="%s" class="color-picker" data-default-color="%s" />', $id, $name, $value, $default);
        $tag[] = '</div>';

        return Common::wrapper($args, $tag);

    }

    public static function image($args)
    {

        // image_size
        // button_label
        // multiple
        // max_number
        // remove

        $args = Common::validateArgs(__FUNCTION__, $args);
        extract($args);

        $value = \SB\Forms::autoRegister($auto_value, $value, $name, true);

        $image_size = (isset($image_size)) ? $image_size : 'thumbnail';
        $multiple = (isset($multiple) && $multiple == 'true') ? 'true' : 'false';
        if (!isset($button_label)) {
            $button_label = ($multiple == 'true') ? 'Lägg till bilder' : 'Välj bild';
        }
        $classes = array('sb-selected-wrapper');
        $classes[] = ($multiple == 'true') ? 'multiple' : 'single';

        $display_classes = array('sb-selected-display');

        $display_classes[] = ($multiple == 'true') ? 'sb-forms-sortable' : false;
        $sort_wrapper = ($multiple == 'true') ? 'li.sb-media-attachment' : false;
        $sort_function = ($multiple == 'true') ? 'updateHiddenIds' : false;
        $max_number = ($multiple == 'true' && !empty($max_number) && is_numeric($max_number)) ? $max_number : false;
        $disabled = Common::isDisabled($disabled);

        $default = (!empty($default)) ? $default : false;

        // Handle default and force default
        if (false === $value || ('' == $value && (isset($force_default) && $force_default == true))) {
            $value = array(-1);
        }

        // Check for deleted
        $value = Common::checkForDeletedFiles($value);
        $image_elements = array();

        foreach ($value as $media_id) {
            $image_elements[] = Common::getMediaElement($media_id, $image_size, $default, $remove, $name);
        }

        $dim_button = ($max_number == count($value) && !empty($value)) ? 'disabled' : false;

        $remove = ($remove) ? $remove : 'false';

        $tag[] = '<div class="'.implode(' ', $classes).'" data-name="'.$name.'" data-image-size="'.$image_size.'" data-multiple="'.$multiple.'" data-remove="'.$remove.'" data-max-number="'.$max_number.'">';
        $tag[] = '<ul class="'.implode(' ', $display_classes).'" data-sort-wrapper="'.$sort_wrapper.'">'.implode('', $image_elements).'</ul>';
        $tag[] = (!$disabled) ? '<div class="block"><input class="sb-media-upload button-secondary" type="button" value="'.$button_label.'" '.$dim_button.'></div>' : false;
        $tag[] = '</div>';

        return Common::wrapper($args, $tag);

        // Defualt
        // Force default

    }

    public static function posts($args)
    {

        // post_type (array)
        // multiple
        // placeholder (Sök)
        // max_number
        // remove
        // list (boolean)
        // limit (if list is true ALL posts are returned)

        $args = Common::validateArgs(__FUNCTION__, $args);
        extract($args);

        $value = \SB\Forms::autoRegister($auto_value, $value, $name, true);

        $post_type = (empty($post_type)) ? 'post' : $post_type;
        if (!is_array($post_type)) {
            $post_type = array($post_type);
        }
        $post_type = json_encode($post_type, JSON_FORCE_OBJECT);

        $multiple = (isset($multiple) && $multiple == 'true') ? 'true' : 'false';
        $list = (isset($list) && $list) ? true : false;
        $is_list = ($list) ? 'true' : 'false';
        $search_limit = (isset($search_limit) && is_numeric($search_limit)) ? $search_limit : 10;

        $default_placeholder = (!$list) ? 'Sök' : 'Filtrera';
        $placeholder = (isset($placeholder)) ? $placeholder : $default_placeholder;

        $display_classes = array('selected-posts', 'sb-selected-display');
        $display_classes[] = ($multiple == 'true') ? 'sb-forms-sortable' : false;
        $sort_wrapper = ($multiple == 'true') ? 'li.post-element' : false;
        $max_number = ($multiple == 'true' && !empty($max_number) && is_numeric($max_number)) ? $max_number : false;
        $meta_key = (isset($meta_key)) ? $meta_key : 'post_date_gmt';
        $image_meta = (isset($image_meta)) ? $image_meta : '_thumbnail_id';
        $choose = (isset($choose)) ? $choose : 'Välj';

        $remove = ($remove) ? '&ndash;' : 'false';

        $selected_posts = array();
        if (!empty($value)) {
            if (!is_array($value)) {
                $value = explode(',', $value);
            }
            foreach ($value as $id) {
                $load_data_via_ajax = (Utils::isAjaxRequest()) ? true : false; // this happens when widget is saved
                $selected_posts[] = Common::getPostElement($id, $remove, $name, $load_data_via_ajax, $meta_key, $image_meta);
            }
        }

        $has_selected_posts = (!empty($selected_posts)) ? 'has-posts' : false;

        $wrapper_classes = array('sb-post-select-wrapper', 'sb-selected-wrapper', $has_selected_posts);
        $wrapper_classes[] = ($multiple == 'true') ? 'multiple' : false;
        $wrapper_classes[] = ($list) ? 'sb-post-list-w-filter' : false;

        $data = array(
            'name' => $name,
            'metakey' => $meta_key,
            'image' => $image_meta,
            'posttype' => esc_attr($post_type),
            'multiple' => $multiple,
            'remove' => $remove,
            'max-number' => $max_number,
            'function' => 'get_post_element',
            'list' => $is_list,
            'search-limit' => $search_limit
            );

        $tag[] = '<div class="'.implode(' ', $wrapper_classes).'" '.Common::dataAttr($data).'>';
        $tag[] = '<ul class="'.implode(' ', $display_classes).'" data-sort-wrapper="'.$sort_wrapper.'">'.implode('', $selected_posts).'</ul>';

        if (!$list) {
            // SEARCH
            $tag[] = '<div class="search-wrapper"><input type="search" class="wide-fat sb-post-search" placeholder="'.$placeholder.'" name="sb-post-select" /><span class="spinner"></span>';
            $tag[] = '<ul class="search-results"></ul></div>';

        } else {
            // FILTER + LIST
            $tag[] = '<a href class="button button-secondary choose-post-from-list">'.$choose.'</a><span class="spinner"></span>';
            $tag[] = '<div class="list-wrapper"><input type="search" class="wide-fat sb-post-filter" placeholder="'.$placeholder.'" name="sb-post-filter" />';
            $tag[] = '<ul class="post-list"></ul>';

        }

        $tag[] = '</div>';

        return Common::wrapper($args, $tag);

    }

    public static function url($args)
    {

        $args = Common::validateArgs(__FUNCTION__, $args);
        extract($args);

        $value = \SB\Forms::autoRegister($auto_value, $value, $name, true);

        $default_post_types = array('post', 'page');

        if (is_array($default)) {
            $default_data = array('id' => null, 'url' => null, 'target' => 0, 'text' => false);
            $default_data = wp_parse_args($default, $default_data);
        } else {
            $default = (!empty($default)) ? $default : false;
            $default_data = array('id' => null, 'url' => $default, 'target' => 0, 'text' => false);
        }

        $value = wp_parse_args($value, $default_data);

        if (empty($post_type) || !is_array($post_type)) {
            $post_type = $default_post_types;
        }

        $post_type = json_encode($post_type, JSON_FORCE_OBJECT);
        $placeholder = (isset($placeholder)) ? $placeholder : 'Sök';
        $target_class = array('target');
        $target_class[] = (isset($target) && $target) ? 'hidden' : false;

        $wrapper_classes = array('sb-post-select-wrapper', 'sb-selected-wrapper', 'sb-fancy-url');

        $hide_display = 'hidden';
        $hide_search = false;
        $permalink = false;
        $post_title = false;

        if (is_numeric($value['id'])) {
            $permalink = str_replace(Utils::wpSiteurl(), '', get_permalink($value['id']));
            $post_title = get_the_title($value['id']);
            $hide_display = false;
            $hide_search = 'hidden';
        }

        if (!is_numeric($value['id']) && !empty($value['id'])) {
            $custom_item = SB\NavMenu::getCustomItemData($value['id']);
            if ($custom_item) {
                if ($custom_item['type'] == 'custom_post_type') {
                    $permalink = str_replace(Utils::wpSiteurl(), '', get_post_type_archive_link($custom_item['id']));
                } else {
                    $permalink = $custom_item['uri'];
                }
                $post_title = $custom_item['addmenulabel'];
                $hide_display = false;
                $hide_search = 'hidden';
            }
        }

        $text = (isset($text)) ? false : 'hidden';
        $labels = (!empty($labels)) ? $labels : array('Länk', 'Text');

        $disable_search = (isset($disable_search) && $disable_search) ? false : 'sb-post-search';
        $search_classes = array('wide-fat', $disable_search, $hide_search);
        $meta_key = (isset($meta_key)) ? $meta_key : 'post_date_gmt';

        $shorten_title = (!empty($shorten_title) && $shorten_title) ? 'true' : 'false';

        if ($shorten_title == 'true') {
            $post_title = Utils::shorten($post_title, 35);
        }

        $selected_post = '<a class="selected" href="'.$permalink.'" target="_blank">'.$post_title.'</a>';
        $display_classes = array('display-wrapper', $hide_display);

        $tag[] = '<div class="'.implode(' ', $wrapper_classes).'" data-metakey="'.$meta_key.'" data-posttype="'.esc_attr($post_type).'" data-function="set_post_id" data-shorten-title="'.$shorten_title.'">';

        if ($text) {
            $tag[] = '<label>'.$labels[0].'</label>';
        }

        $tag[] = '<input class="selected-post-id" type="hidden" name="'.$name.'[id]" value="'.$value['id'].'">';
        $tag[] = '<div class="'.implode(' ', $display_classes).'"><div class="selected-post">'.$selected_post.'<a href="" class="remove">&ndash;</a></div></div>';
        $tag[] = '<div class="search-wrapper"><input type="text" class="'.implode(' ', $search_classes).'"';
        $tag[] = ' placeholder="'.$placeholder.'" name="'.$name.'[url]" value="'.$value['url'].'" /><span class="spinner"></span>';
        $tag[] = '<ul class="search-results"></ul></div>';

        $tag[] = '<div class="'.implode(' ', $target_class).'">';
        $tag[] = '<input type="checkbox" value="1" name="'.$name.'[target]" '.checked(1, $value['target'], false).'>';
        $tag[] = '<label for="target">Öppna i nytt fönster</label>';
        $tag[] = '</div>';

        $tag[] = '<div class="text-wrapper '.$text.'">';
        $tag[] = '<label>'.$labels[1].'</label>';
        $tag[] = '<input class="url-text" type="text" name="'.$name.'[text]" value="'.$value['text'].'">';
        $tag[] = '</div>';

        $tag[] = '</div>';

        return Common::wrapper($args, $tag);

    }

    public static function multi($args)
    {

        $args = Common::validateArgs(__FUNCTION__, $args);
        extract($args);

        $value = \SB\Forms::autoRegister($auto_value, $value, $name, true);
        // Utils::debug($value);

        $tag = Multi::form($args, $value);

        return Common::wrapper($args, $tag);

    }

    public static function toggle($args) // http://todo.wildhorse.dev/todo_gui.php#
    {

        $args = Common::validateArgs(__FUNCTION__, $args);
        extract($args);

        $default = (!isset($default)) ? 0 : $default;

        $value = \SB\Forms::autoRegister($auto_value, $value, $name, false);
        $value = (!isset($value)) ? $default : $value;

        $on = (empty($on)) ? 'I' : $on;
        $off = (empty($off)) ? 'O' : $off;

        $active = (1 == $value) ? 'is-active' : false;
        $theme = (empty($theme)) ? false : $theme;
        $classes = array('toggle-wrapper', $active, $theme);

        $tag = array();

        $tag[] = '<div class="'.implode(' ', $classes).'">';
        $tag[] = '<span class="on">'.$on.'</span>';
        $tag[] = '<span class="off">'.$off.'</span>';
        $tag[] = '<span class="switch"></span>';
        $tag[] = '<input name="'.$name.'" type="hidden" value="'.$value.'">';
        $tag[] = '</div>';

        return Common::wrapper($args, $tag);

    }

    public static function custom($args)
    {

        $args = Common::validateArgs(__FUNCTION__, $args);
        $output = array();
        $method = (!empty($args['callback']) && is_callable($args['callback'])) ? $args['callback'] : false;

        if (!$method) {
            Utils::debug($args);
            Utils::debug('ERROR function is not callable');
            return false;
        }

        $output = call_user_func($method, $args);

        return Common::wrapper($args, $output);

    }

    public static function password($args)
    {

        $args = Common::validateArgs(__FUNCTION__, $args);
        extract($args);

        $value = \SB\Forms::autoRegister($auto_value, $value, $name, 'SB\Forms\Common::encryptPassword');
        $value = (empty($value)) ? $default : $value;

        if (!is_callable('mcrypt_decrypt')) {
            Utils::debug('ERROR You need to install Mcrypt to use password decryption.');
        } else {
            $value = Common::decryptPassword($value);
        }

        $placeholder = (!empty($placeholder)) ? $placeholder : null;
        $disabled = Common::isDisabled($disabled);
        $required = Common::isRequired($required);
        $show = (empty($show)) ? 'Visa' : $show;
        $hide = (empty($hide)) ? 'Dölj' : $hide;
        $button = '<a class="button button-secondary sb-password-toggle" href="javascript:void(0);" data-show="'.$show.'" data-hide="'.$hide.'">'.$show.'</a>';

        $tag = sprintf('<input %s type="password" autocomplete="off" name="%s" class="sb-fields-password" value="%s" %s %s />%s', $id, $name, esc_attr($value), $disabled, $required, $button);
        return Common::wrapper($args, $tag);

    }

    public static function fader($args)
    {

        $args = Common::validateArgs(__FUNCTION__, $args);
        extract($args);

        $value = \SB\Forms::autoRegister($auto_value, $value, $name, false);

        $value = (empty($value)) ? $default : $value;
        $value = (empty($value)) ? 0 : $value;

        $output = (!empty($output) && $output) ? 'text' : 'hidden';

        $classes = array('wrapper');

        $config = array();
        $config['min'] = (empty($min)) ? 0 : $min;
        $config['max'] = (empty($max)) ? 100 : $max;

        $tag = array();
        $tag[] = '<div class="'.implode(' ', $classes).'" '.Common::elementData($config).'>';
        $tag[] = '<div class="fader-bar"></div>';
        $tag[] = '<div class="fader-knob"></div>';
        $tag[] = '<input name="'.$name.'" type="'.$output.'" value="'.$value.'">';
        $tag[] = '</div>';

        return Common::wrapper($args, $tag);

    }

    public static function users($args)
    {

        // multiple
        // placeholder (Sök)
        // max_number
        // remove

        $args = Common::validateArgs(__FUNCTION__, $args);
        extract($args);

        $value = \SB\Forms::autoRegister($auto_value, $value, $name, true);

        $multiple = (isset($multiple) && $multiple == 'true') ? 'true' : 'false';

        $placeholder = (isset($placeholder)) ? $placeholder : 'Sök';

        $display_classes = array('selected-posts', 'sb-selected-display');
        $display_classes[] = ($multiple == 'true') ? 'sb-forms-sortable' : false;
        $sort_wrapper = ($multiple == 'true') ? 'li.post-element' : false;
        $max_number = ($multiple == 'true' && !empty($max_number) && is_numeric($max_number)) ? $max_number : false;
        $meta_key = (isset($meta_key)) ? $meta_key : 'post_date_gmt'; // FIXME
        $image_meta = (isset($image_meta)) ? $image_meta : '_thumbnail_id'; // FIXME
        $choose = (isset($choose)) ? $choose : 'Välj';

        $remove = ($remove) ? '&ndash;' : 'false';

        $selected_posts = array();
        if (!empty($value)) {
            foreach ($value as $id) {
                $load_data_via_ajax = (Utils::isAjaxRequest()) ? true : false; // this happens when widget is saved
                $selected_posts[] = Common::getUserElement($id, $remove, $name, $load_data_via_ajax);
            }
        }

        $has_selected_posts = (!empty($selected_posts)) ? 'has-posts' : false;

        $wrapper_classes = array('sb-post-select-wrapper', 'sb-selected-wrapper', $has_selected_posts);
        $wrapper_classes[] = ($multiple == 'true') ? 'multiple' : false;

        $data = array(
            'name' => $name,
            'metakey' => $meta_key,
            'image' => $image_meta,
            'multiple' => $multiple,
            'remove' => $remove,
            'max-number' => $max_number,
            'function' => 'get_user_element',
            'action' => 'get_user_search_result'
            );

        $tag[] = '<div class="'.implode(' ', $wrapper_classes).'" '.Common::dataAttr($data).'>';
        $tag[] = '<ul class="'.implode(' ', $display_classes).'" data-sort-wrapper="'.$sort_wrapper.'">'.implode('', $selected_posts).'</ul>';

        $tag[] = '<div class="search-wrapper"><input type="search" class="wide-fat sb-post-search" placeholder="'.$placeholder.'" name="sb-post-select" /><span class="spinner"></span>';
        $tag[] = '<ul class="search-results"></ul></div>';
        $tag[] = '<ul class="post-list"></ul>';
        $tag[] = '</div>';

        return Common::wrapper($args, $tag);

    }
}