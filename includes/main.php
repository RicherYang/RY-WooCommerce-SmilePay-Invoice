<?php

final class RY_WSI
{
    public const OPTION_PREFIX = 'RY_WSI_';

    protected static $_instance = null;

    public static function instance(): RY_WSI
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
            self::$_instance->do_init();
        }

        return self::$_instance;
    }

    protected function do_init(): void
    {
        load_plugin_textdomain('ry-woocommerce-smilepay-invoice', false, plugin_basename(dirname(__DIR__)) . '/languages');

        if (is_admin()) {
            include_once RY_WSI_PLUGIN_DIR . 'includes/update.php';
            RY_WSI_update::update();
        }

        add_action('woocommerce_init', [$this, 'do_woo_init'], 11);
    }

    public function do_woo_init(): void
    {
        include_once RY_WSI_PLUGIN_DIR . 'woocommerce/abstracts/abstract-model.php';
        include_once RY_WSI_PLUGIN_DIR . 'includes/functions.php';

        include_once RY_WSI_PLUGIN_DIR . 'woocommerce/admin/notes/license-auto-deactivate.php';
        include_once RY_WSI_PLUGIN_DIR . 'includes/license.php';
        include_once RY_WSI_PLUGIN_DIR . 'includes/link-server.php';
        include_once RY_WSI_PLUGIN_DIR . 'includes/updater.php';
        RY_WSI_Updater::instance();

        if (is_admin()) {
            include_once RY_WSI_PLUGIN_DIR . 'woocommerce/admin/admin.php';
            RY_WSI_WC_Admin::instance();
        }

        include_once RY_WSI_PLUGIN_DIR . 'woocommerce/invoice-basic.php';
        RY_WSI_WC_Invoice_Basic::instance();

        if (RY_WSI_License::instance()->is_activated()) {
            include_once RY_WSI_PLUGIN_DIR . 'includes/cron.php';
            RY_WSI_Cron::add_action();

            include_once RY_WSI_PLUGIN_DIR . 'woocommerce/cron.php';
            RY_WSI_WC_Cron::add_action();

            include_once RY_WSI_PLUGIN_DIR . 'woocommerce/invoice.php';
            RY_WSI_WC_Invoice::instance();
        }
    }

    public static function get_option($option, $default = false)
    {
        return get_option(self::OPTION_PREFIX . $option, $default);
    }

    public static function update_option($option, $value, $autoload = null)
    {
        return update_option(self::OPTION_PREFIX . $option, $value, $autoload);
    }

    public static function delete_option($option)
    {
        return delete_option(self::OPTION_PREFIX . $option);
    }

    public static function get_transient($transient)
    {
        return get_transient(self::OPTION_PREFIX . $transient);
    }

    public static function set_transient($transient, $value, $expiration = 0)
    {
        return set_transient(self::OPTION_PREFIX . $transient, $value, $expiration);
    }

    public static function delete_transient($transient)
    {
        return delete_transient(self::OPTION_PREFIX . $transient);
    }

    public static function plugin_activation() {}

    public static function plugin_deactivation()
    {
        wp_unschedule_hook(self::OPTION_PREFIX . 'check_expire');
        wp_unschedule_hook(self::OPTION_PREFIX . 'check_update');
    }
}
