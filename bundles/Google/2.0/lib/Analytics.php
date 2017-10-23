<?php

namespace SB\Google;

use SB\Utils;

/**
 *
 * @package SB
 *
 */

class Analytics
{

    protected static $version = 2;

    protected static $options = array();

    protected static $is_tag_manager_used = false;
    protected static $is_google_analytics_used = false;

    public static $client; // Authorized client
    public static $service; // Requsted service
    public static $cred; // Credentials
    
    public static $dependencies = array(
        'Utils' => '2.0'
    );


    /**
     *
     * Register settings for class
     *
     * @param type $settings array
     * @return none
     */
    public static function registerSettings($settings)
    {
        self::$options = array_replace_recursive(self::$options, $settings);
    }

    /**
     * Call this to implement Google Tag Manager. If Google Analytics already used, method returns. Can't have both
     *
     * @return none
     */
    public static function tagManager()
    {

        if (self::$is_google_analytics_used) {
            return;
        }

        self::tagManagerData();
        self::renderTagManager();

        // add_action('wp_footer', array(__CLASS__, 'render_tag_manager'), 30);
        // add_action('wp_footer', array(__CLASS__, 'tag_manager_data'), 10);

        self::$is_tag_manager_used = true;

    }

    /**
     *
     * Renders the actual code, in footer, necessary for Google Tag Manager
     * Dependant on tm_container_id and data_layer->name from settings
     *
     * @return none
     */
    public static function renderTagManager()
    {
?>

      <!-- Google Tag Manager -->
      <noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-N46GQG"
      height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
      <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
      new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
      j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
      '//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
      })(window,document,'script','<?php echo self::$options['data_layer']['name']; ?>','<?php echo self::$options['tm_container_id']; ?>');</script>
      <!-- End Google Tag Manager -->
