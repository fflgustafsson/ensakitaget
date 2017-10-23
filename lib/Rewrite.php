<?php

use SB\Rewrite;

// Template special
// This how you add a specifik "fake-page" with a template
// template file name is template + .php, i.e template-special.php
// // Christoffer

Rewrite::addRule(array(
    'uri' => 'special/?',
    'template' => 'template-special',
    'after' => 'top',
    'page'  => array(
        'title'     => 'Min specialsida',
        'body_class'    => 'el-speciale'
        )
));

// Rewrite::addRewriteRule(array(
//  'uri' => 'json-api/?',
//  'template' => 'lib/JSON.php',
//  'page'  => array(
//      'title'     => 'JSON Api',
//      'body_class'    => 'json-api'
//      )
// ));

// Get things rolling
add_action('init', array('SB\Rewrite', 'init'), 10);
