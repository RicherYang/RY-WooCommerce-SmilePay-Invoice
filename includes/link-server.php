<?php

include_once RY_WSI_PLUGIN_DIR . 'includes/ry-global/abstract-link-server.php';

final class RY_WSI_LinkServer extends RY_Abstract_Link_Server
{
    protected static $_instance = null;

    protected $plugin_slug = 'ry-woocommerce-smilepay-invoice';

    public static function instance(): RY_WSI_LinkServer
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    protected function get_user_agent()
    {
        return sprintf(
            'RY_WSI %s (WordPress/%s WooCommerce/%s)',
            RY_WSI_VERSION,
            get_bloginfo('version'),
            WC_VERSION,
        );
    }
}