<?php

    }

    /**
     * Outputs variables defined in settings to a javascript array used by tag manager
     *
     * @return none
     */
    public static function tagManagerData()
    {

        $local_post = get_post();

        $vars           = self::$options['data_layer']['vars'];
        $dataLayerName  = self::$options['data_layer']['name'];

        echo '<script type="text/javascript">
                var ' . $dataLayerName . ' = ' . $dataLayerName . ' || [];' . "\n";

        $dataStr = '';
        foreach ($vars as $name => $type) {
            switch ($type) {
                case 'page_title':
                    $val = (!empty($local_post)) ? get_the_title($local_post->ID) : get_bloginfo('name');
                    $dataStr .= $dataLayerName . '.push({\'' . $name . '\':\'' . $val . '\'});' . "\n";
                    break;
                case 'ga_account':
                    $val = self::$options['ga_account'];
                    $dataStr .= $dataLayerName . '.push({\'' . $name . '\':\'' . $val . '\'});' . "\n";
                    break;
            }
        }

        echo $dataStr;

        echo '</script>';
    }

    /**
     * Call this to implement Google Analytics. If Tag Manager already used, method returns. Can't have both
     *
     * @return none
     */
    public static function googleAnalytics()
    {

        if (self::$is_tag_manager_used) {
            return;
        }

        add_action('wp_footer', array(__CLASS__, 'renderGoogleAnalytics'), 30);

        self::$is_google_analytics_used = true;


    }
    /**
     *
     * Renders the actual code, in footer, necessary for Google Analytics
     * Dependant on ga_account from settings
     *
     * @return none
     */
    public static function renderGoogleAnalytics()
    {

?>
        
        <script>
          (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
          (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
          m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
          })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

          ga('create', '<?php echo self::$options['ga_account']; ?>', 'auto');
          ga('send', 'pageview');

        </script>
<?php

    }

    public static function socialInteractions()
    {
        add_action('wp_enqueue_scripts', array(__CLASS__, 'includeSocialEventlisteners'), 30);
    }

    /**
     * Description
     * @return type
     */
    public static function includeSocialEventlisteners()
    {


        wp_register_script(
            'sb-social-interactions',
            Utils::get_bundle_uri('Google') . '/js/social.interactions-template.js',
            array(),
            self::$version,
            true
        );

        wp_localize_script(
            'sb-social-interactions',
            'sbSocial',
            array(
                'isGaInUse' => self::$is_google_analytics_used ? 'true' : 'false',
                'isTagManagerInUse' => self::$is_tag_manager_used ? 'true' : 'false'
            )
        );

        wp_enqueue_script('sb-social-interactions');

    }

    /**
     *
     * Fetch top pages from Google Analytics. Fetches latest 90 days as default
     * Options:
     * output = links|object optional default links
     * post_types = optional default null string or array of post types
     * used in conjunction with output = object to filter output
     * days = int optional default 90 timespan of data in days from today
     * count = int optional default 3 number of links to get.
     * Note if filtering is set use a high count to sure you fetch all of a certain type
     * force_google = boolean optional default false force flush of cache
     * @param array $options array of settings
     * @return array permalinks|WPObjects
     */
    public static function getTopVisited($options = array())
    {

        $params = array(
            'output' => 'links',
            'post_types' => null,
            'days' => 90,
            'count' => 3,
            'force_google' => false,
            'filters' => null
        );
        $params = array_merge($params, $options);

        extract($params);

        // Get cached version based on days and count
        $transient_name = 'visited-' . $days . '-' . $count * 50;
        $links = get_transient($transient_name);

        if ($links === false || $force_google) {
            $result = self::makeGoogleRequest($days, $count * 50, 'ga:pageviews', 'ga:pagePath', null, $filters);

            // debug($result);

            $links = array();

            if ($result !== false) {
                $rows = $result->getRows();

                if (!empty($rows)) {
                    foreach ($result->getRows() as $row) {
                        $permalink = $row[1];
                        $hits = $row[2];

                        // Store unique path, adding hits together
                        if (array_key_exists($permalink, $links)) {
                            $hits += intval($links[$permalink]);
                            $links[$permalink] = $hits;
                        } else {
                            $links[$permalink] = $hits;
                        }

                    }

                }

                // debug($links);

                // Store results
                if (!empty($links)) {
                    set_transient($transient_name, $links, self::$options['cache_time']);
                }

            }

        }

        if ($output == 'object') {
            return self::getTopUniqueWpobj($links, $post_types, $count);

        } else {
            if (count($links) > $count) {
                return array_slice($links, 0, $count);

            } else {
                return $links;

            }

        }

    }

    /**
     *
     * Fetch top shared/liked/+1/tweeted pages from Google Analytics. Fetches latest 30 days as default
     * Options:
     * output = links|object optional default links
     * post_types = optional default null string or array of post types used in
     * conjunction with output = object to filter output
     * days = int optional default 30 timespan of data in days from today
     * count = int optional default 3 number of links to get.
     * Note if filtering is set use a high count to sure you fetch all of a certain type
     * force_google = boolean optional default false force flush of cache
     *
     * @param array $options array of settings
     * @return array permalinks|WPObjects
     */
    public static function getTopSocialInteractions($options = array())
    {

        $params = array(
            'output' => 'links',
            'post_types' => null,
            'days' => 90,
            'count' => 3,
            'force_google' => false,
            'filters' => null
        );
        $params = array_merge($params, $options);

        extract($params);

        // Get cached version based on days and count
        $transient_name = 'social-' . $days . '-' . $count * 50;
        $links = get_transient($transient_name);

        if ($links === false || $force_google) {
            $result = self::makeGoogleRequest(
                $days,
                $count * 50,
                'ga:socialInteractions',
                array(
                    'ga:socialInteractionTarget',
                    'ga:socialInteractionAction'),
                null,
                $filters
            );

            Utils::debug($result);

            $interactions = array();
            $links = array();

            if ($result !== false) {
                $rows = $result->getRows();

                if (!empty($rows)) {
                    foreach ($rows as $row) {
                        $permalink = preg_replace('/http(s)?:\/\/[^\/]+/', '', $row[1]);
                        $shares = $row[3];

                        // Store unique path, adding hits together

                        if ($row[2] !== 'unlike') {
                            if (array_key_exists($permalink, $links)) {
                                $shares += intval($links[$permalink]);
                                $links[$permalink] = $shares;
                            } else {
                                $links[$permalink] = $shares;
                            }

                        }

                    }

                    if (!empty($links)) {
                        set_transient($transient_name, $links, self::$options['cache_time']);
                    }

                }

            }

        }

        if ($output == 'object') {
            return self::getTopUniqueWpobj($links, $post_types, $count);

        } else {
            if (count($links) > $count) {
                return array_slice($links, 0, $count);

            } else {
                return $links;

            }

        }

    }

    /**
     * Private method for doing Google requests
     * @param int $days timespan of data in days from today
     * @param int $count number of data to get
     * @param string $metrics Google Analytics Metrics (ga:xxx)
     * @param string|array $dimensions Google Analytics Dimansions (ga:xxx)
     * @param string $sort optional how to sort data. Defaults to Metrics desc
     * @return array|boolean false if request fails
     */
    protected static function makeGoogleRequest($days, $count, $metrics, $dimensions, $sort = null, $filters = null)
    {

        $end_date = date('Y-m-d');
        $start_date = date('Y-m-d', time() - ($days * 24 * 60 * 60 ));

        if (is_null($sort)) {
            $sort = '-' . $metrics;
        }

        if (is_array($dimensions)) {
            $dimensions = implode(',', $dimensions);
        }

        // If request fails don't break site
        try {
            $result = self::$service->data_ga->get(
                self::$options['view_id'],
                $start_date,
                $end_date,
                $metrics,
                array(
                    'dimensions' => 'ga:hostname, '.$dimensions,
                    'sort' => $sort,
                    'max-results' => $count,
                    'filters' => $filters
                )
            );

        } catch (\Exception $e) {
            debug($e);
            return false;
        }

        return $result;
    }

    /**
     * Return top WP objects in links-array from google request.
     *
     * @param array $links
     * @param array|string $post_types
     * @param string $count count
     * @return WP object || false
     */
    protected static function getTopUniqueWpobj($links, $post_types, $count)
    {

        if (empty($links)) {
            return array();
        }

        // If no filtering get all
        if (empty($post_types)) {
            $post_types = get_post_types();
        }

        if (!is_array($post_types)) {
            $post_types = explode(', ', $post_types);
        }

        $cache = array();
        $objects = array();

        foreach ($links as $path => $hits) {
            $parts = array_filter(explode('/', $path));
            if (1 >= count($parts)) {
                continue;
            }

            foreach ($post_types as $post_type) {
                $object = self::convertPathToWpobj($path, $post_type);
            }

            if ($object) {
                if ($object->post_status != 'publish') {
                    continue;
                }

                $cache[$object->ID] = $object;

                if (array_key_exists($object->ID, $objects)) {
                    $objects[$object->ID] = $hits + $objects[$object->ID];

                } else {
                    $objects[$object->ID] = $hits;

                }

            }

        }

        if (empty($objects)) {
            return array();
        }

        $return = array();

        // sort after hits
        asort($objects, SORT_NUMERIC);
        $objects = array_reverse($objects, true);

        // limit according to count
        foreach ($objects as $id => $hits) {
            array_push($return, $cache[$id]);
            if (count($return) == $count) {
                break;
            }

        }

        return $return;

    }


    /**
     * Converts path to WP object if possible
     *
     * @param array $links
     * @param array|string $post_types
     * @return WP object || false
     */
    protected static function convertPathToWpobj($path, $post_type)
    {

        if ($post_type != 'page' && !is_post_type_hierarchical($post_type)) {
            $item = get_page_by_path(basename(untrailingslashit($path)), OBJECT, $post_type);

        } else {
            $item = get_page_by_path($path);

        }

        if (is_object($item)) {
            return $item;
        }

        return false;

    }

    public static function createService()
    {
        self::$service = new \Google_Service_Analytics(self::$client);
    }
}
