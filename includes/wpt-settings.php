<?php

global $wptsf_settings;
$plugin_l10n = 'wp-trello';


// General Settings section
$wptsf_settings[] = array(
    'section_id' => 'general',
    'section_title' => 'General Settings',
    'section_order' => 1,
    'fields' => array(
        array(
            'id' => 'target-blank',
            'title' => __( 'Open Links in New Page', $plugin_l10n ),
            'desc' => __( '', $plugin_l10n ),
            'type' => 'checkbox',
            'std' => false
        ),
        array(
            'id' => 'output-css',
            'title' => __( 'Custom CSS', $plugin_l10n ),
            'desc' => __( 'Customise the output of the plugin with your own CSS.<br/><br/>You can use the following classes (replace [type] with board or card etc).<br/>.wpt-[type]-wrapper, ul.wpt-[type]-wrapper<br/>.wpt-[type], li.wpt-[type]<br/>.wpt-[type]-link', $plugin_l10n ),
            'type' => 'textarea',
            'std' => ''
        ),
        array(
            'id' => 'link-love',
            'title' => __( 'Link Love', $plugin_l10n ),
            'desc' => __( 'Adds a link to the plugin page after the plugin output.', $plugin_l10n ),
            'type' => 'checkbox',
            'std' => false
        ),

    )
);
// API Helper section
$wptsf_settings[] = array(
    'section_id' => 'helper',
    'section_title' => 'API Helper',
    'section_order' => 2,
    'fields' => array(
        array(
            'id' => 'orgs',
            'title' => __( 'Organizations', $plugin_l10n ),
            'desc' => __( '', $plugin_l10n ),
            'type' => 'organizations',
            'choices' => array(),
            'std' => ''
        ),
        array(
            'id' => 'boards',
            'title' => __( 'Boards', $plugin_l10n ),
            'desc' => __( '', $plugin_l10n ),
            'type' => 'boards',
            'choices' => array(),
            'std' => ''
        ),
        array(
            'id' => 'lists',
            'title' => __( 'Lists', $plugin_l10n ),
            'desc' => __( '', $plugin_l10n ),
            'type' => 'lists',
            'choices' => array(),
            'std' => ''
        ),
        array(
            'id' => 'cards',
            'title' => __( 'Cards', $plugin_l10n ),
            'desc' => __( '', $plugin_l10n ),
            'type' => 'cards',
            'choices' => array(),
            'std' => ''
        ),
    )
);
?>