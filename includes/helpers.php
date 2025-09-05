<?php
defined('ABSPATH') || exit;

/**
 * Memformat angka menjadi format mata uang Rupiah (Rp).
 *
 * @param float|int $number Angka yang akan diformat.
 * @return string String dalam format Rupiah.
 */
if (!function_exists('wppob_format_rp')) {
    function wppob_format_rp($number) {
        return 'Rp ' . number_format(is_numeric($number) ? $number : 0, 0, ',', '.');
    }
}

/**
 * Mengembalikan label yang mudah dibaca untuk status transaksi.
 *
 * @param string $status Status internal (e.g., 'pending', 'success').
 * @return string Label yang sudah diterjemahkan.
 */
if (!function_exists('wppob_get_status_label')) {
    function wppob_get_status_label($status) {
        $labels = [
            'pending'    => __('Pending', 'wp-ppob'),
            'processing' => __('Diproses', 'wp-ppob'),
            'success'    => __('Sukses', 'wp-ppob'),
            'failed'     => __('Gagal', 'wp-ppob'),
            'refunded'   => __('Dana Dikembalikan', 'wp-ppob'),
        ];
        return $labels[strtolower($status)] ?? ucfirst($status);
    }
}

/**
 * Mencari ID lampiran (attachment) di Media Library berdasarkan nama file.
 * Ini adalah cara yang andal untuk menghindari duplikasi unggahan.
 *
 * @param string $filename Nama file (e.g., 'telkomsel.jpg').
 * @return int|null ID lampiran jika ditemukan, jika tidak null.
 */
if (!function_exists('wppob_get_attachment_id_by_filename')) {
    function wppob_get_attachment_id_by_filename($filename) {
        global $wpdb;
        $attachment_id = $wpdb->get_var($wpdb->prepare(
            "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s",
            '%/' . $filename
        ));
        return $attachment_id ? (int) $attachment_id : null;
    }
}

/**
 * Mengatur featured image untuk sebuah produk dari file lokal dengan logika pencocokan fleksibel.
 *
 * @param int    $product_id ID produk WooCommerce.
 * @param string $brand      Nama merek dari API (e.g., 'TELKOMSEL', 'GO-PAY').
 */
if (!function_exists('wppob_set_product_image_from_brand')) {
    function wppob_set_product_image_from_brand($product_id, $brand) {
        if (has_post_thumbnail($product_id)) {
            return;
        }

        $brand_key = strtolower(preg_replace("/[^a-zA-Z0-9]+/", "", $brand));
        $image_dir = WPPPOB_PLUGIN_DIR . 'assets/images/';
        
        $image_path = null;
        if (file_exists($image_dir . $brand_key . '.jpg')) {
            $image_path = $image_dir . $brand_key . '.jpg';
        } elseif (file_exists($image_dir . $brand_key . '.png')) {
            $image_path = $image_dir . $brand_key . '.png';
        } else {
            return; // Hentikan jika file gambar tidak ada.
        }

        $filename = basename($image_path);
        $attachment_id = wppob_get_attachment_id_by_filename($filename);

        // Jika gambar belum ada di Media Library, unggah sekarang.
        if (!$attachment_id) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');

            $file_array = ['name' => $filename, 'tmp_name' => $image_path];
            $attachment_id = media_handle_sideload($file_array, $product_id, $brand . ' Logo');

            if (is_wp_error($attachment_id)) {
                return; // Gagal mengunggah.
            }
        }
        
        // Pasang gambar yang sudah ada atau yang baru diunggah ke produk.
        if ($attachment_id) {
            set_post_thumbnail($product_id, $attachment_id);
        }
    }
}