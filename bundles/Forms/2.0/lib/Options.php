<?php

namespace SB\Forms;

use SB\Utils;
use SB\Forms\Common;
use SB\Forms;

class Options
{

    public static $instances = array();

    // icons
    // http://melchoyce.github.io/dashicons/
    //
    // position / either intval for menu position or slug for parent page, for example "upload.php" creates a sub menu page in Media.
    // 2 Dashboard
    // 4 Separator
    // 5 Posts
    // 10 Media
    // 15 Links
    // 20 Pages
    // 25 Comments
    // 59 Separator
    // 60 Appearance
    // 65 Plugins
    // 70 Users
    // 75 Tools
    // 80 Settings
    // 99 Separator
    //
    // data construct, array:
    //
    // level 1, option page slug
    // level 2, option page data (menu_name, headline, button_label, tabs(array), fields (array))
    // level 3, tabs
    // level 3, fields
    // -- level 1, field name
    // -- level 2, fn Field array() with specified type (fn)
    //
    // Example
    // $data = array(
    //  'common_settings' => array(
    //      'menu_name' => 'Allmänt',
    //      'headline' => 'Allmänna inställnignar',
    //      'button_label' => 'Spara inställningar',
    //      'tabs' => array(
    //          'Texter' => array('sb_email', 'sb_select'),
    //          'Övrigt' => array('sb_date_from', 'sb_radiobutton')
    //          ),
    //      'fields' => array(
    //          'sb_date_from' => array(
    //              'type'          => 'date',
    //              'label'         => 'Något är giltig från',
    //              'description'   => 'Fyll i ett datum',
    //          ),
    //          'sb_email' => array(
    //              'id'            => 'email',
    //              'type'          => 'text',
    //              'label'         => 'Email, expedition',
    //              'default'       => 'expedition@wildhorse.se',
    //              'description'   => 'Används för att få kontaktmeddelande',
    //          )
    //      )
    //  ),
    //  'campaign' => array(
    //      'menu_name' => 'Kampanj',
    //      'headline' => 'Inställningar för kampanj',
    //      'button_label' => 'Spara inställningar',
    //      'fields' => array(
    //          'sb_color_2' => array(
    //              'type'          => 'color',
    //              'label'         => 'Bakgrundsfärg, sidfot'
    //              ),
    //          'sb_image_3' => array(
    //              'type'          => 'image',
    //              'label'         => 'Bild',
    //              'description'   => 'En bild som syns nånstans',
    //              ),
    //          )
    //      )
    //  );

    public static function setMethod($args)
    {

        if (!empty($args['method']) && is_callable($args['method'])) {
            return $args;
        }

        // Simple callbacks
        $methods = array(
            'image' => true,
            'posts' => true,
            'url'   => true,
            'multi' => true,
            'password' => 'SB\Forms\Common::encryptPassword',
            );

        if (array_key_exists($args['type'], $methods)) {
            $args['method'] = $methods[$args['type']];
        } else {
            $args['method'] = false;
        }

        // Conditional args
        if ($args['type'] == 'date') {
            if (!empty($args['timestamp']) && $args['timestamp']) {
                $args['method'] = 'SB\Forms\Common::dateToTimestamp';
            }
        }

        return $args;

    }

    public static function register($name, $icon, $user_cap, $position, $data, $name_change = true)
    {

        return new Options($name, $icon, $user_cap, $position, $data, $name_change);

    }

    public function __construct($name, $icon, $user_cap, $position, $data, $name_change)
    {

        if (Utils::isAjaxRequest()) {
            return;
        }

        $this->name = $name;
        $this->icon = $icon;
        $this->user_cap = $user_cap;
        $this->position = $position;
        $this->data = $data;

        add_action('admin_menu', array($this, 'adminMenu'));
        add_action('admin_init', array($this, 'saveValues'));

        if ($name_change) {
            add_action('admin_menu', array($this, 'menuNameChange'));
        }

        if ('add_options_page' == $position
            && Utils::getString('page') == key($this->data)) {
            // Suppress whacky core settings_errors(), we have our own
            add_filter('admin_body_class', array(__CLASS__, 'adminBodyClasses'));
        }

        // INFO
        // Yes, it's being used by the getOption default function
        self::$instances[] = $data;

    }

