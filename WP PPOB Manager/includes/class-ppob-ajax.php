<?php
defined( 'ABSPATH' ) || exit;

class WPPPOB_Ajax {
    public function __construct() {
        add_action( 'wp_ajax_wppob_get_nominals', [ $this, 'get_nominals' ] );
        add_action( 'wp_ajax_nopriv_wppob_get_nominals', [ $this, 'get_nominals' ] );
        add_action( 'wp_ajax_wppob_submit_order', [ $this, 'submit_order' ] );
        add_action( 'wp_ajax_nopriv_wppob_submit_order', [ $this, 'submit_order' ] );

        add_action( 'wp_ajax_wppob_sync_products', [ $this, 'sync_products' ] );
        add_action( 'wp_ajax_wppob_adjust_balance', [ $this, 'adjust_balance' ] );
    }

    public function get_nominals() {
        check_ajax_referer( 'wppob_frontend', 'nonce' );
        $operator = sanitize_text_field( $_POST['operator'] ?? '' );
        if ( ! $operator ) wp_send_json_error( 'Operator tidak valid.' );

        $api = new WPPPOB_API();
        $list = $api->get_price_list( 'prepaid' );
        $data = [];

        foreach ( $list['data'] ?? [] as $item ) {
            if ( strpos( $item['brand'], $operator ) !== false ) {
                $data[] = [
                    'code'  => $item['buyer_sku_code'],
                    'name'  => $item['product_name'],
                    'price' => floatval( $item['price'] ) + floatval( get_option( 'wppob_margin', 0 ) ),
                ];
            }
        }

        wp_send_json_success( $data );
    }

    public function submit_order() {
        check_ajax_referer( 'wppob_frontend', 'nonce' );

        $phone   = sanitize_text_field( $_POST['customer_no'] ?? '' );
        $sku     = sanitize_text_field( $_POST['sku'] ?? '' );
        $user_id = get_current_user_id();

        if ( ! $phone || ! $sku ) {
            wp_send_json_error( 'Data tidak lengkap.' );
        }

        $product_id = wc_get_product_id_by_sku( $sku );
        if ( ! $product_id ) {
            wp_send_json_error( 'Produk tidak ditemukan.' );
        }

        $price = floatval( get_post_meta( $product_id, '_price', true ) );
        $balance = WPPPOB_Balances::get( $user_id );

        if ( $balance < $price ) {
            wp_send_json_error( 'Saldo tidak cukup.' );
        }

        // Buat order
        $order = wc_create_order( [ 'customer_id' => $user_id ] );
        $order->add_product( wc_get_product( $product_id ), 1 );
        $order->set_total( $price );
        $order->update_status( 'processing' );
        $order->update_meta_data( '_wppob_customer_no', $phone );
        $order->save();

        // Kurangi saldo
        WPPPOB_Balances::deduct( $user_id, $price );

        wp_send_json_success( [
            'message' => 'Pesanan berhasil dibuat.',
            'redirect' => $order->get_checkout_order_received_url()
        ] );
    }

    public function sync_products() {
        check_ajax_referer( 'wppob_admin', 'nonce' );
        $products = new WPPPOB_Products();
        $products->sync_from_api();
        wp_send_json_success( 'Produk berhasil disinkronkan.' );
    }

    public function adjust_balance() {
        check_ajax_referer( 'wppob_admin', 'nonce' );
        $user_id = intval( $_POST['user_id'] ?? 0 );
        $amount  = floatval( $_POST['amount'] ?? 0 );

        if ( ! $user_id ) wp_send_json_error( 'User tidak valid.' );

        WPPPOB_Balances::add( $user_id, $amount );
        wp_send_json_success( 'Saldo diperbarui.' );
    }
}