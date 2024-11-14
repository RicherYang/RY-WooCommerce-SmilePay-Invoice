<?php

final class RY_WSI_WC_Admin_Setting_Invoice
{
    protected static $_instance = null;

    public static function instance(): RY_WSI_WC_Admin_Setting_Invoice
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
            self::$_instance->do_init();
        }

        return self::$_instance;
    }

    protected function do_init()
    {
        add_filter('woocommerce_get_sections_rytools', [$this, 'add_sections'], 11);
        add_filter('woocommerce_get_settings_rytools', [$this, 'add_setting'], 10, 2);
        add_action('woocommerce_update_options_rytools_smilepay_invoice', [$this, 'check_option']);
        add_filter('ry_setting_section_tools', '__return_false');
    }

    public function add_sections($sections)
    {
        if (isset($sections['tools'])) {
            $add_idx = array_search('tools', array_keys($sections));
            $sections = array_slice($sections, 0, $add_idx) + [
                'smilepay_invoice' => __('SimlePay invoice', 'ry-woocommerce-smilepay-invoice'),
            ] + array_slice($sections, $add_idx);
        } else {
            $sections['smilepay_invoice'] = __('SimlePay invoice', 'ry-woocommerce-smilepay-invoice');
            $sections['tools'] = __('Tools', 'ry-woocommerce-smilepay-invoice');
        }

        return $sections;
    }

    public function add_setting($settings, $current_section)
    {
        if ('smilepay_invoice' == $current_section) {
            if (!function_exists('simplexml_load_string')) {
                echo '<div class="notice notice-error"><p><strong>RY ECPay Invoice for WooCommerce</strong> ' . esc_html__('Required PHP function `simplexml_load_string`.', 'ry-woocommerce-smilepay-invoice') . '</p></div>';
            }

            $settings = include RY_WSI_PLUGIN_DIR . 'woocommerce/admin/settings/settings-invoice.php';
        }
        return $settings;
    }

    public function check_option()
    {
        $enable_list = apply_filters('enable_ry_invoice', []);
        if (1 == count($enable_list)) {
            if ($enable_list != ['smilepay']) {
                WC_Admin_Settings::add_error(__('Not recommended enable two invoice module/plugin at the same time!', 'ry-woocommerce-smilepay-invoice'));
            }
        } elseif (1 < count($enable_list)) {
            WC_Admin_Settings::add_error(__('Not recommended enable two invoice module/plugin at the same time!', 'ry-woocommerce-smilepay-invoice'));
        }

        if (!RY_WSI_WC_Invoice::instance()->is_testmode()) {
            if (empty(RY_WSI::get_option('smilepay_Grvc')) || empty(RY_WSI::get_option('smilepay_Verify_key'))) {
                WC_Admin_Settings::add_error(__('SimlePay invoice method failed to enable!', 'ry-woocommerce-smilepay-invoice'));
            }
        }

        if (!preg_match('/^[a-z0-9]*$/i', RY_WSI::get_option('order_prefix'))) {
            WC_Admin_Settings::add_error(__('Order no prefix only letters and numbers allowed', 'ry-woocommerce-smilepay-invoice'));
            RY_WSI::update_option('order_prefix', '');
        }
    }
}
