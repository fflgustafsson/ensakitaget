<?php

namespace SB\Forms;

use SB\Forms;
use SB\Utils;
use SB\Forms\Fields;
use SB\Forms\Options;

class Common
{

    public static function validateArgs($field, $args)
    {

        // $defaults = array(
        //  'id'            => false,
        //  'name'          => $field,
        //  'type'          => $field,
        //  'orgname'       => false,
        //  'label'         => $field,
        //  'current_value' => false,
        //  'class'         => strtolower($field),
        //  'disabled'      => false,
        //  'remove'        => '&ndash;',
        //  'print_label'   => true,
        //  'auto_value'    => false
        //  );

        if (empty($args['name'])) {
            return self::error(__FUNCTION__, $args);
        }

        $illegal_chars = array('[', ']');

        $defaults = array(
            'type'  => $field,
            'id'    => null, // to-do
            'name'  => $field,
            'label' => Utils::mbUcfirst($args['name']),
            'class' => str_replace($illegal_chars, '', 'sb-'.$field.'-'.strtolower($args['name'])),
            'disabled' => false,
            'placeholder' => null,
            'required' => null,
            'value' => null,
            'default' => null,
            'force_default' => false, // to-do
            'remove' => '&ndash;',
            'print_label' => true,
            'auto_value' => false,
            );

        $args = wp_parse_args($args, $defaults);

        // $args = self::create_id($args);
        // $args = self::add_name_index($args);

        return $args;

    }

    public static function error($field, $args)
    {

        Utils::debug('ERROR in field of type '.$field.' with following arguments');
        Utils::debug($args);
        return $args;

    }

    public static function isDisabled($string)
    {

        if ($string) {
            return 'disabled';
        }

        return false;

    }

    public static function isRequired($string)
    {

        if ($string) {
            return 'required';
        }

        return false;

    }

    public static function wrapper($args, $tag, $wrapper = 'div')
    {

        // debug($args);

        extract($args);
        $description = (isset($description)) ? '<p class="description">'.$description.'</p>' : false;
        $tag = (is_array($tag)) ? implode('', $tag) : $tag;
        $type = str_replace('_', '-', $type);
        $label = ($print_label) ? sprintf('<label for="%s">%s</label>', $name, $label) : null;

        if (false === $wrapper) {
            return $label.$tag.$description;
        }

        if ('table' == $wrapper) {
            $row = '<tr valign="top" class="sb-forms %s %s"><th scope="row">%s</th><td>%s%s</td></tr>';
            return sprintf($row, 'sb-'.$type, $class, $label, $tag, $description);
        } else {
            $div = '<div class="sb-forms %s %s">%s%s%s</div>';
            return sprintf($div, 'sb-'.$type, $class, $label, $tag, $description);
        }

    }

    public static function removeBaseArgs($args)
    {

        $keys_to_remove = array(
            'id',
            'name',
            'label',
            'value',
            'class',
            'type',
            'description',
            'wrapper',
            'remove',
            'print_label',
            'placeholder',
            'disabled',
            'required',
            'default',
            'force_default'
            );
        foreach ($keys_to_remove as $key) {
            if (array_key_exists($key, $args)) {
                unset($args[$key]);
            }
        }
        return $args;

    }

    public static function getMediaElement($media_id, $size = 'thumbnail', $default = false, $remove = '&ndash;', $name = false)
    {

        $size = (!in_array($size, get_intermediate_image_sizes())) ? 'thumbnail' : $size;
        $img_tag = false;

        if ($media_id < 0) {
            if ($default) {
                $img_tag = '<img src="'.get_bloginfo('template_url').$default.'" alt="sb-media">';
                $post_title = 'Förinställd';
            }
        } else {
            $img_tag = wp_get_attachment_image($media_id, $size, true);
            $post_title = _draft_or_post_title($media_id);
        }

        if (empty($img_tag)) {
            return '<li class="sb-media-attachment hidden" data-id="-1"><input type="hidden" name="'.$name.'[]" value="-1"></li>';
        }

        $remove = (!$remove || $remove == 'false') ? false : '<a href="javascript:void(0)" class="remove-selected" data-id="'.$media_id.'">'.$remove.'</a>';
        $hidden = '<input type="hidden" name="'.$name.'[]" value="'.$media_id.'">';

        $admin_url = admin_url('upload.php?item='.$media_id);

        $return = array();
        $return[] = '<li class="sb-media-attachment" data-id="'.$media_id.'">';
        $return[] = '<a class="cover" href="'.$admin_url.'" target="_blank"></a>';
        $return[] = $img_tag.'<span class="post-title">'.$post_title.'</span>';
        $return[] = $remove;
        $return[] = $hidden;
        $return[] = '</li>';

        return implode("\n", $return);

    }

