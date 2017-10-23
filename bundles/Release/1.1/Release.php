<?php

namespace SB;

use SB\Utils;
use SB\Forms\Fields;
use SB\Forms\Common;

Release::init();

class Release
{

    public static function init()
    {

        add_action('admin_menu', array(__CLASS__, 'addOptionsPage'));
        add_action('template_redirect', array(__CLASS__, 'overrideVisitor'));
        // add_action('after_setup_theme', array(__CLASS__, 'addImageSize'));
        add_action('admin_init', array(__CLASS__, 'registerSettings'));
        add_action('admin_print_styles', array(__CLASS__, 'stylesheet'));
        add_action('admin_init', array(__CLASS__, 'preview'));
        add_action('admin_bar_menu', array(__CLASS__, 'adminBarStatus'), 999);

    }

    public static function registerSettings()
    {

        register_setting('sb-release', 'sb-release');
        register_setting('sb-release', 'sb-release-passwd');

        // add encryption to passwd
        add_filter('pre_update_option_sb-release-passwd', 'SB\Forms\Common::encrypt_password');

    }

    public static function addOptionsPage()
    {

        add_options_page('Release', 'Release', 'manage_options', 'sb-release', array(__CLASS__, 'form'));

    }

    public static function addImageSize()
    {

        // add_image_size('release_image', 398, 0, true); // false: soft proportional crop mode, true: hard crop mode.

    }

    public static function stylesheet()
    {

        wp_register_style('sb-release', Utils::get_bundle_uri('Release').'/css/admin.css');
        wp_enqueue_style('sb-release');

    }

    public static function checkTheme($theme_path)
    {

        $index = (file_exists($theme_path.'/index.html')) ? true : false;
        $style = (file_exists($theme_path.'/style.css')) ? true : false;

        if ($index && $style) {
            return true;
        }

        return false;

    }

    public static function getAllThemes()
    {

        $themes = array();

        $bundle_dir = dirname(__FILE__).'/themes/';
        $dir = dir($bundle_dir);

        while (false !== ($theme = $dir->read())) {
            if ($theme == '.' || $theme == '..') {
                continue;
            }
            if (self::checkTheme($dir->path.$theme)) {
                $themes[] = array($theme, $dir->path.$theme);
            }
        }


        $custom_dirs = array(
                get_template_directory().'/lib/',
                get_stylesheet_directory().'/lib/'
            );

        foreach ($custom_dirs as $custom_dir) {
            if (file_exists($custom_dir)) {
                $dir = dir($custom_dir);
                while (false !== ($theme = $dir->read())) {
                    if ($theme == '.' || $theme == '..') {
                        continue;
                    }
                    if ($theme == 'Release') {
                        $current_theme = wp_get_theme();
                        if (self::checkTheme($dir->path.$theme)) {
                            $themes[] = array($current_theme->get('Name'), $dir->path.$theme);
                        }
                    }
                }

            }

        }

        return $themes;

    }

    public static function getThemeOptions()
    {

        $themes = self::getAllThemes();
        $options = array();

        foreach ($themes as $theme) {
            $options[$theme[1]] = $theme[0];
        }

        return $options;

    }

    public static function data()
    {

        $settings = get_option('sb-release', self::$defaults);
        $settings = wp_parse_args($settings, self::$defaults);
        $settings['passwd'] = get_option('sb-release-passwd', false);

        return $settings;

    }

    public static $defaults = array(

        'status'    => false,
        'users'     => array(1),
        'theme'     => 1,
        'image'     => -1,
        'headline'  => false,
        'text'      => false,
        'css'       => false

    );

