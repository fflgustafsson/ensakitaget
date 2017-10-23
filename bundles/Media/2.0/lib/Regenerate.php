<?php

namespace SB\Media;

use SB\Utils;

class Regenerate
{

    public static function init()
    {

        add_action('admin_enqueue_scripts', array(__CLASS__, 'javascript'));
        add_action('admin_print_styles', array(__CLASS__, 'stylesheet'));
        add_action('admin_menu', array(__CLASS__, 'addPage'));
        add_filter('media_row_actions', array(__CLASS__, 'addRegenerateImageLink'), 10, 2);

        // AJAX
        add_action('wp_ajax_regenerate_image', array(__CLASS__, 'regenerateImage'));
        add_action('wp_ajax_get_all_attachment_ids', array(__CLASS__, 'getAllAttachmentIds'));

    }

    public static function javascript()
    {

        wp_register_script('sb-regenerate-media', Utils::getBundleUri('Media').'/js/regenerate.min.js', 'jquery', '1', true);
        wp_enqueue_script('sb-regenerate-media');

    }

    public static function stylesheet()
    {

        wp_register_style('sb-progress', Utils::getBundleUri('Media').'/css/progress.css');
        wp_enqueue_style('sb-progress');

    }

    public static function addPage()
    {
        add_media_page('Generera om media', 'Generera om media', 'manage_options', 'regenerate_images', array(__CLASS__, 'page'));
    }

    public static function regenerateImage($request = false) // AJAX
    {

        $id = Utils::postString('media_id');
        $type = Utils::postString('type');

        if ($type == 'all') {
            if (!Utils::isAjaxRequest()) {
                return;
            }
            if (wp_attachment_is_image($id)) {
                $status = self::processImage($id);
                $json['status'] = $status;
            } else {
                $json['status'] = array('error' => 'Resursen är inte en bild.');
            }

        } elseif ($type == 'batch') {
            // FIXME batch generate
        } else {
            if (!Utils::isAjaxRequest()) {
                return;
            }

            if (wp_attachment_is_image($id)) {
                $status = self::processImage($id);
                if (isset($status['status']) && $status['status'] == 'OK') {
                    $json['status'] = '<div id="message" class="updated below-h2"><p><strong>Alla storlekar skapade på nytt.</strong></p></div>';
                } elseif (isset($status['error'])) {
                    $json['status'] = '<div class="error"><p><strong>'.$status['error'].'.</strong></p></div>';
                } else {
                    $json['status'] = '<div class="error"><p><strong>Något gick snett.</strong></p></div>';
                }

            }

        }

        header('Content-Type: application/json');
        echo json_encode($json);
        die();

    }

    public static function processImage($image_id)
    {
        @set_time_limit(900);
        require_once(ABSPATH.'wp-admin/includes/image.php');
        $image_path = get_attached_file($image_id);

        $file_array = explode('/', $image_path);
        $file = end($file_array);
        $new_file = sanitize_file_name($file);
        array_splice($file_array, -1, 1, array($new_file));
        $new_image_path = implode('/', $file_array);

        rename($image_path, $new_image_path);
        update_attached_file($image_id, $new_image_path);

        $return = array();

        if (!file_exists($new_image_path)) {
            $return['error'] = 'Orginalbilden kan ej hittas';
            return $return;
        }

        $metadata = wp_generate_attachment_metadata($image_id, $new_image_path);

        if (is_wp_error($metadata)) {
            $return['error'] = 'Bild kan ej skapas om';
            return $return;
        }

        if (!empty($metadata)) {
            wp_update_attachment_metadata($image_id, $metadata);
            $return['status'] = 'OK';
            $return['file'] = $metadata['file'];
        }
        Utils::console('NOTICE: regeneration of image id '.$image_id.' done!');
        return $return;
    }

    public static function addRegenerateImageLink($actions, $post)
    {
        if (!wp_attachment_is_image($post->ID) || ! current_user_can('manage_options')) {
            return $actions;
        }

        $actions['regen_link'] = '<a href="#" class="regenerate-image" data-image-id="'.$post->ID.'">Generera om media</a>';
        return $actions;
    }

    public static function getAllAttachmentIds()
    {
        $images = get_posts(array(
            'posts_per_page'    => -1,
            'post_type'         => 'attachment',
            ));
        $return = array();
        foreach ($images as $i => $obj) {
            $return[] = $obj->ID;
        }

        if (Utils::is_ajax_request()) {
            header('Content-Type: application/json');
            echo json_encode($return);
            die();
        }
        return $return;
    }

    public static function page()
    {

        ?>
        <div class="wrap sb-options" style="width: 600px;">
            <?php screen_icon('tools'); ?>
            <h2>Generera om media</h2>
            <form method="post" id="poststuff" class="options sb-options">
                <?php wp_nonce_field('sb_settings', 'sb_settings'); ?>
                <p class="description">
                    Detta verktyg genererar om samtliga media-storlekar utfrån orginalbilden.<br />
                    Det kan ta ett tag, speciellt om det är många bilder. Stäng ej ner eller ladda om fönstret.
                    <br />Ha tålamod!
                </p>
                <div class="progress-bar-wrapper">
                    <div class="progress"></div>
                    <span>0</span>
                </div>
                <ul class="regenerate-all-response">

                </ul>
                <p class="submit">
                    <span class="spinner"></span>
                    <input id="regenerate-all" class="button-primary" name="save" type="submit" value="Starta regenerering" />
                </p>
            </form>
        </div>
        <?php

    }
}