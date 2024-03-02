<?php
/**
 * Plugin Name: RY SmilePay Invoice for WooCommerce
 * Plugin URI: https://ry-plugin.com/ry-woocommerce-smilepay-invoice
 * Description: WooCommerce order invoice for smilepay
 * Version: 1.2.0
 * Requires at least: 6.3
 * Requires PHP: 8.0
 * Author: Richer Yang
 * Author URI: https://richer.tw/
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * Text Domain: ry-woocommerce-smilepay-invoice
 * Domain Path: /languages
 *
 * WC requires at least: 8
 */

function_exists('plugin_dir_url') or exit('No direct script access allowed');

define('RY_WSI_VERSION', '1.2.0');
define('RY_WSI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RY_WSI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RY_WSI_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('RY_WSI_PLUGIN_LANGUAGES_DIR', plugin_dir_path(__FILE__) . '/languages');

require_once RY_WSI_PLUGIN_DIR . 'class.ry-wsi.main.php';

register_activation_hook(__FILE__, ['RY_WSI', 'plugin_activation']);
register_deactivation_hook(__FILE__, ['RY_WSI', 'plugin_deactivation']);

add_action('init', ['RY_WSI', 'init'], 11);
