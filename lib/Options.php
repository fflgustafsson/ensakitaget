<?php

use SB\Forms\Options;

$data = array(

    'common_settings' => array(

        'menu_name' => 'Allmänt',
        'headline' => 'Allmänna inställningar',
        'button_label' => 'Spara inställningar',
        'tabs' => array(
            'Stock / Delivery' => array('aifi_stock_select', 'aifi_stock_content')
            ),
        'fields' => array(

            'aifi_stock_select' => array(
                'type'          => 'checkbox',
                'label'         => 'Show delivery information'
                ),
            
            'aifi_stock_content' => array(
                'type'          => 'editor',
                'label'         => 'Content'
                ),
            

        )

    )
);

Options::register('Tema', 'dashicons-admin-media', 'edit_others_posts', 27, $data);

// Add a menu separator above theme
SB\Utils\Wordpress::addAdminMenuSeparator(26);

// Positions
// 2 - Dashboard
// 4 - Separator
// 5 - Posts
// 10 - Media
// 15 - Links
// 20 - Pages
// 25 - Comments
// 59 - Separator
// 60 - Appearance
// 65 - Plugins
// 70 - Users
// 75 - Tools
// 80 - Settings
// 99 - Separator

// Example
