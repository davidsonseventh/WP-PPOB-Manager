<?php
class WPPPOB_Activator {
    public static function activate() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Balances table
        $sql_balances = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wppob_balances (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) NOT NULL,
            balance decimal(12,2) DEFAULT 0.00,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY user_id (user_id)
        ) $charset_collate;";
        
        // Transactions table
        $sql_transactions = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wppob_transactions (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            order_id bigint(20) NOT NULL,
            product_code varchar(100) NOT NULL,
            customer_no varchar(50) NOT NULL,
            price decimal(12,2) NOT NULL,
            profit decimal(12,2) DEFAULT 0.00,
            status enum('pending','success','failed','refunded') DEFAULT 'pending',
            remote_trx_id varchar(100),
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY order_id (order_id)
        ) $charset_collate;";
        
        // API logs table
        $sql_logs = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}wppob_api_logs (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            trx_id varchar(100),
            request text,
            response text,
            timestamp datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql_balances);
        dbDelta($sql_transactions);
        dbDelta($sql_logs);
        
        // Set default options
        add_option('wppob_margin', 500);
        add_option('wppob_api_username', '');
        add_option('wppob_api_key', '');
    }
}