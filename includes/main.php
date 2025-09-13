<?php

include_once RY_WSI_PLUGIN_DIR . 'includes/ry-global/abstract-basic.php';

final class RY_WSI extends RY_Abstract_Basic
{
    public const OPTION_PREFIX = 'RY_WSI_';

    public const PLUGIN_NAME = 'RY SmilePay Invoice for WooCommerce';

    protected static $_instance = null;

    public RY_WSI_Admin $admin;

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

        include_once RY_WSI_PLUGIN_DIR . 'includes/license.php';
        include_once RY_WSI_PLUGIN_DIR . 'includes/link-server.php';
        include_once RY_WSI_PLUGIN_DIR . 'includes/updater.php';
        RY_WSI_Updater::instance();

        if (is_admin()) {
            include_once RY_WSI_PLUGIN_DIR . 'includes/ry-global/admin-license.php';
            include_once RY_WSI_PLUGIN_DIR . 'admin/admin.php';
            $this->admin = RY_WSI_Admin::instance();

            include_once RY_WSI_PLUGIN_DIR . 'woocommerce/admin/admin.php';
            RY_WSI_WC_Admin::instance();
        }

        include_once RY_WSI_PLUGIN_DIR . 'woocommerce/invoice-basic.php';
        RY_WSI_WC_Invoice_Basic::instance();

        if (RY_WSI_License::instance()->is_activated()) {
            include_once RY_WSI_PLUGIN_DIR . 'includes/cron.php';
            RY_WSI_Cron::add_action();

            include_once RY_WSI_PLUGIN_DIR . 'woocommerce/invoice.php';
            RY_WSI_WC_Invoice::instance();
        }
    }

    public static function plugin_activation() {}

    public static function plugin_deactivation()
    {
        wp_unschedule_hook(self::OPTION_PREFIX . 'check_expire');
        wp_unschedule_hook(self::OPTION_PREFIX . 'check_update');
    }
}