    public static function adminBodyClasses()
    {

        return 'sb-options-regular-options-page';

    }

    public function saveValues()
    {

        if (empty($_POST)) {
            return;
        }

        if (Utils::isAjaxRequest()) {
            return;
        }

        $page = Utils::getString('page');
        if (empty($page)) { // mid save process
            $page = Utils::postString('option_page');
        }

        if (empty($page)) {
            return;
        }

        $data = self::getPageData($this, $page);
        if (empty($data)) {
            return;
        }

        $fields = $data['fields'];

        if (self::isTabbed($data)) {
            $active_tab = self::getActiveTab($data);

            foreach ($data['tabs'] as $name => $field_names) {
                if ($active_tab == sanitize_title($name)) {
                    $fields = self::getTabbedFields($field_names, $fields);
                }
            }

        }

        foreach ($fields as $name => $args) {
            if ($args['type'] == 'headline') {
                continue;
            }
            register_setting($page, $name);

            // set option methods
            $args = self::setMethod($args);

            // Use if you need something to happen after option has been saved
            // console('SB_save_option_'.$page.'_'.$name);

            if (is_bool($args['method'])) {
                $value = ($args['method']) ? Utils::postArray($name, false) : Utils::postString($name, false);

            } else {
                if (is_callable($args['method'])) {
                    // Before update_option is run, the $value is run through the method, returning a new value
                    // For example Field::password(), password in clear text comes from the POST, before its saved
                    // it's passed through the method $args['method] which in Field::password is 'SB\Forms\Common::encryptPassword'.
                    // see method Common::setMethod().
                    add_filter('pre_update_option_' . $name, $args['method']);
                    $value = call_user_func($args['method'], Utils::postVar($name), $name);

                } else {
                    Utils::debug('ERROR '.$args['method'].' is not callable, field not processed.', 0);
                    continue;
                }

            }

            do_action('SB_save_option_'.$page.'_'.$name, $value);

            Forms::$save_debug[] = array($name => $value);

        }

    }

    private static function getPageData($object, $page)
    {

        if (empty($page)) {
            return;
        }

        $object->data = apply_filters('SB_Options_data_construct_'.$page, $object->data);

        if (empty($object->data[$page])) {
            return;
        }

        return $object->data[$page];

    }

    private static function isTabbed($data)
    {

        if (empty($data['tabs'])) {
            return false;
        }

        return true;

    }

    private static function getActiveTab($data)
    {

        $active_tab = Utils::postString('active_tab');

        if (!$active_tab) {
            $active_tab = (Utils::getString('tab')) ? Utils::getString('tab') : sanitize_title(key($data['tabs']));
        }

        return sanitize_title($active_tab);

    }

    private static function getTabbedFields($field_names, $fields)
    {

        $tab_fields = array();
        foreach ($field_names as $name) {
            if (empty($fields[$name])) {
                continue;
            }

            $tab_fields[$name] = $fields[$name];

        }

        return $tab_fields;

    }

    public function menuNameChange()
    {

        global $submenu;

        reset($this->data);
        $menu_page = key($this->data);

        if (!empty($submenu[$menu_page][0][0])) {
            $submenu[$menu_page][0][0] = $this->data[$menu_page]['menu_name'];
        }

    }

