<?php

final class RY_WSI_Invoice_setting
{
    private static $initiated = false;

    public static function init()
    {
        if (!self::$initiated) {
            self::$initiated = true;
        }

        if (is_admin()) {
            add_filter('woocommerce_get_sections_rytools', [__CLASS__, 'add_sections'], 11);
            add_filter('woocommerce_get_settings_rytools', [__CLASS__, 'add_setting'], 10, 2);
            add_action('woocommerce_update_options_rytools_smilepay_invoice', [__CLASS__, 'check_option']);
            add_filter('ry_setting_section_tools', '__return_false');
        }
    }

    public static function add_sections($sections)
    {
        if (isset($sections['tools'])) {
            $add_idx = array_search('tools', array_keys($sections));
            $sections = array_slice($sections, 0, $add_idx) + [
                'smilepay_invoice' => __('SimlePay invoice', 'ry-woocommerce-smilepay-invoice')
            ] + array_slice($sections, $add_idx);
        } else {
            $sections['smilepay_invoice'] = __('SimlePay invoice', 'ry-woocommerce-smilepay-invoice');
            $sections['tools'] = __('Tools', 'ry-woocommerce-smilepay-invoice');
        }

        return $sections;
    }

    public static function add_setting($settings, $current_section)
    {
        if ($current_section == 'smilepay_invoice') {
            $settings = include RY_WSI_PLUGIN_DIR . 'woocommerce/settings/settings-smilepay-invoice.php';
        }
        return $settings;
    }

    public static function check_option()
    {
        if ('yes' == RY_WSI::get_option('enabled_invoice', 'no')) {
            $enable_list = apply_filters('enable_ry_invoice', []);
            if (count($enable_list) == 1) {
                if ($enable_list != ['smilepay']) {
                    WC_Admin_Settings::add_error(__('Not recommended enable two invoice module/plugin at the same time!', 'ry-woocommerce-smilepay-invoice'));
                }
            } elseif (count($enable_list) > 1) {
                WC_Admin_Settings::add_error(__('Not recommended enable two invoice module/plugin at the same time!', 'ry-woocommerce-smilepay-invoice'));
            }

            if ('yes' != RY_WSI::get_option('smilepay_testmode', 'no')) {
                if (empty(RY_WSI::get_option('smilepay_Grvc')) || empty(RY_WSI::get_option('smilepay_Verify_key'))) {
                    WC_Admin_Settings::add_error(__('SimlePay invoice method failed to enable!', 'ry-woocommerce-smilepay-invoice'));
                    RY_WSI::update_option('enabled_invoice', 'no');
                }
            }
        }

        if (!preg_match('/^[a-z0-9]*$/i', RY_WSI::get_option('order_prefix'))) {
            WC_Admin_Settings::add_error(__('Order no prefix only letters and numbers allowed', 'ry-woocommerce-smilepay-invoice'));
            RY_WSI::update_option('order_prefix', '');
        }

        if (!is_callable('simplexml_load_string')) {
            WC_Admin_Settings::add_error(__('SimlePay invoice method failed to enable!', 'ry-woocommerce-ecpay-invoice')
                . __('Required PHP function simplexml_load_string.', 'ry-woocommerce-ecpay-invoice'));
            RY_WEI::update_option('enabled_invoice', 'no');
        }
    }
}

RY_WSI_Invoice_setting::init();
