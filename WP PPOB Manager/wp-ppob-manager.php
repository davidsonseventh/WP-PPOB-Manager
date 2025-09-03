<?php
/**
 * Plugin Name: WP PPOB Manager
 * Description: Plugin PPOB lengkap dengan WooCommerce - Pulsa, PLN, BPJS, dll
 * Version: 1.0.0
 * Author: WP PPOB Team
 * Text Domain: wp-ppob
 * WC requires at least: 6.0
 * WC tested up to: 8.8
 */

if (!defined('ABSPATH')) exit;

// Define constants
define('WPPPOB_VERSION', '1.0.0');
define('WPPPOB_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WPPPOB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WPPPOB_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include files
require_once WPPPOB_PLUGIN_DIR . 'includes/class-ppob-loader.php';
require_once WPPPOB_PLUGIN_DIR . 'includes/class-ppob-activator.php';
require_once WPPPOB_PLUGIN_DIR . 'includes/class-ppob-deactivator.php';

// Activation/Deactivation
register_activation_hook(__FILE__, array('WPPPOB_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('WPPPOB_Deactivator', 'deactivate'));

// Initialize plugin
add_action('plugins_loaded', 'wppob_init');
function wppob_init() {
    WPPPOB_Loader::get_instance();
}