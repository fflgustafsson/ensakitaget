<?php

namespace SB;

class Google
{

    protected static $version = 2;

    protected static $options = array(
        'cache_time' => 43200,
        'application_name' => 'Example Application Name',
        'service_account_name' => '150809009475-r3kuh96fvqhrbr13gk2fuhdo5oblairc@developer.gserviceaccount.com',
        'key_file_location' => 'key.p12',
        'view_id' => 'ga:58643352',
        'ga_account' => 'UA-xxxxxx-x',
        'tm_container_id' => 'GTM-5QJCJD',
        'data_layer' => array(
                'name' => 'dataLayer',
                'vars' => array(
                        'pageTitle' => 'page_title',
                        'uaId'  => 'ga_account'
                    )
            )
    );

    public static $client; // Authorized client
    public static $service; // Requsted service
    public static $cred; // Credentials

    public static $analytics;
    public static $youtube;

    private static $is_authorized = false;
    
    public static $dependencies = array(
        'Utils' => '2.0'
    );

    /**
    * Include libraries
    * @return none
    */
    public static function init()
    {

        require_once('vendor/autoload.php');
        require_once('lib/Analytics.php');
        require_once('lib/Youtube.php');

    }

    /**
     *
     * Register settings for class and lib classes
     *
     * @param type $settings array
     * @return none
     */
    public static function registerSettings($settings)
    {

        self::$options = array_replace_recursive(self::$options, $settings);

        Google\Analytics::registerSettings(self::$options);
        Google\Youtube::registerSettings(self::$options);

    }

    /**
     * Initialize services an autorize if needed
     * @param array $services array of strings
     * @return none
     */
    public static function initService($services)
    {
        foreach ($services as $service_name) {
            if ($service_name == 'analytics') {
                Google\Analytics::registerSettings(self::$options);

                if (!self::$is_authorized) {
                    self::authorize();
                }

                Google\Analytics::$client = self::$client;
                Google\Analytics::$cred = self::$cred;
                Google\Analytics::createService();

            } elseif ($service_name == 'youtube') {
                Google\Youtube::registerSettings(self::$options);

                if (!self::$is_authorized) {
                    self::authorize();
                }

                Google\Youtube::$client = self::$client;
                Google\Youtube::$cred = self::$cred;
                Google\Youtube::createService();

            }
        }
    }

    /**
     *
     * Authorize against Google
     *
     * @return none
     */
    protected static function authorize()
    {

        // Make sure all properties ar set
        if (!isset(self::$options['application_name'])) {
            throw new \Exception("No application name provided", 1);
        }

        if (!isset(self::$options['service_account_name'])) {
            throw new \Exception("No service account name provided", 1);
        }

        if (!isset(self::$options['key_file_location']) ||
            strlen(self::$options['key_file_location']) === 0 ||
            !file_exists(self::$options['key_file_location'])) {
            throw new \Exception("No key file or invalid key path provided: " . self::$options['key_file_location'], 1);

        }


        self::$client = new \Google_Client();

        //
        self::$client->setApplicationName(self::$options['application_name']);

        // This file location should point to the private key file.
        $key = file_get_contents(self::$options['key_file_location']);

        self::$cred = new \Google_Auth_AssertionCredentials(
            // Replace this with the email address from the client.
            self::$options['service_account_name'],
            // Replace this with the scopes you are requesting.
            array(\Google_Service_Analytics::ANALYTICS, \Google_Service_YouTube::YOUTUBE),
            $key
        );

        self::$client->setAssertionCredentials(self::$cred);

        if (isset(self::$options['sub'])) {
            self::$cred->sub = self::$options['sub'];
        }

        self::$is_authorized = true;

    }
}
