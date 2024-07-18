<?php
/**
 * Plugin Name: RY SmilePay Invoice for WooCommerce
 * Plugin URI: https://ry-plugin.com/ry-woocommerce-smilepay-invoice
 * Description: WooCommerce order invoice for smilepay
 * Version: 2.0.1
 * Requires at least: 6.3
 * Requires PHP: 8.0
 * Requires Plugins: woocommerce
 * Author: Richer Yang
 * Author URI: https://richer.tw/
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 * Update URI: https://ry-plugin.com/ry-woocommerce-smilepay-invoice
 *
 * Text Domain: ry-woocommerce-smilepay-invoice
 * Domain Path: /languages
 *
 * WC requires at least: 8
 */

function_exists('plugin_dir_url') or exit('No direct script access allowed');

define('RY_WSI_VERSION', '2.0.1');
define('RY_WSI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RY_WSI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RY_WSI_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('RY_WSI_PLUGIN_LANGUAGES_DIR', plugin_dir_path(__FILE__) . '/languages');

require_once RY_WSI_PLUGIN_DIR . 'includes/main.php';

register_activation_hook(__FILE__, ['RY_WSI', 'plugin_activation']);
register_deactivation_hook(__FILE__, ['RY_WSI', 'plugin_deactivation']);

function RY_WSI(): RY_WSI
{
    return RY_WSI::instance();
}

RY_WSI();
