<?php

final class RY_WSI
{
    public static $options = [];
    public static $option_prefix = 'RY_WSI_';

    private static $initiated = false;
    private static $activate_status = false;

    public static function init()
    {
        if (!self::$initiated) {
            self::$initiated = true;

            load_plugin_textdomain('ry-woocommerce-smilepay-invoice', false, plugin_basename(dirname(RY_WSI_PLUGIN_BASENAME)) . '/languages');

            if (!defined('WC_VERSION')) {
                return;
            }

            include_once RY_WSI_PLUGIN_DIR . 'include/license.php';
            include_once RY_WSI_PLUGIN_DIR . 'include/link-server.php';
            include_once RY_WSI_PLUGIN_DIR . 'include/updater.php';
            include_once RY_WSI_PLUGIN_DIR . 'woocommerce/admin/notes/license-auto-deactivate.php';

            self::$activate_status = RY_WSI_License::valid_key();

            include_once RY_WSI_PLUGIN_DIR . 'class.ry-wsi.update.php';
            RY_WSI_update::update();

            if (is_admin()) {
                include_once RY_WSI_PLUGIN_DIR . 'class.ry-wsi.admin.php';
                if (!self::$activate_status) {
                    add_action('woocommerce_settings_start', [RY_WSI_admin::instance(), 'add_license_notice']);
                }
            }

            include_once RY_WSI_PLUGIN_DIR . 'woocommerce/invoice-basic.php';
            if (self::$activate_status) {
                include_once RY_WSI_PLUGIN_DIR . 'include/cron.php';
                include_once RY_WSI_PLUGIN_DIR . 'woocommerce/settings/class-settings.invoice.php';

                if ('yes' === self::get_option('enabled_invoice', 'no')) {
                    include_once RY_WSI_PLUGIN_DIR . 'woocommerce/invoice.php';
                }
            }
        }
    }

    public static function get_option($option, $default = false)
    {
        return get_option(self::$option_prefix . $option, $default);
    }

    public static function update_option($option, $value)
    {
        return update_option(self::$option_prefix . $option, $value);
    }

    public static function delete_option($option)
    {
        return delete_option(self::$option_prefix . $option);
    }

    public static function get_transient($transient)
    {
        return get_transient(self::$option_prefix . $transient);
    }

    public static function set_transient($transient, $value, $expiration = 0)
    {
        return set_transient(self::$option_prefix . $transient, $value, $expiration);
    }

    public static function delete_transient($transient)
    {
        return delete_transient(self::$option_prefix . $transient);
    }

    public static function plugin_activation() {}

    public static function plugin_deactivation()
    {
        wp_unschedule_hook(self::$option_prefix . 'check_expire');
        wp_unschedule_hook(self::$option_prefix . 'check_update');
    }
}
