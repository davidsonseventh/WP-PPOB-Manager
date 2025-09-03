<?php
defined( 'ABSPATH' ) || exit;

class WPPPOB_Balances {
    public function __construct() {
        add_action( 'woocommerce_order_status_completed', [ $this, 'process_topup' ] );
    }

    public static function get( $user_id ) {
        global $wpdb;
        return floatval( $wpdb->get_var(
            $wpdb->prepare( "SELECT balance FROM {$wpdb->prefix}wppob_balances WHERE user_id = %d", $user_id )
        ) );
    }

    public static function add( $user_id, $amount ) {
        global $wpdb;
        $current = self::get( $user_id );
        $wpdb->replace(
            "{$wpdb->prefix}wppob_balances",
            [
                'user_id'  => $user_id,
                'balance'  => $current + floatval( $amount ),
                'updated_at' => current_time( 'mysql' )
            ]
        );
    }

    public static function deduct( $user_id, $amount ) {
        return self::add( $user_id, -abs( $amount ) );
    }

    private function process_topup( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) return;

        foreach ( $order->get_items() as $item ) {
            if ( strpos( $item->get_product()->get_sku(), 'topup-' ) === 0 ) {
                $amount = floatval( str_replace( 'topup-', '', $item->get_product()->get_sku() ) );
                self::add( $order->get_customer_id(), $amount );
                break;
            }
        }
    }
}