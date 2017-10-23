<?php

namespace SB;

use SB\Utils;
use SB\Modules;
use SB\Media;
use SB\Render;
use SB\Forms\Fields;

Modules::config(array(
    // 'show_visiblity'     => false,
    // 'show_handle_page'   => false,
    'add_meta_box'       => array('page'),
    'meta_box_title'    => 'Moduler'
    ));

Modules::registerType(array(
    'name' => 'HomeModuleImage',
    'description' => 'Modul som visar en feed från LinkedIn'
    )); // Formatted for wp_enqueue_script, either as a single array or multiple
Modules::registerType(array(
    'name' => 'HomeModuleVideo',
    'description' => 'Modul som visar en feed från LinkedIn'
    )); // Formatted for wp_enqueue_script, either as a single array or multiple

Modules::registerType(array(
    'name' => 'Youtube',
    'description' => 'Youtube ID'
    // This type has no render method, which can either be a specified function/method or the default ClassName::render method.
    // Could also be a file, by default /theme_folder/modules/template-{name in lowercase}.php
    ));
Modules::registerType(array(
    'name' => 'TextImageModule',
    'description' => 'Youtube ID'
    // This type has no render method, which can either be a specified function/method or the default ClassName::render method.
    // Could also be a file, by default /theme_folder/modules/template-{name in lowercase}.php
    ));

/*Modules::registerType(array(
    'name' => 'Slideshow',
    'form' => 'SB\\Slideshow::admin',
    'template' => 'SB\\Slideshow::output',
    'description' => 'En karusell',
    'javascript' => array(
            array('slideshow', get_bloginfo('template_url').'/js/slideshow.js', array('jquery'), 2)
        ),
    'init' => 'SB\\Slideshow::setup' // init parameter is executed on register if callable
    ));*/

class HomeModuleImage
{

    // If module name corresponds with Class and we have an init method, it is excecuted on register
    public static function init()
    {

        // Utils::debug('NOTICE: '.__CLASS__.'::init executed on registerType');

    }

    public static function render($module)
    {


        $image_id = get_post_meta($module->ID, '_home_module_image_src', true );
        $module_title = get_post_meta($module->ID, '_home_module_image_title', true );
        $module_content = get_post_meta($module->ID, '_home_module_image_content', true );

        $image = Media::get( $image_id[0] , 'full' );
        
        echo '<div class="module home home-image">';
        Render::image($image_id[0], 'module-image-third');
        echo '<div class="text-container"><h3>' . $module_title . '</h3>' . apply_filters("the_content", $module_content) . '</div></div>';

    }

    public static function form($post, $module_args)
    {

        echo Fields::text(array(
            'name'  => '_home_module_image_title',
            'label' => 'Title',
            'auto_value' => true,
            ));

        echo Fields::editor(array(
            'name'  => '_home_module_image_content',
            'label' => 'Content',
            'auto_value' => true,
            ));

        echo Fields::image(array(
            'name'  => '_home_module_image_src',
            'label' => 'Image',
            'auto_value' => true
            ));

    }
}

class HomeModuleVideo
{

    // If module name corresponds with Class and we have an init method, it is excecuted on register
    public static function init()
    {

        // Utils::debug('NOTICE: '.__CLASS__.'::init executed on registerType');

    }

    public static function render($module)
    {

        $video_id = get_post_meta($module->ID, '_home_module_video_id', true );
        $module_title = get_post_meta($module->ID, '_home_module_video_title', true );
        $module_content = get_post_meta($module->ID, '_home_module_video_content', true );
        
        echo '<div class="module home home-video"><div class="videowrapper"><iframe id="ytplayer" class="video" width="640" src="http://www.youtube.com/embed/' . $video_id . '?autoplay=0&amp;rel=0&amp;showinfo=0"></iframe></div><div class="text-container"><h3>' . $module_title . '</h3>' . apply_filters("the_content", $module_content) . '</div></div>';


    }

    public static function form($post, $module_args)
    {

        echo Fields::text(array(
            'name'  => '_home_module_video_title',
            'label' => 'Title',
            'auto_value' => true,
            ));

        echo Fields::editor(array(
            'name'  => '_home_module_video_content',
            'label' => 'Content',
            'auto_value' => true,
            ));

        echo Fields::text(array(
            'name'  => '_home_module_video_id',
            'label' => 'VideoID',
            'auto_value' => true,
            ));

    }
}

class Youtube
{

    public static function form()
    {

        echo Fields::text(array(
            'name'  => '_yt_id',
            'label' => 'Youtube ID',
            'auto_value' => true,
            ));
    }

    public static function render($module)
    {
        
        $video_id = get_post_meta($module->ID, '_yt_id', true );        
        echo '<div class="module youtube"><div class="videowrapper"><iframe id="ytplayer" class="video" width="640" src="http://www.youtube.com/embed/' . $video_id . '?autoplay=0&amp;rel=0&amp;showinfo=0""></iframe></div></div>';
    }
}

class TextImageModule
{

    // If module name corresponds with Class and we have an init method, it is excecuted on register
    public static function init()
    {

        // Utils::debug('NOTICE: '.__CLASS__.'::init executed on registerType');

    }

