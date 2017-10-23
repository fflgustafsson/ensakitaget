<?php

namespace SB\User;

use SB\User;
use SB\Utils;
use SB\Forms\Fields;
use SB\Media;

class MetaData
{

    // TODO
    // Add columns

    public static $defaults = array(
        'headline'  => 'Din profil',
        'meta_data' => array()
        );

    public function __construct($args)
    {

        $args = wp_parse_args($args, self::$defaults);

        foreach ($args as $key => $value) {
            $this->$key = $value;
        }

        add_action('personal_options_update', array($this, 'saveProfile'));
        add_action('edit_user_profile_update', array($this, 'saveProfile'));

        add_action('show_user_profile', array($this, 'editProfile'), 10);
        add_action('edit_user_profile', array($this, 'editProfile'), 10);

        add_action('admin_print_styles', array($this, 'stylesheet'));

        add_filter('get_avatar', array($this, 'getAvatar'), 10, 2);

    }

    public static function register($args)
    {

        if (empty($args['meta_data'])) {
            return false;
        }

        User::$meta_data = $args['meta_data'];

        return new self($args);

    }

    public static function stylesheet()
    {

        wp_register_style('sb-profile-css', Utils::getBundleUri('User').'/css/profile.css', false, 1);
        wp_enqueue_style('sb-profile-css');

    }

    public function saveProfile()
    {

        $user_id = Utils::postInt('user_id');

        if (!wp_verify_nonce(Utils::postString('_wpnonce'), 'update-user_'.$user_id)) {
            return false;
        }

        foreach ($this->meta_data as $key => $data) {
            update_user_meta($user_id, $key, Utils::postVar($key));
        }

    }

    public function editProfile($user)
    {

        if (!empty($this->headline)) {
            echo '<h3>'.$this->headline.'</h3>';
        }
        echo '<table class="form-table">';
        echo '<tbody>';

        foreach ($this->meta_data as $name => $data) {
            $args = $data;
            $default = (!empty($data['default'])) ? $data['default'] : false;

            $args['name'] = $name;
            $args['value'] = get_the_author_meta($name, $user->ID);
            $args['wrapper'] = 'table';

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

        echo '</tbody>';
        echo '</table>';

    }

    public function getAvatar($tag, $user_id)
    {

        $meta_key = apply_filters('SB_user_meta_data_avatar', '_avatar');

        $media_id = get_the_author_meta($meta_key, $user_id);

        if (empty($media_id)) {
            return false;
        }

        $image = Media::get(current($media_id), 'thumbnail');
        $image = '<img class="sb-meta-data-avatar" src="'.$image['src'][0].'" alt="avatar-40">';

        return $image;

    }
}
