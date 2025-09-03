<?php
defined( 'ABSPATH' ) || exit;

class WPPPOB_Products {
    public function __construct() {
        add_action( 'wppob_hourly_sync', [ $this, 'sync_from_api' ] );
        if ( ! wp_next_scheduled( 'wppob_hourly_sync' ) ) {
            wp_schedule_event( time(), 'hourly', 'wppob_hourly_sync' );
        }
    }

    public function sync_from_api() {
        $api = new WPPPOB_API();
        $list = $api->get_price_list( 'prepaid' );

        if ( empty( $list['data'] ) ) return;

        foreach ( $list['data'] as $item ) {
            $sku   = sanitize_title( $item['buyer_sku_code'] );
            $price = floatval( $item['price'] ) + floatval( get_option( 'wppob_margin', 0 ) );

            $product_id = wc_get_product_id_by_sku( $sku );
            if ( ! $product_id ) {
                $product = new WC_Product_Simple();
                $product->set_sku( $sku );
                $product->set_name( sanitize_text_field( $item['product_name'] ) );
                $product->set_virtual( true );
                $product->set_catalog_visibility( 'visible' );
            } else {
                $product = wc_get_product( $product_id );
            }

            $product->set_regular_price( $price );
            $product->save();
        }
    }
}