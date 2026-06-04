(function() {
    'use strict';

    var sameAsShipping = document.getElementById('sameAsShipping');
    if (sameAsShipping) {
        sameAsShipping.addEventListener('change', function() {
            var billingFields = document.querySelectorAll('.billing-field');
            billingFields.forEach(function(field) {
                var inputs = field.querySelectorAll('input, textarea, select');
                inputs.forEach(function(input) {
                    input.disabled = this.checked;
                }, this);
                field.style.opacity = this.checked ? '0.5' : '1';
            }, this);
        });
    }

    var paymentRadios = document.querySelectorAll('input[name="payment_method"]');
    paymentRadios.forEach(function(radio) {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.payment-option').forEach(function(opt) {
                opt.style.borderColor = 'var(--border-color)';
                opt.style.background = '#fff';
            });
            var parent = this.closest('.payment-option');
            if (parent) {
                parent.style.borderColor = 'var(--primary-color)';
                parent.style.background = 'rgba(212,175,55,0.05)';
            }
        });
    });
})();