    public static function checkForDeletedFiles($value)
    {

        if (empty($value) || 0 > $value) {
            return array();
        }

        $return = array();
        foreach ($value as $id) {
            if (0 > $id) {
                return $value;
            }
            $p = get_post($id);
            if ($p) {
                $return[] = $id;
            }
        }
        return $return;

    }

    public static function getDefault($name)
    {

        $default = false;
        foreach (Options::$instances as $data_set) {
            foreach ($data_set as $page => $data) {
                if (!empty($data['fields'][$name]['default'])) {
                    $default = $data['fields'][$name]['default'];
                }
            }
        }

        return $default;

    }

    public static function getPostElement($post_id, $remove = '&ndash;', $name = false, $load_data_via_ajax = false, $meta_key = false, $image_meta = false)
    {

        $post = get_post($post_id);

        if (empty($post)) {
            return false;
        }

        $pt_obj = get_post_type_object($post->post_type);
        $post_title = _draft_or_post_title($post->ID);
        $post_type = $pt_obj->labels->singular_name;

        if ($image_meta == '_thumbnail_id') {
            $image_id = get_post_thumbnail_id($post_id);
        } else {
            $image_id = get_post_meta($post_id, $image_meta, true);
            if (!empty($image_id) && is_array($image_id)) {
                $image_id = $image_id[0];
            }
        }

        $img = wp_get_attachment_image_src($image_id, 'thumbnail');
        $post_img = (!empty($img)) ? $img[0] : false;

        $img_tag = ($post_img) ? '<img class="post-img" src="'.$post_img.'" />' : '<div class="missing-image"></div>';
        $remove_tag = (!$remove || $remove == 'false') ? false : '<a href="" title="Ta bort" class="remove-selected">'.$remove.'</a>';

        $meta_value = (isset($post->$meta_key)) ? $post->$meta_key : false;
        if (!$meta_value) {
            $meta_value = get_post_meta($post_id, $meta_key, true);
        }

        $cached_meta_value = $meta_value;
        $meta_value = apply_filters('SB_Post_Element_Meta_Value', $meta_value, $meta_key, $post_id);

        // Shorted unfiltered meta values
        if ($meta_value == $cached_meta_value) {
            $meta_value = Utils::shorten($meta_value, 50);
        }

        $element[] = '<li class="post-element" data-id="'.$post_id.'">';
        $element[] = $img_tag;
        $element[] = '<span class="post-title">'.$post_title.'</span>';
        $element[] = '<span class="post-type">'.$post_type.'</span>';
        $element[] = '<span class="post-meta">'.$meta_value.'</span>';

        // if( !empty($meta_fields) ){
        //  foreach( $meta_fields as $meta_field => $index ){
        //      $value = get_post_meta($post_id, $meta_field, true);
        //      if( is_array($value) ){
        //          $value = $value[$index];
        //      }

        //      if( strlen( $value ) > 50 ){
        //          $value = Utils::shorten( $value, 50, '&hellip;', true );
        //      }

        //      $element[] = '<span class="post-meta">'.$value.'</span>';
        //  }
        // } else {
        //  $element[] = '<span class="post-date">'.$post_date.'</span>';
        // }

        $element[] = $remove_tag;
        $element[] = '<input type="hidden" value="'.$post_id.'" name="'.$name.'[]">';
        $element[] = '</li>';

        $element = implode('', $element);

        return $element;

    }