    public static function render($module)
    {

        $image_id = get_post_meta($module->ID, '_module_image', true );
        $image_align = get_post_meta($module->ID, '_module_img_align', true );
        $image_mobile_hide = get_post_meta($module->ID, '_module_img_mobile', true );
        $image_bg = get_post_meta($module->ID, '_module_img_bg_color', true );
        $module_title = get_post_meta($module->ID, '_module_title', true );
        $module_content = get_post_meta($module->ID, '_module_content', true );
        $module_bg = get_post_meta($module->ID, '_module_bg_color', true );
        $module_cta = get_post_meta($module->ID, '_module_cta', true );
        $module_cta_label = get_post_meta($module->ID, '_module_cta_label', true );
        $module_cta_link = get_post_meta($module->ID, '_module_cta_link', true );
        $module_cta_buy = get_post_meta($module->ID, '_module_cta_buy', true );
        $module_cta_classes = $module_cta_buy == "1" ? " buy" : "";
        
        $image = Media::get( $image_id[0] , 'full' );
        $hideImg =  $image_mobile_hide ? 'hide-img-mobile' : "";
        $classes = $module_bg . ' ' . $hideImg;

        
        if ($image_align === 'right') { ?>
            <div class="module text-image <?php echo $classes; ?>">
                <div class="mobile-image-container">
<?php               Render::image($image_id[0], 'module-image-half'); ?>
                </div>
                <div class="module-inner">
                    <div class="text-container">
                        <div class="text-inner-container">
                            <h3><?php echo  $module_title; ?></h3>
<?php                       echo apply_filters("the_content", $module_content); ?>
                        </div>
                    </div>
                    <div class="image-container right <?php echo  $image_bg; ?>">
<?php                   Render::image($image_id[0], 'module-image-half'); ?>
                    </div>
                </div><!-- module-inner -->
<?php           if( $module_cta ){ ?>
                    <a class="button cta<?php echo $module_cta_classes; ?>" href="<?php echo $module_cta_link; ?>"><?php echo $module_cta_label; ?></a>
 <?php          } ?>

            </div>

<?php       } else { ?>
            <div class="module text-image <?php echo $classes; ?>">
                <div class="module-inner">
                    <div class="image-container <?php echo  $image_bg; ?>">
<?php                   Render::image($image_id[0], 'module-image-half'); ?>
                    </div>
                    <div class="text-container">
                        <div class="text-inner-container">
                            <h3><?php echo  $module_title; ?></h3>
<?php                       echo apply_filters("the_content", $module_content); ?>
                        </div>
                    </div>
                </div><!-- module-inner -->
<?php           if( $module_cta ){ ?>
                    <a class="button cta<?php echo $module_cta_classes; ?>" href="<?php echo $module_cta_link; ?>"><?php echo $module_cta_label; ?></a>
 <?php          } ?>

            </div>
<?php       }

    }

    public static function form($post, $module_args)
    {

        echo Fields::text(array(
            'name'  => '_module_title',
            'label' => 'Title',
            'auto_value' => true,
            ));

        echo Fields::editor(array(
            'name'  => '_module_content',
            'label' => 'Content',
            'auto_value' => true,
            ));

        echo Fields::image(array(
            'name'  => '_module_image',
            'label' => 'Image',
            'auto_value' => true
            ));

        echo Fields::radio(array(
            'name' => '_module_img_align',
            'label' => 'Image Alignment',
            'data' => array(
                'left' => 'Left',
                'right' => 'Right'
            ),
            'default'       => 'left',
            'auto_value' => true,
        ));

        echo Fields::checkbox(array(
            'name'  => '_module_img_mobile',
            'label' => 'Hide image on mobile',
            'auto_value' => true,
            ));

        echo Fields::radio(array(
            'name' => '_module_bg_color',
            'label' => 'Module background color',
            'data' => array(
                'white' => 'White',
                'lgray' => 'Light gray',
                'mgray' => 'Medium gray',
                'blue' => 'Blue'
            ),
            'default'       => 'white',
            'auto_value' => true,
        ));

        echo Fields::radio(array(
            'name' => '_module_img_bg_color',
            'label' => 'Image Container background color',
            'data' => array(
                'transparent' => 'Transparent',
                'lgray' => 'Light gray',
                'mgray' => 'Medium gray',
                'blue' => 'Blue'
            ),
            'default'       => 'transparent',
            'auto_value' => true,
        ));

        echo Fields::checkbox(array(
            'name'  => '_module_cta',
            'label' => 'Call to action button',
            'auto_value' => true,
        ));

        echo Fields::text(array(
            'name'  => '_module_cta_label',
            'label' => 'CTA label',
            'auto_value' => true,
        ));

        echo Fields::text(array(
            'name'  => '_module_cta_link',
            'label' => 'CTA link',
            'auto_value' => true,
        ));
        echo Fields::checkbox(array(
            'name'  => '_module_cta_buy',
            'label' => 'CTA Buy button?',
            'auto_value' => true,
        ));

    }
}

class Slideshow
{

    public static function setup()
    {

        // Utils::debug('NOTICE: '.__CLASS__.'::setup executed on registerType');

    }

    public static function output($obj)
    {

        echo '<div class="module" style="background-color: lightblue;">';
        echo 'Slideshow!';
        echo '<h1 style="font-size: 7em">'.get_post_meta($obj->ID, '_slideshow_id', true).'</h1>';
        echo '</div>';

    }

    public static function admin()
    {

        echo Fields::text(array(
            'name'  => '_slideshow_id',
            'label' => 'Slideshow id',
            'auto_value' => true,
            ));

    }
}