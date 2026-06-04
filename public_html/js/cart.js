(function() {
    'use strict';

    document.querySelectorAll('.qty-minus, .qty-plus').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var container = this.closest('.qty-control');
            if (!container) return;
            var input = container.querySelector('.qty-input');
            if (!input) return;
            var val = parseInt(input.value) || 1;
            if (this.classList.contains('qty-minus')) {
                if (val > 1) input.value = val - 1;
            } else {
                input.value = val + 1;
            }
            input.dispatchEvent(new Event('change'));
        });
    });

    document.querySelectorAll('.qty-input').forEach(function(input) {
        input.addEventListener('change', function() {
            var val = parseInt(this.value);
            if (isNaN(val) || val < 1) this.value = 1;
            var form = this.closest('form');
            if (form) form.submit();
        });
    });
})();
