(function ($) {
    'use strict';

    $(document).ready(function () {

        // --- Handle operator & nominal dropdown ---
        $('#wppob-phone').on('keyup change', detectOperator);
        $('#wppob-operator').on('change', loadNominals);

        function detectOperator() {
            const phone = $('#wppob-phone').val().replace(/\D/g, '');
            let operator = '';

            if (/^08(1[123]|5[12345678]|52|53|21|22|23)/.test(phone)) {
                operator = 'telkomsel';
            } else if (/^08(17|18|19|59|77|78)/.test(phone)) {
                operator = 'xl';
            } else if (/^08(14|15|16|55|56|57|58)/.test(phone)) {
                operator = 'indosat';
            } else if (/^08(3[123]|38)/.test(phone)) {
                operator = 'axis';
            } else if (/^08(9[56]|95|98|99)/.test(phone)) {
                operator = 'tri';
            } else if (/^08(81|82|83|84|85|88)/.test(phone)) {
                operator = 'smartfren';
            }

            if (operator) {
                $('#wppob-operator').val(operator).trigger('change');
            }
        }

        function loadNominals() {
            const operator = $('#wppob-operator').val();
            if (!operator) return;

            $.ajax({
                url: wppob.ajax_url,
                type: 'POST',
                data: {
                    action: 'wppob_get_nominals',
                    operator: operator,
                    nonce: wppob.nonce
                },
                beforeSend: function () {
                    $('#wppob-nominal').html('<option value="">Loading...</option>');
                },
                success: function (res) {
                    if (res.success) {
                        let options = '<option value="">Pilih Nominal</option>';
                        res.data.forEach(function (item) {
                            options += `<option value="${item.code}" data-price="${item.price}">${item.name} - Rp ${item.price}</option>`;
                        });
                        $('#wppob-nominal').html(options);
                    } else {
                        $('#wppob-nominal').html('<option value="">' + res.data + '</option>');
                    }
                }
            });
        }

        // --- Update price on nominal change ---
        $('#wppob-nominal').on('change', function () {
            const selected = $(this).find('option:selected');
            const price = selected.data('price');
            $('#wppob-price').val(price ? 'Rp ' + price.toLocaleString('id-ID') : '');
        });

        // --- Submit form ---
        $('.wppob-form').on('submit', function (e) {
            e.preventDefault();
            const form = $(this);
            const formData = new FormData(form[0]);
            formData.append('action', 'wppob_submit_order');
            formData.append('nonce', wppob.nonce);

            $.ajax({
                url: wppob.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function () {
                    form.find('.wppob-btn').prop('disabled', true).text('Memproses...');
                },
                success: function (res) {
                    if (res.success) {
                        showMessage(res.data.message, 'success');
                        if (res.data.redirect) {
                            window.location.href = res.data.redirect;
                        }
                    } else {
                        showMessage(res.data, 'error');
                    }
                },
                complete: function () {
                    form.find('.wppob-btn').prop('disabled', false).text('Beli Sekarang');
                }
            });
        });

        function showMessage(msg, type = 'error') {
            const $msg = $('<div class="wppob-message ' + type + '">' + msg + '</div>');
            $('.wppob-form-container').prepend($msg);
            setTimeout(() => $msg.fadeOut(() => $msg.remove()), 4000);
        }
    });
})(jQuery);