    public function adminMenu()
    {

        reset($this->data);

        $menu_page = key($this->data);

        if (is_numeric($this->position)) {
            add_menu_page($this->name, $this->name, $this->user_cap, $menu_page, array($this, 'form'), $this->icon, $this->position);

            foreach ($this->data as $page => $data) {
                if ($page == $menu_page) {
                    continue;
                }
                add_submenu_page($menu_page, $data['menu_name'], $data['menu_name'], $this->user_cap, $page, array($this, 'form'));

            }

        } elseif ('add_options_page' == $this->position) {
            add_options_page($this->name, $this->name, $this->user_cap, $menu_page, array($this, 'form'));
        } else {
            foreach ($this->data as $page => $data) {
                add_submenu_page($this->position, $data['menu_name'], $data['menu_name'], $this->user_cap, $page, array($this, 'form'));
                break;
            }

            if (1 < count($this->data)) {
                Utils::debug('Notice:: As of now Wordpress doesnt support sub-submenu-pages, only the first one in your data was added.', 0);
            }

        }

    }

    public function form()
    {

        $page = Utils::getString('page');
        $data = self::getPageData($this, $page);
        $data = apply_filters('SB_Options_data_'.$page, $data);

        $fields = $data['fields'];

        $message = (empty($data['message'])) ? 'Inställningarna sparade.' : $data['message'];
        $message = '<div class="updated fade message"><p><strong>'.$message.'</strong></p></div>';
        $message = (Utils::getString('settings-updated')) ? $message : false;

        $tabs = array();
        $active_tab = false;

        $wrapper_classes = array('wrap', 'sb-options');

        if (self::isTabbed($data)) {
            $active_tab = self::getActiveTab($data);

            $tabs[] = '<h2 class="nav-tab-wrapper">';

            foreach ($data['tabs'] as $headline => $field_names) {
                $classes = array('nav-tab');
                $active = ($active_tab == sanitize_title($headline)) ? true : false;
                $classes[] = ($active) ? 'nav-tab-active' : false;
                $tabs[] = '<a class="'.implode(' ', $classes).'" href="?page='.$page.'&amp;tab='.sanitize_title($headline).'">'.$headline.'</a>';

                if ($active) {
                    $active_tab = $headline;
                    $wrapper_classes[] = 'option-tab-'.sanitize_title($headline);
                    $fields = self::getTabbedFields($field_names, $fields);
                }

            }

            $tabs[] = '</h2>';

        }

        ?>

        <div class="<?php echo implode(' ', $wrapper_classes); ?>">
            <h2><?php echo $data['headline']; ?></h2>
            <?php echo $message; ?>
            <?php echo implode("\n", $tabs); ?>
            <form action="options.php" method="post" id="poststuff" class="options">
                <?php settings_fields($page); ?>
                <?php wp_nonce_field($page, '_nonce_'.$page); ?>
                <input type="hidden" name="active_tab" value="<?php echo $active_tab ?>">
                <table class="form-table">
                    <?php self::options($fields); ?>
                </table>
                <p class="submit"><input class="button-primary" name="save" type="submit" value="<?php echo $data['button_label']; ?>" /></p>
            </form>
        </div>

        <?php

    }

    public static function options($data)
    {

        if (empty($data)) {
            return;
        }

        foreach ($data as $name => $data) {
            if ($data['type'] == 'headline') {
                $text = (!empty($data['text'])) ? '<h2>'.$data['text'].'</h2>' : false;
                $description = (!empty($data['description'])) ? '<p class="description">'.$data['description'].'</p>' : false;
                $row = '<tr valign="top"><td colspan="2" class="sb-headline">%s%s</td></tr>';

                echo sprintf($row, $text, $description);
                continue;

            }

            $data = apply_filters('SB_Options_field_'.$name, $data, $name);
            if (empty($data['label'])) {
                Common::error($data['type'], $data);
                continue;
            }

            $default = (!empty($data['default'])) ? $data['default'] : false;

            $args = $data;
            $args['name'] = $name;
            $args['value'] = get_option($name, $default);
            $args['class'] = str_replace('_', '-', $name);
            $args['wrapper'] = 'table';

            $method = $data['type'];

            switch ($method) {
                case 'image':
                    $args['value'] = get_option($name, -1);
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
}
