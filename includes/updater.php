<?php

final class RY_WSI_Updater
{
    protected static $_instance = null;

    public static function instance(): RY_WSI_Updater
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
            self::$_instance->do_init();
        }

        return self::$_instance;
    }

    protected function do_init(): void
    {
        add_filter('update_plugins_ry-plugin.com', [$this, 'update_plugin'], 10, 2);

        add_filter('plugins_api', [$this, 'modify_plugin_details'], 10, 3);
    }

    public function update_plugin($update, $plugin_data)
    {
        if ('RY SmilePay Invoice for WooCommerce' !== $plugin_data['Name']) {
            return $update;
        }

        $update = RY_WSI_LinkServer::instance()->check_version();
        if (is_array($update)) {
            $update['id'] = 'https://ry-plugin.com/ry-woocommerce-smilepay-invoice';
            $update['url'] = 'https://ry-plugin.com/ry-woocommerce-smilepay-invoice';
            $update['slug'] = 'ry-woocommerce-smilepay-invoice';
        }

        return $update;
    }

    public static function modify_plugin_details($result, $action, $args)
    {
        if ($action !== 'plugin_information') {
            return $result;
        }

        if ($args->slug != 'ry-woocommerce-smilepay-invoice') {
            return $result;
        }

        $response = RY_WSI_LinkServer::instance()->get_info();
        if (!empty($response)) {
            return (object) $response;
        }

        return $result;
    }
}
