<?php
defined( 'ABSPATH' ) || exit;

class WPPPOB_Deactivator {
    public static function deactivate() {
        // Hapus cron job jika ada
        wp_clear_scheduled_hook( 'wppob_hourly_sync' );

        // Hapus transient cache
        global $wpdb;
        $wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_wppob_%'" );
    }
}