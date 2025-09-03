<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @package WP PPOB Manager
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

global $wpdb;

/* 1.  Hapus tabel kustom */
$tables = array(
    $wpdb->prefix . 'wppob_balances',
    $wpdb->prefix . 'wppob_transactions',
    $wpdb->prefix . 'wppob_api_logs',
);

foreach ( $tables as $table ) {
    $wpdb->query( "DROP TABLE IF EXISTS {$table}" );
}

/* 2.  Hapus option yang disimpan */
$options = array(
    'wppob_margin',
    'wppob_api_username',
    'wppob_api_key',
    'wppob_last_sync',
    'wppob_version',
);

foreach ( $options as $opt ) {
    delete_option( $opt );
}

/* 3.  Hapus semua produk virtual PPOB (opsional) */
$products = wc_get_products( array(
    'type'      => 'simple',
    'limit'     => -1,
    'return'    => 'ids',
    'sku'       => 'wppob-*', // wildcard tidak langsung work di wc_get_products,
                             // jadi kita query langsung
) );

$product_ids = $wpdb->get_col(
    "SELECT p.ID FROM {$wpdb->posts} p
     LEFT JOIN {$wpdb->postmeta} m ON p.ID = m.post_id
     WHERE p.post_type = 'product'
       AND m.meta_key = '_sku'
       AND m.meta_value LIKE 'wppob-%'"
);

foreach ( $product_ids as $pid ) {
    wp_delete_post( $pid, true );
}