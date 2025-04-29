<?php

function rywsi_link_error_to_msg($error)
{
    static $error_msg = [];
    if (empty($error_msg)) {
        $error_msg = [
            'Unknown key' => __('Unknown key', 'ry-woocommerce-smilepay-invoice'),
            'Locked key' => __('Locked key', 'ry-woocommerce-smilepay-invoice'),
            'Unknown target url' => __('Unknown target url', 'ry-woocommerce-smilepay-invoice'),
            'Used key' => __('Used key', 'ry-woocommerce-smilepay-invoice'),
            'Is tried' => __('Is tried', 'ry-woocommerce-smilepay-invoice'),
        ];
    }

    return $error_msg[$error] ?? $error;
}

function rywsi_invoice_type_to_name($invoice_type)
{
    static $type_name = [];
    if (empty($type_name)) {
        $type_name = [
            'personal' => _x('personal', 'invoice type', 'ry-woocommerce-smilepay-invoice'),
            'company' => _x('company', 'invoice type', 'ry-woocommerce-smilepay-invoice'),
            'donate' => _x('donate', 'invoice type', 'ry-woocommerce-smilepay-invoice'),
        ];
    }

    return $type_name[$invoice_type] ?? $invoice_type;
}

function rywsi_carruer_type_to_name($carruer_type)
{
    static $type_name = [];
    if (empty($type_name)) {
        $type_name = [
            'none' => _x('none', 'carruer type', 'ry-woocommerce-smilepay-invoice'),
            'smilepay_host' => _x('smilepay_host', 'carruer type', 'ry-woocommerce-smilepay-invoice'),
            'MOICA' => _x('MOICA', 'carruer type', 'ry-woocommerce-smilepay-invoice'),
            'phone_barcode' => _x('phone_barcode', 'carruer type', 'ry-woocommerce-smilepay-invoice'),
        ];
    }

    return $type_name[$carruer_type] ?? $carruer_type;
}
