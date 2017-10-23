<?php

namespace SB\Utils;

class Firewall
{
    // This is not a real firewall, it just adds function to keep outside from pinging in.

    public static function init()
    {

        // Turn off XMLRPC if we don't need it
        add_filter('xmlrpc_enabled', '__return_false');

        // Disable X-Pingback HTTP Header.
        add_filter('wp_headers', function ($headers, $wp_query) {

            if (isset($headers['X-Pingback'])) {
                unset($headers['X-Pingback']);
            }
            return $headers;

        }, 11, 2);

        // Hijack pingback_url for get_bloginfo (<link rel="pingback" />).
        add_filter('bloginfo_url', function ($output, $property) {

            return ($property == 'pingback_url') ? null : $output;

        }, 11, 2);

    }
}
