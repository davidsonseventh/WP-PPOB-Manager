<?php
defined( 'ABSPATH' ) || exit;

class WPPPOB_Orders {
    public function __construct() {
        add_action( 'woocommerce_order_status_processing', [ $this, 'process_order' ], 10, 1 );
        add_action( 'wppob_check_status', [ $this, 'check_remote_status' ] );
    }

    public function process_order( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) return;

        foreach ( $order->get_items() as $item ) {
            $product = $item->get_product();
            if ( ! $product ) continue;

            $sku = $product->get_sku();
            if ( strpos( $sku, 'wppob-' ) !== 0 ) continue;

            $customer_no = wc_clean( $_POST['wppob_customer_no'] ?? $order->get_meta( '_wppob_customer_no' ) );
            if ( empty( $customer_no ) ) continue;

            $ref_id = uniqid( 'wppob_' );
            $price  = floatval( $item->get_total() );

            global $wpdb;
            $wpdb->insert(
                "{$wpdb->prefix}wppob_transactions",
                [
                    'order_id'     => $order_id,
                    'product_code' => $sku,
                    'customer_no'  => $customer_no,
                    'price'        => $price,
                    'profit'       => $price - floatval( $product->get_meta( '_wppob_base_price', true ) ),
                    'status'       => 'pending',
                    'remote_trx_id'=> $ref_id,
                ]
            );

            $api = new WPPPOB_API();
            $res = $api->topup( str_replace( 'wppob-', '', $sku ), $customer_no, $ref_id );

            if ( isset( $res['data']['status'] ) && $res['data']['status'] === 'Pending' ) {
                wp_schedule_single_event( time() + 30, 'wppob_check_status', [ $ref_id ] );
            }
        }
    }

    public function check_remote_status( $ref_id ) {
        global $wpdb;
        $row = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}wppob_transactions WHERE remote_trx_id = %s", $ref_id
        ) );

        if ( ! $row ) return;

        $api = new WPPPOB_API();
        $status = $api->get_transaction_status( $ref_id ); // endpoint belum ada di class API, tambahan diperlukan

        if ( isset( $status['data']['status'] ) ) {
            $remote_status = strtolower( $status['data']['status'] );
            $map = [
                'success' => 'success',
                'failed'  => 'failed',
                'pending' => 'pending',
            ];

            $new_status = $map[ $remote_status ] ?? 'pending';
            $wpdb->update(
                "{$wpdb->prefix}wppob_transactions",
                [ 'status' => $new_status ],
                [ 'remote_trx_id' => $ref_id ]
            );

            $order = wc_get_order( $row->order_id );
            if ( $order ) {
                $order->update_status( $new_status );
            }
        }
    }
}