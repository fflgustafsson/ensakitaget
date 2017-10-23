<?php

namespace SB;

use Sly\NotificationPusher\PushManager,
    Sly\NotificationPusher\Adapter\Apns as ApnsAdapter,
    Sly\NotificationPusher\Adapter\Gcm as GcmAdapter,
    Sly\NotificationPusher\Collection\DeviceCollection,
    Sly\NotificationPusher\Model\Device,
    Sly\NotificationPusher\Model\Message,
    Sly\NotificationPusher\Model\Push,
    SB\Utils;

class Notifications
{

    public static $dependencies = array(
    'Utils' => '2.0'
    );

    public static $apns_certificate = false;
    public static $gcm_api_key = false;
    public static $environment = 'dev';
    public static $default_sound = false;

    public static $post_meta_key = array(
        'apns' => 'apns',
        'gcm' => 'gcm'
        );

    protected static $adapters = array();
    protected static $manager = false;

    public static function init()
    {

        // check if this is needed
        if (Utils::is_ajax_request()) {
            if ('create_black_out' != Utils::post_string('action')) {
                return;
            }
        }

        require_once('lib/vendor/autoload.php');

        $manager_env = ('prod' == self::$environment) ? PushManager::ENVIRONMENT_PROD : PushManager::ENVIRONMENT_DEV;

        self::$manager = new PushManager($manager_env);

        if (self::$apns_certificate) {
            self::$adapters['apns'] = new ApnsAdapter(array(
                'certificate' => self::$apns_certificate
            ));
        }

        if (self::$gcm_api_key) {
            self::$adapters['gcm'] = new GcmAdapter(array(
                'apiKey' => self::$gcm_api_key
            ));
        }

    }

    private static function tokens($system)
    {

        $tokens = apply_filters('SB_Notifications_Tokens', array(), $system);

        return self::devices($tokens);

    }

    private static function devices($tokens = array())
    {

        $devices = array();

        foreach ($tokens as $token) {
            $devices[] = new Device($token);
        }

        return $devices;

    }

    public static function push($message, $sound = false, $tokens = false)
    {

        $args = array();
        $sound = (!$sound) ? self::$default_sound : $sound;

        if ($sound) {
            $args['sound'] = $sound;
        }

        $message = new Message(trim(strip_tags($message)), $args);

        foreach (self::$adapters as $system => $adapter) {
            $devices = array();

            if (!$tokens) {
                $devices = self::tokens(self::$post_meta_key[$system]);
            } else {
                if (!empty($tokens[self::$post_meta_key[$system]])) {
                    $devices = self::devices($tokens[self::$post_meta_key[$system]]);
                }
            }

            if (!empty($devices)) {
                $push = new Push($adapter, $devices, $message);
                self::$manager->add($push);
                self::$manager->push();
            }

            // debug('Feedback:');
            // debug(self::$manager->getFeedback($adapter));

        }

    }
}