    public static function getUrl($name, $post_id = false, $default = true)
    {
        // was name, post_meta, post_id, default

        // Post_meta
        // get_url('_url', $post->ID);
        //
        // Option
        // get_url('_url');
        //
        // w/o option defaults
        // get_url('_url', false, false);

        if ($post_id === true) {
            Utils::debug('Possible misuse since function change', true);
        }

        if (is_numeric($post_id)) {
            $data = get_post_meta($post_id, $name, true);
        } else {
            $default = ($default) ? self::getDefault($name) : false;
            $data = get_option($name, $default);
        }

        if (empty($data)) {
            return false;
        }

        $default_data = array('id' => null, 'url' => $data, 'target' => 0, 'text' => false);

        if (!is_array($data)) {
            $data = $default_data;
        } else {
            $data = wp_parse_args($data, $default_data);
        }

        $target = ($data['target']) ? 'target="_blank"' : false;

        if (!empty($data['id']) && is_numeric($data['id'])) {
            return array(
                'url' => str_replace(Utils::wpSiteurl(), '', get_permalink($data['id'])),
                'target' => $target,
                'text' => $data['text']);
        }

        return array('url' => $data['url'], 'target' => $target, 'text' => $data['text']);

        // if (!empty($data['id'])) {
        //  // if custom || post_id
        //  if (is_numeric($data['id'])) {
        //      return str_replace(Utils::wp_siteurl(), '', get_permalink($data['id']));
        //  } else {
        //      $custom_item = SB\NavMenu::get_custom_item_data($data['id']);
        //      if ($custom_item) {
        //          if ($custom_item['type'] == 'custom_post_type') {
        //              return str_replace(Utils::wp_siteurl(), '', get_post_type_archive_link($custom_item['id']));
        //          } else {
        //              return $custom_item['uri'];
        //          }
        //      }
        //  }

        // }

        // return $data['url'];

    }

    // Quickfix: if user wants to be able to return an empty field, set default to false
    public static function getOption($name, $default = true)
    {

        $default = ($default) ? self::getDefault($name) : false;

        $value = get_option($name);
        $value = (is_array($value)) ? array_filter($value, 'htmlspecialchars') : htmlspecialchars($value);

        if (false === $default) {
            return $value;
        }

        if (empty($default)) {
            return $value;
        }

        if (empty($value)) {
            return $default;
        }

        return $value;

    }

    public static function encrypt_password($password)
    {
        Utils::deprecated('encrypt_password', 'encryptPassword');
        return self::encryptPassword($password);
    }