    public static function form()
    {

        ?>

        <div class="wrap sb-options">
            <h2>Release</h2>
            <form action="options.php" method="post" id="poststuff" class="options">
                <?php settings_fields('sb-release'); ?>
                <table class="form-table sb-release">
                    <?php

                        $settings = self::data();
                        extract($settings);

                        echo Fields::toggle(array(
                                'name'          => 'sb-release[status]',
                                'label'         => 'Dölj webbsidan för besökare',
                                'value'         => $status,
                                'wrapper'       => 'table',
                                'on'            => 'Dölj',
                                'off'           => 'Visa'
                            ));

                    ?>

                    <tr>
                        <th scope="row">
                            <label for="sb-release[users]">Visa webbsidan för följande inloggade användare</label>
                        </th>
                        <td valign="top">
                        <?php

                        $count = 0;
                        $all_users = get_users(array('fields' => array('ID', 'display_name')));

                        foreach ($all_users as $user) {
                            $count++;

                            $checked = (array_key_exists($user->ID, $users) || $user->ID == 1) ? true : false;
                            $disabled = ($user->ID == 1) ? true : false;

                            echo Fields::checkbox(array(
                                'name'          => 'sb-release[users]['.$user->ID.']',
                                'disabled'      => $disabled,
                                'label'         => $user->display_name,
                                'value'         => $checked,
                                'class'         => 'users'
                                ));

                            echo '<br />';

                            if (20 < $count) {
                                break;
                            }

                        }

                        ?>
                        </td>
                    </tr>

                    <?php

                        echo Fields::password(array(
                            'name'          => 'sb-release-passwd',
                            'label'         => 'Visa webbsidan för användare som anger följande lösenord',
                            'value'         => $passwd,
                            'wrapper'       => 'table',
                            'description'   => 'Lämna tomt om du inte vill använda funktionen.<br />
                                                Valt release-tema måste även ha stöd för lösenord.'
                            ));

                        $themes = self::getThemeOptions();

                        echo Fields::select(array(
                            'name'          => 'sb-release[theme]',
                            'label'         => 'Tema',
                            'value'         => $theme,
                            'wrapper'       => 'table',
                            'data'          => $themes
                            ));

                        echo Fields::image(array(
                            'name'          => 'sb-release[image]',
                            'label'         => 'Bild',
                            'image_size'    => 'medium',
                            'value'         => $image,
                            'wrapper'       => 'table',
                            ));

                        echo Fields::text(array(
                            'name'          => 'sb-release[headline]',
                            'label'         => 'Rubrik',
                            'value'         => $headline,
                            'wrapper'       => 'table',
                            ));

                        echo Fields::textarea(array(
                            'name'          => 'sb-release[text]',
                            'label'         => 'Text',
                            'value'         => $text,
                            'wrapper'       => 'table',
                            'rows'          => 5,
                            ));

                        echo Fields::textarea(array(
                            'name'          => 'sb-release[css]',
                            'label'         => 'Extra CSS',
                            'value'         => $css,
                            'wrapper'       => 'table',
                            'rows'          => 20,
                            'html'          => true,
                            ));

                    ?>

                </table>
                <p class="release-actions">
                    <?php

                        global $pagenow;
                        $current_screen = admin_url($pagenow.'?page='.Utils::getString('page').'&preview=1');

                    ?>
                    <input class="button-primary" name="save" type="submit" value="Spara" />
                    <a class="show-screen button button-secondary" target="_blank" href="<?php echo $current_screen; ?>">
                        Visa
                    </a>
                </p>
            </form>
        </div>
        <div class="release-information">

            <h2>Information för utvecklare</h2>
            <p>
                Ett tema-specifikt tema kan läggas i lib/Release.<br />
                Varje release-tema består av minst index.html och style.css.<br /><br />
                Inställningarna ovan skrivs ut i mallen på följande vis:<br />
                {{headline}}<br />
                {{text}}<br />
                {{image bildstorlek}}, t ex {{image large}} skriver ut URL till bild.<br />
                {{uri}} skriver ut URL till temats folder.<br />
                {{password}} skriver ut inputfält för lösenord om lösenord är satt.
            </p>

        </div>

        <?php

    }

    public static function hasPassword()
    {

        return get_option('sb-release-passwd', false);

    }

