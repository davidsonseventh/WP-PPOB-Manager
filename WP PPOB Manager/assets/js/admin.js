(function ($) {
    'use strict';

    $(document).ready(function () {

        // --- Sync products button ---
        $('#wppob-sync-products').on('click', function (e) {
            e.preventDefault();
            const btn = $(this);

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'wppob_sync_products',
                    nonce: wppob_admin.nonce
                },
                beforeSend: function () {
                    btn.prop('disabled', true).text('Syncing...');
                },
                success: function (res) {
                    if (res.success) {
                        alert(res.data);
                        location.reload();
                    } else {
                        alert(res.data);
                    }
                },
                complete: function () {
                    btn.prop('disabled', false).text('Sync Products');
                }
            });
        });

        // --- Quick balance adjustment ---
        $('.wppob-adjust-balance').on('click', function () {
            const userId = $(this).data('user-id');
            const current = $(this).data('current');
            const amount = prompt('Masukkan jumlah (gunakan minus untuk mengurangi):', 0);

            if (amount === null) return;

            $.post(ajaxurl, {
                action: 'wppob_adjust_balance',
                user_id: userId,
                amount: amount,
                nonce: wppob_admin.nonce
            }, function (res) {
                if (res.success) {
                    location.reload();
                } else {
                    alert(res.data);
                }
            });
        });

        // --- Transaction filter ---
        $('#wppob-filter-transactions').on('change', function () {
            const status = $(this).val();
            window.location.href = updateQueryString('status', status);
        });

        function updateQueryString(key, value) {
            const url = new URL(window.location);
            if (value) {
                url.searchParams.set(key, value);
            } else {
                url.searchParams.delete(key);
            }
            return url.toString();
        }

        // --- Date picker for reports ---
        if ($.fn.datepicker) {
            $('.wppob-date-picker').datepicker({
                dateFormat: 'yy-mm-dd'
            });
        }
    });
})(jQuery);