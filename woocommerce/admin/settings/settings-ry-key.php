<?php

return [
    [
        'title' => 'RY SmilePay Invoice for WooCommerce',
        'id' => 'wsi_options',
        'type' => 'title',
    ],
    [
        'title' => __('License key', 'ry-woocommerce-smilepay-invoice'),
        'id' => RY_WSI::OPTION_PREFIX . 'license_key',
        'type' => 'text',
        'default' => '',
    ],
    [
        'id' => 'ry_wsi_version_info',
        'type' => 'ry_wsi_version_info',
    ],
    [
        'id' => 'wsi_options',
        'type' => 'sectionend',
    ],
];
