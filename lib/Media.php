<?php

use SB\Media;
use SB\Media\Responsive;

// How to register image sizes

// Media::register_image_sizes(
//  // name => array(width, height, crop)
//  array(
//      'release-image' => array(398, 200),
//      'test-image'    => array(300, 300, 'soft'), // soft proportional crop
//      )
//  );

// How to add image to posttype listings

Media::registerFeaturedImageColumn(array(
    // post_type => post_meta
    'post'  => '_thumbnail_id',
    'page'  => '_thumbnail_id',
    'custom' => '_image'
    ));

// How to register responsive images

// Responsive::register_sizes(
//  array(
//      'monkey'        => array(
//          'size'          => array(1024, 768),
//          'breakpoints'   => array('large' => 1024, 'medium' => 768, 'small' => 540),
//          'crop'          => true,
//          )
//      )
//  );

Responsive::registerSizes(
    array(
        'stack-slider' => array(
            'size'          => array(1920, 800),
            'breakpoints'   => array('large' => 1920, 'medium' => 1024, 'small' => 600),
            'crop'          => false,
        ),
        'header-image' => array(
            'size'          => array(1920, 392),
            'breakpoints'   => array('large' => 1920, 'medium' => 1024, 'small' => 600),
            'crop'          => false,
        ),
        'module-image-half' => array(
            'size'          => array(960, 960),
            'breakpoints'   => array('large' => 960, 'medium' => 520, 'small' => 200),
            'crop'          => false,
        ),
        'module-image-third' => array(
            'size'          => array(800, 452),
            'breakpoints'   => array('large' => 800, 'medium' => 600, 'small' => 240),
            'crop'          => false,
        )
    )
);
