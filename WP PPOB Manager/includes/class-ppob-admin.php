<?php
class WPPPOB_Admin {
    public function __construct() {
        add_action('admin_menu', [$this, 'add_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_assets']);
        add_action('wp_dashboard_setup', [$this, 'add_dashboard_widgets']);
    }
    
    public function add_menu() {
        add_menu_page(
            'PPOB Manager',
            'PPOB Manager',
            'manage_options',
            'wppob-manager',
            [$this, 'render_dashboard'],
            'dashicons-money-alt',
            56
        );
        
        add_submenu_page(
            'wppob-manager',
            'Produk',
            'Produk',
            'manage_options',
            'wppob-products',
            [$this, 'render_products']
        );
        
        add_submenu_page(
            'wppob-manager',
            'Transaksi',
            'Transaksi',
            'manage_options',
            'wppob-transactions',
            [$this, 'render_transactions']
        );
        
        add_submenu_page(
            'wppob-manager',
            'Pengguna',
            'Pengguna',
            'manage_options',
            'wppob-users',
            [$this, 'render_users']
        );
        
        add_submenu_page(
            'wppob-manager',
            'Pengaturan',
            'Pengaturan',
            'manage_options',
            'wppob-settings',
            [$this, 'render_settings']
        );
    }
    
    public function enqueue_assets($hook) {
        if (strpos($hook, 'wppob') !== false) {
            wp_enqueue_style('wppob-admin', WPPPOB_PLUGIN_URL . 'assets/css/admin.css', [], WPPPOB_VERSION);
            wp_enqueue_script('wppob-admin', WPPPOB_PLUGIN_URL . 'assets/js/admin.js', ['jquery'], WPPPOB_VERSION, true);
        }
    }
    
    public function add_dashboard_widgets() {
        wp_add_dashboard_widget(
            'wppob_summary',
            'PPOB Summary',
            [$this, 'dashboard_widget']
        );
    }
    
    public function dashboard_widget() {
        global $wpdb;
        
        $today = $wpdb->get_var("SELECT SUM(profit) FROM {$wpdb->prefix}wppob_transactions WHERE DATE(created_at) = CURDATE()");
        $week = $wpdb->get_var("SELECT SUM(profit) FROM {$wpdb->prefix}wppob_transactions WHERE WEEK(created_at) = WEEK(CURDATE())");
        $month = $wpdb->get_var("SELECT SUM(profit) FROM {$wpdb->prefix}wppob_transactions WHERE MONTH(created_at) = MONTH(CURDATE())");
        
        echo '<div class="wppob-summary">';
        echo '<p><strong>Profit Hari Ini:</strong> Rp ' . number_format($today ?: 0, 0, ',', '.') . '</p>';
        echo '<p><strong>Profit Minggu Ini:</strong> Rp ' . number_format($week ?: 0, 0, ',', '.') . '</p>';
        echo '<p><strong>Profit Bulan Ini:</strong> Rp ' . number_format($month ?: 0, 0, ',', '.') . '</p>';
        echo '</div>';
    }
    
    public function render_dashboard() {
        include WPPPOB_PLUGIN_DIR . 'templates/admin/dashboard.php';
    }
    
    public function render_products() {
        include WPPPOB_PLUGIN_DIR . 'templates/admin/products.php';
    }
    
    public function render_transactions() {
        include WPPPOB_PLUGIN_DIR . 'templates/admin/transactions.php';
    }
    
    public function render_users() {
        include WPPPOB_PLUGIN_DIR . 'templates/admin/users.php';
    }
    
    public function render_settings() {
        include WPPPOB_PLUGIN_DIR . 'templates/admin/settings.php';
    }
}