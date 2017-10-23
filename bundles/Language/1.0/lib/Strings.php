<?php

namespace SB\Language;

use SB\Forms\Fields;
use SB\Utils;
use SB\Language;

class Strings
{

    public static $defaults = array(
        'menu_title'        => 'Översättning',
        'icon'              => 'dashicons-translation',
        'menu_pos'          => 98,
        'headline'          => 'Översättning',
        'strings'           => array(
                // 'label' => array('sv' => 'svenska', 'en' => 'engelska', 'rows' => 3)
            )
        );

    public static function init()
    {
        add_action('admin_menu', array(__CLASS__, 'adminMenu'));
        add_option('sb_langstrings', array(), false, 'yes');

    }

    public static function getStrings()
    {

        if (empty(Language::$strings)) {
            return self::$defaults;
        }

        self::$defaults['strings'] = Language::$strings;

        return self::$defaults;

    }

    public static function adminMenu()
    {

        $strings = self::getStrings();

        if (empty($strings)) {
            return false;
        }

        $hej = add_menu_page(
            $strings['menu_title'],
            $strings['menu_title'],
            'edit_others_posts',
            '_sb_lang_strings',
            array(__CLASS__, 'form'),
            $strings['icon'],
            $strings['menu_pos']
        );

    }

    public static function form()
    {

        $data = self::getStrings();
        extract($data);

        $message = self::saveData();
        $languages = Language::getLanguages();
        $default = Language::getDefault();
        $saved_strings = self::loadData();

        ?>

        <div class="wrap sb-langstrings-wrapper">
            <h2><?php echo $headline; ?></h2>
            <?php echo $message; ?>
            <form method="post" id="poststuff" class="sb-langstrings">
                <?php wp_nonce_field('lang_strings', '_sb_langstring_nonce'); ?>
                <table class="wp-list-table widefat fixed pages">
                    <thead>
                        <tr>
                            <th class="label">Ord</th>
                            <?php

                            foreach ($languages as $code => $lang) {
                                $description = ($code == $default) ? '<p class="description">Visas om annan översättning inte finns.</p>' : false;
                                echo '<th class="lang">'.$lang.$description.'</th>';
                            }

                            ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php

                        foreach ($strings as $label => $settings) {
                            $data = (empty($saved_strings[$label])) ? false : $saved_strings[$label];
                            self::formElement($label, $settings, $data);
                        }

                        ?>
                    </tbody>
                </table>
                <p class="submit"><input class="button-primary" name="save" type="submit" value="Spara" /></p>
            </form>
        </div>

        <?php

    }

    public static function formElement($label, $settings, $data)
    {

        // description
        $description = (empty($settings['description'])) ? false : '<p class="description">'.$settings['description'].'</p>';
        // rows
        $rows = (empty($settings['rows'])) ? 0 : $settings['rows'];
        $fn = (0 == $rows) ? 'text' : 'textarea';

        $languages = Language::getLanguages(); ?>

        <tr>
            <td>
                <label><?php echo $label ?></label>
                <?php echo $description; ?>
            </td>

            <?php

            foreach ($languages as $code => $lang) {
                $value = (empty($data[$code]) && !empty($settings[$code])) ? $settings[$code] : false; // set default
                $value = (isset($data[$code])) ? $data[$code] : $value; // load data
                $status = (empty($value)) ? 'missing-string' : false;

            ?>
                <td>
                    <?php echo Fields::$fn(array(
                            'label' => false,
                            'name' => 'strings['.$label.']['.$code.']',
                            'value' => $value,
                            'class' => $status,
                            'rows' => $rows,
                            'html' => true
                        )); ?>
                </td>

        <?php } ?>

        </tr>

        <?php

    }

    public static function saveData()
    {

        if (!Utils::postString('_sb_langstring_nonce')) {
            return;
        }

        if (!wp_verify_nonce(Utils::postString('_sb_langstring_nonce'), 'lang_strings')) {
            return;
        }

        if (!current_user_can('edit_others_posts')) {
            return;
        }

        update_option('sb_langstrings', stripslashes_deep(Utils::postArray('strings', false)));
        return '<div id="message" class="updated fade"><p><strong>Översättning sparad.</strong></p></div>';

    }

    public static function loadData()
    {
        return get_option('sb_langstrings', array());
    }

    public static function langString($string)
    {

        $default = Language::getDefault();
        $strings = get_option('sb_langstrings', false);

        $return = (empty($strings[$string][Language::lang()])) ? false : $strings[$string][Language::lang()];

        if (!$return && $default != Language::lang()) {
            $return = (empty($strings[$string][$default])) ? false : $strings[$string][$default];
        }

        if (!$return) {
            Utils::debug('Language string '.$string.' missing.');
        }

        return $return;

    }

}