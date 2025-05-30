<?php

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

        if (version_compare($now_version, '2.0.8', '<')) {
            RY_WSI::update_option('version', '2.0.8', true);
        }
    }
}
