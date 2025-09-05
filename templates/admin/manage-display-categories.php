<?php
defined('ABSPATH') || exit;

global $wpdb;
$table_name = $wpdb->prefix . 'wppob_display_categories';

// Logika untuk Hapus Kategori
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) && isset($_GET['_wpnonce'])) {
    if (wp_verify_nonce($_GET['_wpnonce'], 'delete_category_' . $_GET['id'])) {
        $wpdb->delete($table_name, ['id' => intval($_GET['id'])], ['%d']);
        echo '<div id="message" class="updated notice is-dismissible"><p>Kategori berhasil dihapus.</p></div>';
    }
}

$edit_mode = isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id']);
$item_to_edit = null;
if ($edit_mode) { $item_id = intval($_GET['id']); $item_to_edit = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$table_name} WHERE id = %d", $item_id)); }
$categories = $wpdb->get_results("SELECT id, name, parent_id FROM {$table_name} ORDER BY name ASC");
$ppob_products = wc_get_products(['limit' => -1, 'meta_key' => '_wppob_base_price', 'orderby' => 'name', 'order' => 'ASC']);
$selected_products = !empty($item_to_edit->assigned_products) ? json_decode($item_to_edit->assigned_products) : [];
?>
<div class="wrap">
    <h1><?php echo $edit_mode ? 'Edit Kategori Tampilan' : 'Tambah Kategori Tampilan'; ?></h1>
    
    <?php if (isset($_GET['message']) && $_GET['message'] === 'success') : ?>
        <div id="message" class="updated notice is-dismissible"><p>Kategori berhasil disimpan.</p></div>
    <?php endif; ?>

    <div id="col-container" class="wp-clearfix">
        <div id="col-left">
            <div class="col-wrap">
                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                    <input type="hidden" name="action" value="wppob_save_category">
                    <?php if ($edit_mode) { echo '<input type="hidden" name="category_id" value="' . esc_attr($item_to_edit->id) . '">'; } ?>
                    <?php wp_nonce_field('wppob_save_category_nonce', '_wpnonce'); ?>

                    <h2>Pengaturan Dasar</h2>
                    <div class="form-field"><label for="cat_name">Nama</label><input name="cat_name" id="cat_name" type="text" value="<?php echo esc_attr($item_to_edit->name ?? ''); ?>" required></div>
                    <div class="form-field">
                        <label for="cat_parent">Induk Kategori</label>
                        <select name="cat_parent" id="cat_parent">
                            <option value="0">— Kategori Utama —</option>
                            <?php foreach ($categories as $cat) { if ($cat->id !== ($item_to_edit->id ?? 0)) { echo '<option value="' . esc_attr($cat->id) . '" ' . selected($item_to_edit->parent_id ?? 0, $cat->id, false) . '>' . esc_html($cat->name) . '</option>'; } } ?>
                        </select>
                    </div>

                    <h2>Pengaturan Tampilan</h2>
                    <div class="form-field">
                        <label>Gambar</label>
                        <div class="wppob-image-uploader">
                            <?php $image_url = ($item_to_edit->image_id ?? 0) ? wp_get_attachment_image_url($item_to_edit->image_id, 'thumbnail') : ''; ?>
                            <img src="<?php echo esc_url($image_url); ?>" class="wppob-image-preview" style="<?php echo empty($image_url) ? 'display:none;' : ''; ?>">
                            <input type="hidden" name="cat_image_id" class="wppob-image-id" value="<?php echo esc_attr($item_to_edit->image_id ?? 0); ?>">
                            <button type="button" class="button wppob-upload-btn" style="<?php echo !empty($image_url) ? 'display:none;' : ''; ?>">Pilih Gambar</button>
                            <button type="button" class="button wppob-remove-btn" style="<?php echo empty($image_url) ? 'display:none;' : ''; ?>">Hapus Gambar</button>
                        </div>
                    </div>
                     <div class="form-field">
                        <label for="display_style">Gaya Tampilan</label>
                        <select name="display_style" id="display_style">
                            <option value="image_text" <?php selected($item_to_edit->display_style ?? 'image_text', 'image_text'); ?>>Gambar & Teks</option>
                            <option value="image_only" <?php selected($item_to_edit->display_style ?? '', 'image_only'); ?>>Gambar Saja</option>
                            <option value="text_only" <?php selected($item_to_edit->display_style ?? '', 'text_only'); ?>>Teks Saja</option>
                        </select>
                    </div>
                    <div class="form-field">
                        <label for="image_size_px">Ukuran Gambar (px)</label><input type="number" name="image_size_px" id="image_size_px" value="<?php echo esc_attr($item_to_edit->image_size_px ?? 80); ?>" min="20" max="200">
                    </div>
                    <div class="form-field">
                        <label for="border_radius">Radius Border (%)</label><input type="number" name="border_radius" id="border_radius" value="<?php echo esc_attr($item_to_edit->border_radius ?? 15); ?>" min="0" max="50">
                    </div>
                     <div class="form-field">
                        <label for="display_mode">Tampilan Sub-item</label>
                        <select name="display_mode" id="display_mode">
                            <option value="grid" <?php selected($item_to_edit->display_mode ?? 'grid', 'grid'); ?>>Grid</option>
                            <option value="list" <?php selected($item_to_edit->display_mode ?? '', 'list'); ?>>Daftar (List)</option>
                        </select>
                    </div>

                    <h2>Konten Kategori</h2>
                    <div class="form-field">
                        <label for="assigned_products">Pilih Produk</label>
                        <select name="assigned_products[]" id="assigned_products" multiple="multiple" style="width:100%; height: 250px;">
                            <?php foreach($ppob_products as $product) { echo '<option value="' . esc_attr($product->get_id()) . '" ' . (in_array($product->get_id(), $selected_products) ? 'selected' : '') . '>' . esc_html($product->get_name()) . '</option>'; } ?>
                        </select>
                    </div>

                    <?php submit_button($edit_mode ? 'Perbarui Kategori' : 'Tambah Kategori'); ?>
                </form>
            </div>
        </div>
        <div id="col-right">
            <div class="col-wrap">
                <h2>Daftar Kategori</h2>
                <table class="wp-list-table widefat fixed striped">
                     <thead><tr><th>Gambar</th><th>Nama</th></tr></thead>
                     <tbody>
                        <?php if (!empty($categories)) { 
                            foreach ($categories as $cat) {
                                $delete_nonce = wp_create_nonce('delete_category_' . $cat->id);
                                $delete_url = '?page=wppob-display-categories&action=delete&id=' . $cat->id . '&_wpnonce=' . $delete_nonce;
                                $image = $cat->image_id ? wp_get_attachment_image($cat->image_id, 'thumbnail', false, ['style' => 'width:40px;height:40px;object-fit:cover;border-radius:4px;']) : '';
                                echo '<tr><td>' . $image . '</td><td>' . ($cat->parent_id ? '— ' : '') . esc_html($cat->name) . '<div class="row-actions"><span class="edit"><a href="?page=wppob-display-categories&action=edit&id=' . $cat->id . '">Edit</a> | </span><span class="trash"><a href="' . esc_url($delete_url) . '" onclick="return confirm(\'Anda yakin ingin menghapus kategori ini?\')" style="color:#a00;">Hapus</a></span></div></td></tr>';
                            } 
                        } else { 
                            echo '<tr><td colspan="2">Belum ada kategori.</td></tr>'; 
                        } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>