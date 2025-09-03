<?php
defined( 'ABSPATH' ) || exit;

function wppob_format_rp( $number ) {
    return 'Rp ' . number_format( $number, 0, ',', '.' );
}

function wppob_get_transaction_status_label( $status ) {
    $labels = [
        'pending' => 'Menunggu',
        'success' => 'Sukses',
        'failed'  => 'Gagal',
        'refunded'=> 'Dikembalikan',
    ];
    return $labels[ $status ] ?? $status;
}