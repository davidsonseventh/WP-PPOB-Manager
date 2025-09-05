(function ($) {
    'use strict';
    $(document).ready(function () {
        // ... (Kode untuk sinkronisasi produk dan uploader gambar tetap sama) ...

        // --- Logika untuk Manajemen Pengguna ---
        const userManager = $('#wppob-user-manager');
        if (userManager.length) {
            let userSearchTimeout;
            
            // Event saat mengetik di kolom pencarian
            $('#wppob-user-search-input').on('keyup', function() {
                const query = $(this).val();
                const resultsList = $('#wppob-user-search-results');
                clearTimeout(userSearchTimeout);

                if (query.length < 3) {
                    resultsList.empty().hide();
                    return;
                }

                userSearchTimeout = setTimeout(function() {
                    resultsList.html('<li>Mencari...</li>').show();
                    $.post(wppob_admin_params.ajax_url, {
                        action: 'wppob_user_search',
                        nonce: wppob_admin_params.nonce,
                        search: query
                    }).done(function(response) {
                        resultsList.empty();
                        if(response.success && response.data.length > 0) {
                            response.data.forEach(function(user) {
                                resultsList.append(`<li data-userid="${user.id}" data-username="${user.text.split(' (')[0]}" data-email="${user.text.split(' (')[1].slice(0, -1)}" data-balance="${user.balance}">${user.text}</li>`);
                            });
                        } else {
                            resultsList.html('<li>Tidak ada pengguna ditemukan.</li>');
                        }
                    });
                }, 500);
            });

            // Event saat hasil pencarian diklik
            $('#wppob-user-search-results').on('click', 'li', function() {
                const user = $(this);
                if (!user.data('userid')) return;

                $('#wppob-detail-username').text(user.data('username'));
                $('#wppob-detail-email').text(user.data('email'));
                $('#wppob-detail-balance').text(user.data('balance'));
                $('#wppob-adjust-user-id').val(user.data('userid'));
                
                $('#wppob-user-placeholder').hide();
                $('#wppob-user-details-wrapper').show();
                $(this).closest('ul').empty().hide();
            });
        }
    });
})(jQuery);