    public static function validatePassword()
    {

        $encrypted_password = self::hasPassword();

        if (false == $encrypted_password) {
            return false;
        }

        if (!empty($_SESSION['release_validated']) && $encrypted_password === $_SESSION['release_validated']) {
            return true;
        }

        $posted_password = Utils::postString('passwd');

        if (empty($posted_password)) {
            return false;
        }

        $password = Common::decrypt_password($encrypted_password);

        if ($password == $posted_password) {
            $_SESSION['release_validated'] = $encrypted_password;
            return true;
        } else {
            $_SESSION['release_validated'] = false;
        }

    }

    public static function overrideVisitor()
    {

        $settings = get_option('sb-release', self::$defaults);
        $settings = wp_parse_args($settings, self::$defaults);

        if (1 == $settings['status']) {
            if (is_user_logged_in()) {
                global $current_user;

                if (1 == $current_user->ID || // original admin always let in
                    array_key_exists($current_user->ID, $settings['users'])) { // either a user or a validated pass
                    return;
                }

            }

            if (self::validatePassword()) { // user has validated password
                return;
            }

            self::printReleasePage($settings);
            die;

        }

    }

    public static function preview()
    {

        if (!Utils::getString('preview')) {
            return;
        }

        $settings = get_option('sb-release', self::$defaults);
        self::printReleasePage($settings);
        die();

    }

    public static function printReleasePage($settings)
    {

        $template = file_get_contents($settings['theme'].'/index.html');

        if (false !== strpos($settings['theme'], '/bundles/Release/')) {
            $theme_path = Utils::get_bundle_uri('Release').'/themes/'.basename($settings['theme']);
        } else {
            $theme_path = get_template_directory_uri().'/lib/Release';
        }

        $style = array();
        $style[] = '<link rel="stylesheet" type="text/css" media="all" href="'.$theme_path.'/style.css" />';
        if (!empty($settings['css'])) {
            $style[] = '<style type="text/css">';
            $style[] = $settings['css'];
            $style[] = '</style>';
        }

        preg_match('/{{image\s(.*)}}/', $template, $image_size);
        $image_size = (empty($image_size[1])) ? 'large' : $image_size[1];

        $image = false;
        if ($settings['image'][0]) {
            $image_src = wp_get_attachment_image_src($settings['image'][0], $image_size);
            $image = $image_src[0];
        }

        $error_message = 'Fel lösenord.';
        $error_message = apply_filters('sb_release_error_message', $error_message);
        $error_class = (isset($_SESSION['release_validated']) && false == $_SESSION['release_validated']) ? 'show-error' : false;

        $password = array();

        if (self::hasPassword()) {
            $password[] = '<form class="'.$error_class.'" method="post">';
            $password[] = '<input type="password" name="passwd"><input type="submit" value="Logga in">';
            $password[] = '<p class="message">'.$error_message.'</p>';
            $password[] = '</form>';
        }

        $template = str_replace('{{uri}}', $theme_path, $template);
        $template = str_replace('{{stylesheet}}', implode("\n", $style), $template);
        $template = str_replace('{{title}}', get_bloginfo('name'), $template);
        $template = str_replace('{{headline}}', $settings['headline'], $template);
        $template = str_replace('{{text}}', $settings['text'], $template);
        $template = str_replace('{{password}}', implode("\n", $password), $template);
        $template = preg_replace('/({{image\s.+}})/', $image, $template);

        echo $template;
        exit();

    }

    public static function adminBarStatus($wp_admin_bar)
    {

        $settings = get_option('sb-release', self::$defaults);
        $settings = wp_parse_args($settings, self::$defaults);

        $title = (1 == $settings['status']) ? 'OBS! Webbsidan döljs för besökare!' : false;

        $args = array(
            'id' => 'release_status',
            'title' => $title,
            'meta'  => array('class' => 'active')
            );

        if ($title) {
            $wp_admin_bar->add_node($args);
        }


    }
}
