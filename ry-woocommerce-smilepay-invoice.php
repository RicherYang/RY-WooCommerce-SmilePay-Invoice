<?php
/**
 * Plugin Name: RY WooCommerce SmilePay Invoice
 * Plugin URI: https://ry-plugin.com/ry-woocommerce-smilepay-invoice
 * Version: 1.1.3
 * Requires at least: 5.6
 * Requires PHP: 7.4
 * Author: Richer Yang
 * Author URI: https://richer.tw/
 * License: GPLv3
 *
 * Text Domain: ry-woocommerce-smilepay-invoice
 * Domain Path: /languages
 *
 * WC requires at least: 7
 */

function_exists('plugin_dir_url') or exit('No direct script access allowed');

define('RY_WSI_VERSION', '1.1.3');
define('RY_WSI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RY_WSI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('RY_WSI_PLUGIN_BASENAME', plugin_basename(__FILE__));

require_once RY_WSI_PLUGIN_DIR . 'class.ry-wsi.main.php';

register_activation_hook(__FILE__, ['RY_WSI', 'plugin_activation']);
register_deactivation_hook(__FILE__, ['RY_WSI', 'plugin_deactivation']);

add_action('init', ['RY_WSI', 'init'], 11);
