<?php

// Add end points for API
use SB\JSON;

API::addEndpoint(array(
    'uri'       => '/score/',
    'method'    => 'POST',
    'headers'   => array(
        'WPAPI-UDID' => array('require', 'int'),
        'WPAPI-Checksum' => array('require', 'str')
        ),
    'type'      => 'json',
    'data'      => array(
        'score' => array('require', 'int'),
        'beacon' => array('str')
        ),
    'callback' => array('Score', 'postScore')
    ));

JSON::addEndpoint(array(
    'public'    => false,
    'uri'       => '/posts/all/',
    'method'    => 'GET',
    'post_type' => 'post',
    'query'     => array(
        'post_type' => 'posts',
        'posts_per_page' => -1,
        'orderby' => 'post_date',
        'order' => 'DESC'
        )
    ));

JSON::addEndpoint(array(
    'uri'       => 'regions/',
    'method'    => 'GET',
    'post_type' => 'post',
    'query'     => array(
        'post_type' => 'posts',
        'posts_per_page' => -1,
        'orderby' => 'post_date',
        'order' => 'DESC'
        )
    ));

JSON::addEndpoint(array(
    'uri'       => '/message/post',
    'method'    => 'GET',
    'post_type' => 'post',
    'query'     => array(
        'post_type' => 'posts',
        'posts_per_page' => -1,
        'orderby' => 'post_date',
        'order' => 'DESC'
        )
    ));

JSON::addEndpoint(array(
    'uri'       => '/special/',
    'method'    => 'GET',
    'post_type' => 'post',
    'query'     => array(
        'post_type' => 'posts',
        'posts_per_page' => -1,
        'orderby' => 'post_date',
        'order' => 'DESC'
        )
    ));
