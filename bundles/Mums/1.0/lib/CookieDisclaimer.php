<?php

namespace SB\Mums;

use SB\Utils;
use SB\Forms\Options;
use SB\Forms\Common;

class CookieDisclaimer
{

    public static $dependencies = array(
        'Utils' => '2.0',
        'Forms' => '2.0'
        );

    public static $register = array();

    public static $options = array(

        'cookie_disclaimer' => array(

            'menu_name' => 'Cookies',
            'headline' => 'Information om cookies',
            'button_label' => 'Spara',
            'fields' => array(

                'cookie_disclaimer_text' => array(
                    'type'          => 'textarea',
                    'rows'          => 3,
                    'label'         => 'Text',
                    'default'       => 'Vi har placerat cookies på din dator och lagrat ditt IP-nummer för att ge dig en bättre upplevelse av våra webbplatser.'
                    ),

                'cookie_disclaimer_button' => array(
                    'type'          => 'text',
                    'label'         => 'Knapp',
                    'default'       => 'Jag förstår'
                    ),
            )

        )

    );

    public static function register($callback = false, $javascript = true, $add_page = true)
    {

        $callback = (!empty($callback) && is_callable($callback)) ? $callback : false;

        self::$register = array(
            'callback' => $callback,
            'javascript' => $javascript,
            'active' => true,
            'add_page' => $add_page
            );

        add_action('wp_ajax_set_disclaimer_cookie', array(__CLASS__, 'setCookie'));
        add_action('wp_ajax_nopriv_set_disclaimer_cookie', array(__CLASS__, 'setCookie'));

        if (self::$register['add_page']) {
            Options::register(
                'Cookies',
                'dashicons-admin-media',
                'edit_others_posts',
                'add_options_page',
                self::$options
            );
        }

    }

    public static function setCookie()
    {

        $ttl = apply_filters('SB_cookie_disclaimer_ttl', 31556926); // 31556926 == 1 year

        $cookie_path = apply_filters('SB_cookie_disclaimer_path', COOKIEPATH);
        $cookie_domain = apply_filters('SB_cookie_disclaimer_domain', COOKIE_DOMAIN);

        debug($cookie_path);
        debug($cookie_domain);

        $cookie = setcookie('wp-cookie-info', 'seen', time() + $ttl, $cookie_path, $cookie_domain);
        $status = ($cookie) ? 'set' : 'error';
        Utils::returnJSON(array('status' => $status));

    }

    public static function javascript()
    {

        $admin_url = admin_url('admin-ajax.php');

        ?>

<script id="cookie-disclaimer-js" type="text/javascript">
/*globals
    jQuery
*/
(function ($) {
    "use strict";
    $('#cookie-disclaimer-button').on('click', function () {
        $.post('<?php echo $admin_url; ?>', { 'action' : 'set_disclaimer_cookie' }, function (data) {
            if (data.status === 'set') {
                $('#cookie-disclaimer').fadeOut('fast', function () {
                    $(this).trigger('removed').remove();
                });
            }
        });
    });
}(jQuery));

</script>

        <?php

    }

    public static function render()
    {

        if (empty(self::$register['active'])) {
            return;
        }

        if (self::$register['javascript']) {
            add_action('wp_footer', array(__CLASS__, 'javascript'), 999);
        }

        if (Utils::getCookie('wp-cookie-info') == 'seen') {
            return;
        }

        if (self::$register['callback']) {
            call_user_func(self::$register['callback']);
            return;
        }

        $image = Utils::getBundleUri('Mums').'/images/info.svg';
        $text = Common::getOption('cookie_disclaimer_text');
        $button = Common::getOption('cookie_disclaimer_button');

        ?>

        <div id="cookie-disclaimer" class="cookie-disclaimer">
            <div>
                <object type="image/svg+xml" data="<?php echo $image; ?>">
                    <param name="src" value="<?php echo $image; ?>">
                </object>
                <p><?php echo $text; ?></p>
                <a id="cookie-disclaimer-button" class="cookie-disclaimer-button" href="javascript:void(0)">
                    <?php echo $button; ?>
                </a>
            </div>
        </div>
        <?php

    }
}