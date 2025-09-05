<?php
defined('ABSPATH') || exit;

class WPPPOB_Admin {

    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        add_action('admin_init', [$this, 'register_settings']);
        add_action('admin_post_wppob_save_category', [$this, 'handle_save_category']);
    }

    public function add_admin_menu() {
        add_menu_page('PPOB Manager', 'PPOB Manager', 'manage_options', 'wppob-dashboard', [$this, 'render_dashboard_page'], 'dashicons-store', 58);
        add_submenu_page('wppob-dashboard', 'Dashboard', 'Dashboard', 'manage_options', 'wppob-dashboard');
        add_submenu_page('wppob-dashboard', 'Transaksi', 'Transaksi', 'manage_options', 'wppob-transactions', [$this, 'render_transactions_page']);
        add_submenu_page('wppob-dashboard', 'Manajemen Produk', 'Produk', 'manage_options', 'wppob-products', [$this, 'render_products_page']);
        add_submenu_page('wppob-dashboard', 'Manajemen Pengguna', 'Pengguna', 'manage_options', 'wppob-users', [$this, 'render_users_page']);
        add_submenu_page('wppob-dashboard', 'Kategori Tampilan', 'Kategori Tampilan', 'manage_options', 'wppob-display-categories', [$this, 'render_display_categories_page']);
        add_submenu_page('wppob-dashboard', 'Pengaturan', 'Pengaturan', 'manage_options', 'wppob-settings', [$this, 'render_settings_page']);
    }

    public function enqueue_admin_assets($hook) {
        // Hanya muat aset jika berada di salah satu halaman admin PPOB
        if (strpos($hook, 'wppob-') !== false) {
            
            // **BARIS INI SANGAT PENTING UNTUK UPLOADER GAMBAR**
            wp_enqueue_media();
            
            wp_enqueue_style('wppob-admin-css', WPPPOB_PLUGIN_URL . 'assets/css/admin.css', [], WPPPOB_VERSION);
            wp_enqueue_script('wppob-admin-js', WPPPOB_PLUGIN_URL . 'assets/js/admin.js', ['jquery'], WPPPOB_VERSION, true);
            wp_localize_script('wppob-admin-js', 'wppob_admin_params', [
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('wppob_admin_nonce')
            ]);
        }
    }
    
    public function handle_save_category() {
        if (!isset($_POST['_wpnonce']) || !wp_verify_nonce($_POST['_wpnonce'], 'wppob_save_category_nonce')) { wp_die('Verifikasi keamanan gagal.'); }
        if (!current_user_can('manage_options')) { wp_die('Anda tidak diizinkan.'); }

        global $wpdb;
        $table_name = $wpdb->prefix . 'wppob_display_categories';

        $id = isset($_POST['category_id']) ? intval($_POST['category_id']) : 0;
        $products = isset($_POST['assigned_products']) ? array_map('intval', $_POST['assigned_products']) : [];
        
        $data = [
            'name' => sanitize_text_field($_POST['cat_name']),
            'parent_id' => intval($_POST['cat_parent']),
            'image_id' => intval($_POST['cat_image_id']),
            'display_style' => sanitize_key($_POST['display_style']),
            'display_mode' => sanitize_key($_POST['display_mode']), // Simpan data baru
            'image_size_px' => intval($_POST['image_size_px']),
            'border_radius' => intval($_POST['border_radius']),
            'assigned_products' => json_encode($products)
        ];
        $format = ['%s', '%d', '%d', '%s', '%s', '%d', '%d', '%s'];

        if (empty($data['name'])) { wp_die('Nama kategori tidak boleh kosong.'); }

        if ($id > 0) {
            $wpdb->update($table_name, $data, ['id' => $id], $format, ['%d']);
        } else {
            $wpdb->insert($table_name, $data, $format);
        }
        
        wp_redirect(admin_url('admin.php?page=wppob-display-categories&message=success'));
        exit;
    }

    public function register_settings() { /* ... fungsi register settings lengkap ... */ }
    public function render_api_username_field() { /* ... */ }
    public function render_api_key_field() { /* ... */ }
    public function render_profit_type_field() { /* ... */ }
    public function render_profit_amount_field() { /* ... */ }
    
    public function render_dashboard_page() { include_once WPPPOB_PLUGIN_DIR . 'templates/admin/dashboard.php'; }
    public function render_transactions_page() { include_once WPPPOB_PLUGIN_DIR . 'templates/admin/transactions.php'; }
    public function render_products_page() { include_once WPPPOB_PLUGIN_DIR . 'templates/admin/products.php'; }
    public function render_users_page() { include_once WPPPOB_PLUGIN_DIR . 'templates/admin/users.php'; }
    public function render_settings_page() { include_once WPPPOB_PLUGIN_DIR . 'templates/admin/settings.php'; }
    public function render_display_categories_page() { include_once WPPPOB_PLUGIN_DIR . 'templates/admin/manage-display-categories.php'; }
}