<?php

$order_statuses = wc_get_order_statuses();
$paid_status = [];
foreach (wc_get_is_paid_statuses() as $status) {
    $paid_status[] = $order_statuses['wc-' . $status];
}
$paid_status = implode(', ', $paid_status);

return [
    [
        'title' => __('Base options', 'ry-woocommerce-smilepay-invoice'),
        'id' => 'base_options',
        'type' => 'title',
    ],
    [
        'title' => __('Debug log', 'ry-woocommerce-smilepay-invoice'),
        'id' => RY_WSI::OPTION_PREFIX . 'smilepay_invoice_log',
        'type' => 'checkbox',
        'default' => 'no',
        'desc' => __('Enable logging', 'ry-woocommerce-smilepay-invoice') . '<br>'
            . sprintf(
                /* translators: %s: Path of log file */
                __('Log API / IPN information, inside %s', 'ry-woocommerce-smilepay-invoice'),
                '<code>' . WC_Log_Handler_File::get_log_file_path('ry_smilepay_invoice') . '</code>',
            )
            . '<p class="description" style="margin-bottom:2px">' . __('Note: this may log personal information.', 'ry-woocommerce-smilepay-invoice') . '</p>',
    ],
    [
        'title' => __('Order no prefix', 'ry-woocommerce-smilepay-invoice'),
        'id' => RY_WSI::OPTION_PREFIX . 'order_prefix',
        'type' => 'text',
        'desc' => __('The prefix string of order no. Only letters and numbers allowed.', 'ry-woocommerce-smilepay-invoice'),
        'desc_tip' => true,
    ],
    [
        'title' => __('Show invoice number', 'ry-woocommerce-smilepay-invoice'),
        'id' => RY_WSI::OPTION_PREFIX . 'show_invoice_number',
        'type' => 'checkbox',
        'default' => 'no',
        'desc' => __('Show invoice number in Frontend order list', 'ry-woocommerce-smilepay-invoice'),
    ],
    [
        'title' => __('Move billing company', 'ry-woocommerce-smilepay-invoice'),
        'id' => RY_WSI::OPTION_PREFIX . 'move_billing_company',
        'type' => 'checkbox',
        'default' => 'no',
        'desc' => __('Move billing company to invoice area', 'ry-woocommerce-smilepay-invoice'),
    ],
    [
        'id' => 'base_options',
        'type' => 'sectionend',
    ],
    [
        'title' => __('Invoice options', 'ry-woocommerce-smilepay-invoice'),
        'id' => 'invoice_options',
        'type' => 'title',
    ],
    [
        'title' => __('support paper type (B2C)', 'ry-woocommerce-smilepay-invoice'),
        'id' => RY_WSI::OPTION_PREFIX . 'support_carruer_type_none',
        'type' => 'checkbox',
        'default' => 'no',
        'desc' => __('You need print invoice and seed to orderer.', 'ry-woocommerce-smilepay-invoice'),
    ],
    [
        'title' => __('user SKU as product name', 'ry-woocommerce-smilepay-invoice'),
        'id' => RY_WSI::OPTION_PREFIX . 'use_sku_as_name',
        'type' => 'checkbox',
        'default' => 'no',
        'desc' => __('If product no SKU, back to use product name', 'ry-woocommerce-smilepay-invoice'),
    ],
    [
        'title' => __('Get mode', 'ry-woocommerce-smilepay-invoice'),
        'id' => RY_WSI::OPTION_PREFIX . 'get_mode',
        'type' => 'select',
        'default' => 'manual',
        'options' => [
            'manual' => _x('manual', 'get mode', 'ry-woocommerce-smilepay-invoice'),
            'auto_paid' => _x('auto ( when order paid )', 'get mode', 'ry-woocommerce-smilepay-invoice'),
            'auto_completed' => _x('auto ( when order completed )', 'get mode', 'ry-woocommerce-smilepay-invoice'),
        ],
        'desc' => sprintf(
            /* translators: %s: paid status */
            __('Order paid status: %s', 'ry-woocommerce-smilepay-invoice'),
            $paid_status,
        ),
    ],
    [
        'title' => __('Skip foreign orders', 'ry-woocommerce-smilepay-invoice'),
        'id' => RY_WSI::OPTION_PREFIX . 'skip_foreign_order',
        'type' => 'checkbox',
        'default' => 'no',
        'desc' => __('Disable auto get invoice for order billing country and shipping country are not in Taiwan.', 'ry-woocommerce-smilepay-invoice'),
    ],
    [
        'title' => __('Invalid mode', 'ry-woocommerce-smilepay-invoice'),
        'id' => RY_WSI::OPTION_PREFIX . 'invalid_mode',
        'type' => 'select',
        'default' => 'manual',
        'options' => [
            'manual' => _x('manual', 'invalid mode', 'ry-woocommerce-smilepay-invoice'),
            'auto_cancell' => _x('auto ( when order status cancelled OR refunded )', 'invalid mode', 'ry-woocommerce-smilepay-invoice'),
        ],
    ],
    [
        'title' => __('Amount abnormal mode', 'ry-woocommerce-smilepay-invoice'),
        'id' => RY_WSI::OPTION_PREFIX . 'amount_abnormal_mode',
        'type' => 'select',
        'default' => '',
        'options' => [
            '' => _x('No action', 'amount abnormal mode', 'ry-woocommerce-smilepay-invoice'),
            'product' => _x('add one product to match order amount', 'amount abnormal mode', 'ry-woocommerce-smilepay-invoice'),
            'order' => _x('change order total amount', 'amount abnormal mode', 'ry-woocommerce-smilepay-invoice'),
        ],
    ],
    [
        'title' => __('fix amount product name', 'ry-woocommerce-smilepay-invoice'),
        'id' => RY_WSI::OPTION_PREFIX . 'amount_abnormal_product',
        'type' => 'text',
        'default' => __('Discount', 'ry-woocommerce-smilepay-invoice'),
    ],
    [
        'id' => 'invoice_options',
        'type' => 'sectionend',
    ],
    [
        'title' => __('API credentials', 'ry-woocommerce-smilepay-invoice'),
        'id' => 'api_options',
        'type' => 'title',
    ],
    [
        'title' => __('SmilePay invoice sandbox', 'ry-woocommerce-smilepay-invoice'),
        'id' => RY_WSI::OPTION_PREFIX . 'smilepay_invoice_testmode',
        'type' => 'checkbox',
        'default' => 'no',
        'desc' => __('Enable SmilePay invoice sandbox', 'ry-woocommerce-smilepay-invoice')
            . '<p class="description" style="margin-bottom:2px">' . __('Note: Recommend using this for development purposes only.', 'ry-woocommerce-smilepay-invoice') . '<p>',
    ],
    [
        'title' => __('Grvc', 'ry-woocommerce-smilepay-invoice'),
        'id' => RY_WSI::OPTION_PREFIX . 'smilepay_Grvc',
        'type' => 'text',
        'default' => '',
    ],
    [
        'title' => __('Verify_key', 'ry-woocommerce-smilepay-invoice'),
        'id' => RY_WSI::OPTION_PREFIX . 'smilepay_Verify_key',
        'type' => 'text',
        'default' => '',
    ],
    [
        'id' => 'api_options',
        'type' => 'sectionend',
    ],
];