    public static function encryptPassword($password)
    {

        if (empty($password)) {
            return $password;
        }

        if (!is_callable('mcrypt_encrypt')) {
            Utils::debug('ERROR You need to install Mcrypt to use password encryption.');
            return $password;
        }

        $key = hash('SHA256', SECURE_AUTH_KEY, true);
        srand();
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC), MCRYPT_RAND);
        if (strlen($iv_base64 = rtrim(base64_encode($iv), '=')) != 22) {
            Utils::debug('ERROR in encryption.');
            return $password;
        }
        $encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $password . md5($password), MCRYPT_MODE_CBC, $iv));

        return $iv_base64 . $encrypted;

    }

    public static function decryptPassword($password)
    {

        if (empty($password)) {
            return $password;
        }

        if (!is_callable('mcrypt_decrypt')) {
            Utils::debug('ERROR You need to install Mcrypt to use password decryption.');
            return $password;
        }

        $key = hash('SHA256', SECURE_AUTH_KEY, true);
        $iv = base64_decode(substr($password, 0, 22) . '==');
        $password = substr($password, 22);
        $decrypted = rtrim(mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, base64_decode($password), MCRYPT_MODE_CBC, $iv), "\0\4");
        $hash = substr($decrypted, -32);
        $decrypted = substr($decrypted, 0, -32);
        if (md5($decrypted) != $hash) {
            Utils::debug('ERROR in decryption.');
            return $password;
        }

        return $decrypted;

    }

    public static function timestampToDate($date, $format)
    {

        if (empty($date)) {
            return $date;
        }

        $formats = array(
            'mm/dd/yy' => 'm/d/Y',
            'yy-mm-dd' => 'Y-m-d',
        );

        if (!empty($formats[$format])) {
            return date_i18n($formats[$format], $date);
        } else {
            Utils::debug('ERROR:: cant match date format: '.$format);
        }

        return $date;

    }

    public static function dateToTimestamp($date)
    {

        return strtotime($date);

    }

    public static function elementData($data_array)
    {

        $output = array();

        foreach ($data_array as $name => $value) {
            $output[] = 'data-'.$name.'="'.$value.'"';
        }

        return implode(' ', $output);

    }

    public static function dataAttr($data)
    {

        $data_attr = array();

        foreach ($data as $key => $value) {
            $data_attr[] = 'data-'.$key.'="'.$value.'"';
        }

        return implode(' ', $data_attr);

    }

    public static function getUserElement($user_id, $remove = '&ndash;', $name = false, $load_data_via_ajax = false)
    {

        $user = get_user_by('id', $user_id);

        if (empty($user)) {
            return false;
        }

        $img_tag = get_avatar($user_id, 'thumbnail');
        $remove_tag = (!$remove || $remove == 'false') ? false : '<a href="" title="Ta bort" class="remove-selected">'.$remove.'</a>';

        $element = array();

        $element[] = '<li class="post-element" data-id="'.$user_id.'">';
        $element[] = $img_tag;
        $element[] = '<span class="post-title">'.$user->display_name.' ('.$user->user_login.')</span>';
        $element[] = '<span class="post-meta">'.$user->user_email.'</span>';

        $element[] = $remove_tag;
        $element[] = '<input type="hidden" value="'.$user_id.'" name="'.$name.'[]">';
        $element[] = '</li>';

        $element = implode('', $element);

        return $element;

    }


    // FIXME rewrite following functions with new structure

    // public static function remove_settings($page_key = 'all') // active by setting varible $wh_options_debug to true
    // {
    //  global $wh_options_debug;
    //  if (!$wh_options_debug) return;
    //  foreach (Options::$defaults as $page => $data) {
    //      if ($page_key != 'all' && $page_key != $page) continue;
    //      if (empty($data)) return;
    //      foreach ($data as $option => $values) {
    //          console('debug: deleting option: '.$option);
    //          delete_option($option);
    //      }
    //  }
    // }

    // private static function get_desc($args)
    // {
    //  if (isset($args['description']) && !empty($args['description'])) return $args['description'];
    //  return false;
    // }

    // public static function create_id($args)
    // {

    //  extract($args);

    //  if (isset($index) && $id) {
    //      $id = $id.'-'.$index;
    //  }

    //  if ($id) {
    //      $args['id'] = 'id="'.$id.'"';
    //      return $args;
    //  }

    //  return $args;

    // }

    // public static function add_name_index($args)
    // {

    //  extract($args);
    //  $args['orgname'] = $name;

    //  if (isset($index) && false !== strpos($name, '[]')) {
    //      $args['name'] = str_replace('[]', '['.$index.']', $name);
    //  }

    //  return $args;

    // }









    // private static function get_image_dimensions($image_size)
    // {
    //  global $_wp_additional_image_sizes;

    //  if (!in_array($image_size, get_intermediate_image_sizes())) return;

    //  if (!empty($_wp_additional_image_sizes) && array_key_exists($image_size, $_wp_additional_image_sizes)) {

    //      return $size = array($_wp_additional_image_sizes[$image_size]['width'], $_wp_additional_image_sizes[$image_size]['height']);
    //  } else {
    //      $size[0] = get_option($image_size.'_size_w');
    //      $size[1] = get_option($image_size.'_size_h');
    //      return $size;
    //  }
    // }
}
