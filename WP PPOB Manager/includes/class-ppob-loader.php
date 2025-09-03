<?php
defined( 'ABSPATH' ) || exit;

class WPPPOB_Loader {
    protected static $_instance = null;

    public static function get_instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __construct() {
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_frontend_hooks();
    }

    private function load_dependencies() {
        require_once WPPPOB_PLUGIN_DIR . 'includes/helpers.php';
        require_once WPPPOB_PLUGIN_DIR . 'includes/class-ppob-products.php';
        require_once WPPPOB_PLUGIN_DIR . 'includes/class-ppob-orders.php';
        require_once WPPPOB_PLUGIN_DIR . 'includes/class-ppob-balances.php';
        require_once WPPPOB_PLUGIN_DIR . 'includes/class-ppob-users.php';
        require_once WPPPOB_PLUGIN_DIR . 'includes/class-ppob-frontend.php';
        require_once WPPPOB_PLUGIN_DIR . 'includes/class-ppob-ajax.php';
        require_once WPPPOB_PLUGIN_DIR . 'includes/class-ppob-admin.php';
    }

    private function set_locale() {
        load_plugin_textdomain( 'wp-ppob', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    private function define_admin_hooks() {
        if ( is_admin() ) {
            new WPPPOB_Admin();
        }
    }

    private function define_frontend_hooks() {
        if ( ! is_admin() ) {
            new WPPPOB_Frontend();
        }
    }
}