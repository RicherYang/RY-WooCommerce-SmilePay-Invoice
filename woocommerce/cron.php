<?php

final class RY_WSI_WC_Cron
{
    public static function add_action()
    {
        add_action('ry_wsi_auto_get_invoice', [__CLASS__, 'get_invoice']);
        add_action('ry_wsi_auto_invalid_invoice', [__CLASS__, 'invalid_invoice']);
    }

    public static function get_invoice($order_ID)
    {
        RY_WSI_WC_Invoice_Api::instance()->get($order_ID);
    }

    public static function invalid_invoice($order_ID)
    {
        RY_WSI_WC_Invoice_Api::instance()->invalid($order_ID);
    }
}
