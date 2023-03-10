<?php

return [
    [
        'title' => 'RY WooCommerce SmilePay Invoice',
        'id' => 'wsi_options',
        'type' => 'title'
    ],
    [
        'title' => __('License key', 'ry-woocommerce-smilepay-invoice'),
        'id' => RY_WSI::$option_prefix . 'license_key',
        'type' => 'text',
        'default' => ''
    ],
    [
        'type' => 'rywsi_version_info',
    ],
    [
        'id' => 'wsi_options',
        'type' => 'sectionend'
    ]
];
