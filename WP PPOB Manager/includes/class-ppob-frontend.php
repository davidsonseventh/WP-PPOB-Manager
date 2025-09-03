<?php
defined( 'ABSPATH' ) || exit;

class WPPPOB_Frontend {
    public function __construct() {
        add_shortcode( 'wppob_form', [ $this, 'render_form' ] );
        add_shortcode( 'wppob_dashboard', [ $this, 'render_dashboard' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
    }

    public function enqueue_assets() {
        if ( is_page() && has_shortcode( get_post()->post_content, 'wppob_form' ) ) {
            wp_enqueue_style( 'wppob-frontend', WPPPOB_PLUGIN_URL . 'assets/css/frontend.css', [], WPPPOB_VERSION );
            wp_enqueue_script( 'wppob-frontend', WPPPOB_PLUGIN_URL . 'assets/js/frontend.js', [ 'jquery' ], WPPPOB_VERSION, true );
            wp_localize_script( 'wppob-frontend', 'wppob', [
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce'    => wp_create_nonce( 'wppob_frontend' ),
            ] );
        }
    }

    public function render_form() {
        ob_start();
        include WPPPOB_PLUGIN_DIR . 'templates/form-pulsa.php';
        return ob_get_clean();
    }

    public function render_dashboard() {
        if ( ! is_user_logged_in() ) {
            return '<p>' . __( 'Silakan login untuk melihat dashboard.', 'wp-ppob' ) . '</p>';
        }
        ob_start();
        include WPPPOB_PLUGIN_DIR . 'templates/dashboard-user.php';
        return ob_get_clean();
    }
}