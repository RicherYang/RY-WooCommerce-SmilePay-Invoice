<?php

defined('ABSPATH') or exit;

final class RY_WSI_update
{
    public static function update()
    {
        $now_version = RY_WSI::get_option('version');

        if (false === $now_version) {
            $now_version = '0.0.0';
        }
        if (RY_WSI_VERSION === $now_version) {
            return;
        }

        if (version_compare($now_version, '2.0.0', '<')) {
            wp_unschedule_hook(RY_WSI::OPTION_PREFIX . 'check_update');
            RY_WSI::update_option('smilepay_invoice_log', RY_WSI::update_option('invoice_log', 'no'), true);
            RY_WSI::update_option('smilepay_invoice_testmode', RY_WSI::update_option('smilepay_testmode', 'no'), true);

            RY_WSI::update_option('version', '2.0.0', true);
        }

        if (version_compare($now_version, '2.0.1', '<')) {
            RY_WSI::delete_option('enabled_invoice');

            RY_WSI::update_option('version', '2.0.1', true);
        }

        if (version_compare($now_version, '2.3.0', '<')) {
            if (RY_WSI::get_option('smilepay_Grvc') !== false) {
                RY_WSI::update_option('apiinfo', [
                    'prefix' => RY_WSI::get_option('order_prefix'),
                    'use_sku' => RY_WSI::get_option('use_sku_as_name'),
                    'abnormal_mode' => RY_WSI::get_option('amount_abnormal_mode'),
                    'abnormal_product' => RY_WSI::get_option('amount_abnormal_product'),
                    'trackcode' => RY_WSI::get_option('used_track'),
                    'testmode' => RY_WSI::get_option('smilepay_invoice_testmode'),
                    'Grvc' => RY_WSI::get_option('smilepay_Grvc'),
                    'Verify_key' => RY_WSI::get_option('smilepay_Verify_key'),
                ], false);
                RY_WSI::delete_option('order_prefix');
                RY_WSI::delete_option('use_sku_as_name');
                RY_WSI::delete_option('amount_abnormal_mode');
                RY_WSI::delete_option('amount_abnormal_product');
                RY_WSI::delete_option('used_track');
                RY_WSI::delete_option('smilepay_invoice_testmode');
                RY_WSI::delete_option('smilepay_Grvc');
                RY_WSI::delete_option('smilepay_Verify_key');
            }
            if (RY_WSI::get_option('skip_foreign_order') !== false) {
                RY_WSI::update_option('skip_foreign_order', RY_WSI::get_option('skip_foreign_order'), true);
            }
            RY_WSI::delete_option('invoice_log');
            RY_WSI::delete_option('smilepay_testmode');

            RY_WSI::update_option('version', '2.3.0', true);
        }
    }
}
