<?php

namespace SB\Google;

use SB\Utils;

class Youtube
{

    protected static $version = 2;

    protected static $options = array();

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
     * Create Youtube service
     * @return none
     */
    public static function createService()
    {
        self::$service = new \Google_Service_Youtube(self::$client);
    }

    /**
     * Fetch information about film
     * @param string $video_id
     * @param array $parts optional
     * @param string $return_type optional can be json, simple_object or object
     * @return boolean|Google_Service_YouTube_VideoListResponse
     */
    public static function listVideo($video_id, $parts = array(), $return_type = 'object')
    {
        if (empty($parts)) {
            array_push($parts, 'snippet');
            array_push($parts, 'contentDetails');
        }

        // Always want id
        if (!in_array('id', $parts)) {
            array_push($parts, 'id');
        }

        if (empty($video_id)) {
            throw new \Exception("Youtube::list_video Error: No video ID", 1);
        }

        if (!isset(self::$client)) {
            SB\Google::authorize();
        }

        if (!isset(self::$service)) {
            self::create_service();
        }

        try {
            $result = self::$service->videos->listVideos(implode(',', $parts), array('id' => $video_id));

        } catch (\Exception $e) {
            console($e);
            return false;
        }

        if ($return_type == 'object') {
            return $result;
        } elseif ($return_type == 'simple_object') {
            return $result->toSimpleObject();
        } elseif ($return_type == 'json') {
            return json_encode($result->toSimpleObject());
        }
    }

    /**
     * Save meta data from youtube to post
     * @param int $post_id
     * @param array $meta_field which fields that has youtube videoId stored
     * @param array $parts what to fetch from youtube. Default is id, snippet and contentDetails
     * @return none
     */
    public static function saveYoutubeMeta($post_id, $meta_fields = array('_youtube'), $parts = array())
    {

        // Look for inline videos in post_content
        $post = get_post($post_id);
        $content = $post->post_content;
        $inline_video_ids = self::video_in_content($content);

        $inline_fetch = false;

        if (!empty($inline_video_ids)) {
            $current_meta = get_post_meta($post_id, '_inline_youtube_meta', true);

            foreach ($current_meta->items as $current_item) {
                $item_id = $current_item->id;
                if (!in_array($item_id, $inline_video_ids)) {
                    $inline_fetch = true;
                    break;
                }
            }

            if ($inline_fetch) {
                $meta_data = self::list_video(implode(',', $inline_video_ids), $parts);

                if ($meta_data !== false) {
                    $items = $meta_data->getItems();

                    if (!empty($items)) { // Video is found. Store object in post_meta
                        $meta_data = $meta_data->toSimpleObject();
                        update_post_meta($post_id, '_inline_youtube_meta', $meta_data);
                    }
                }
            }

        }

        foreach ($meta_fields as $meta_field) {
            $video_ids = array();
            $video_string = get_post_meta($post_id, $meta_field, true);

            $video_ids = array_merge($video_ids, self::video_in_content($video_string));

            if (!empty($video_ids)) {
                $current_meta_data = get_post_meta($post_id, '_youtube_meta', true);
                $current_video_id = !empty($current_meta_data->items) ? $current_meta_data->items[0]->id : '';

                if (empty($current_meta_data) || !in_array($current_video_id, $video_ids)) {
                    // if new movie ALWAYS save meta data

                    $meta_data = self::list_video(implode(',', $video_ids), $parts);

                    if ($meta_data !== false) {
                        $items = $meta_data->getItems();

                        if (!empty($items)) { // Video is found. Store object in post_meta
                            $meta_data = $meta_data->toSimpleObject();
                            update_post_meta($post_id, '_youtube_meta', $meta_data);

                        }
                    }

                } else {
                    $custom_meta = Utils::post_array('_custom_youtube');
                    if ($custom_meta) {
                        $update = false;

                        if ($custom_meta['title'] != $current_meta_data->items[0]->snippet['title']) {
                            $current_meta_data->items[0]->snippet['title'] = $custom_meta['title'];
                            $update = true;
                        }

                        if ($custom_meta['description'] != $current_meta_data->items[0]->snippet['description']) {
                            $current_meta_data->items[0]->snippet['description'] = $custom_meta['description'];
                            $update = true;
                        }

                        if ($update) {
                            update_post_meta($post_id, '_youtube_meta', $current_meta_data);
                        }

                    }

                }

            } else {
                // no video, clear meta data
                update_post_meta($post_id, '_youtube_meta', false);

            }

        }

    }

    public static function videoInContent($content)
    {

        $video_ids = array();
        $matches = array();

        if (0 < preg_match_all('/watch\?v=([^&\s]+)/', $content, $matches)) {
            foreach ($matches[1] as $match) {
                $video_ids[] = $match;
            }

        }

        if (0 < preg_match_all('/embed\/([^&\s]+)/', $content, $matches)) {
            foreach ($matches[1] as $match) {
                $video_ids[] = $match;
            }

        }

        if (0 < preg_match_all('/youtu\.be\/([^&\s]+)/', $content, $matches)) {
            foreach ($matches[1] as $match) {
                $video_ids[] = $match;
            }

        }

        if (0 < preg_match_all('/youtube\.com\/v\/([^&\s]+)/', $content, $matches)) {
            foreach ($matches[1] as $match) {
                $video_ids[] = $match;
            }

        }

        return $video_ids;
    }
}
