(function() {
    'use strict';

    // Auto-detect base path from current URL
    var basePath = (function() {
        var path = window.location.pathname;
        var match = path.match(/^(.+)\/public\//);
        if (match) return match[1] + '/public';
        return '';
    })();

    var notificationContainer = null;
    var notificationTimer = null;

    function createNotificationContainer() {
        if (notificationContainer) return notificationContainer;
        notificationContainer = document.createElement('div');
        notificationContainer.id = 'notificationContainer';
        notificationContainer.style.cssText = 'position:fixed;top:24px;left:50%;transform:translateX(-50%);z-index:99999;display:flex;flex-direction:column;gap:8px;pointer-events:none;';
        document.body.appendChild(notificationContainer);
        return notificationContainer;
    }

    window.showNotification = function(type, text, timeout) {
        var container = createNotificationContainer();
        var icons = {
            success: 'fas fa-check-circle',
            error: 'fas fa-times-circle',
            warning: 'fas fa-exclamation-triangle',
            info: 'fas fa-info-circle'
        };
        var colors = {
            success: { bg: '#DCFCE7', border: '#86EFAC', text: '#166534', icon: '#16A34A' },
            error: { bg: '#FEE2E2', border: '#FCA5A5', text: '#991B1B', icon: '#DC2626' },
            warning: { bg: '#FEF3C7', border: '#FCD34D', text: '#92400E', icon: '#D97706' },
            info: { bg: '#DBEAFE', border: '#93C5FD', text: '#1E40AF', icon: '#2563EB' }
        };
        var c = colors[type] || colors.info;

        var el = document.createElement('div');
        el.style.cssText = 'display:flex;align-items:center;gap:10px;padding:14px 20px;background:' + c.bg + ';border:1px solid ' + c.border + ';color:' + c.text + ';border-radius:12px;font-size:.88rem;font-weight:600;box-shadow:0 8px 24px rgba(0,0,0,.12);pointer-events:auto;animation:notifSlideIn .3s ease;min-width:280px;direction:rtl;';
        el.innerHTML = '<i class="' + (icons[type] || icons.info) + '" style="font-size:1.1rem;color:' + c.icon + ';"></i> ' + text;
        el.addEventListener('click', function() {
            el.style.opacity = '0';
            el.style.transform = 'translateY(-10px)';
            el.style.transition = 'all .2s ease';
            setTimeout(function() { if (el.parentNode) el.remove(); }, 200);
        });

        container.appendChild(el);

        var duration = timeout || (type === 'error' ? 5000 : 3500);
        clearTimeout(notificationTimer);
        notificationTimer = setTimeout(function() {
            el.style.opacity = '0';
            el.style.transform = 'translateY(-10px)';
            el.style.transition = 'all .3s ease';
            setTimeout(function() { if (el.parentNode) el.remove(); }, 300);
        }, duration);
    };

    window.addToCart = function(productId, quantity, btnEl) {
        quantity = quantity || 1;
        var qtyInput = document.getElementById('qty');
        if (qtyInput) quantity = parseInt(qtyInput.value) || 1;

        var btn = btnEl || document.querySelector('[onclick*="addToCart(' + productId + ')"]');
        var originalHtml = '';
        if (btn) {
            originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.style.opacity = '0.7';
            btn.style.cursor = 'not-allowed';
            btn.innerHTML = '<span class="loading-spinner" style="width:16px;height:16px;border-width:2px;"></span> جاري الإضافة...';
        }

        fetch(basePath + '/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ product_id: productId, quantity: quantity })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (btn) {
                btn.disabled = false;
                btn.style.opacity = '1';
                btn.style.cursor = 'pointer';
                btn.innerHTML = originalHtml;
            }
            if (data.success) {
                updateCartBadge(data.cart_count);
                showNotification('success', 'تمت الإضافة إلى السلة بنجاح');
            } else if (data.error) {
                showNotification('error', data.error);
            }
        })
        .catch(function() {
            if (btn) {
                btn.disabled = false;
                btn.style.opacity = '1';
                btn.style.cursor = 'pointer';
                btn.innerHTML = originalHtml;
            }
            showNotification('error', 'حدث خطأ، يرجى المحاولة مرة أخرى');
        });
    };

    function updateCartBadge(count) {
        var badge = document.getElementById('cart-count-v3') || document.getElementById('cart-count');
        if (badge) {
            badge.textContent = count;
            badge.style.transform = 'scale(1.3)';
            setTimeout(function() { badge.style.transform = 'scale(1)'; }, 200);
        }
    }

    window.updateCartBadge = updateCartBadge;

    function fetchCartCount() {
        fetch(basePath + '/cart/count', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content
            }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.cart_count !== undefined) {
                updateCartBadge(data.cart_count);
            }
        })
        .catch(function() {});
    }

    fetchCartCount();

    window.addToWishlist = function(productId) {
        fetch(basePath + '/wishlist/toggle', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ product_id: productId })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                showNotification('success', 'تمت الإضافة إلى قائمة الأمنيات');
            } else if (data.error) {
                showNotification('error', data.error);
            }
        });
    };

    window.updateQuantity = function(delta) {
        var input = document.getElementById('qty');
        if (!input) return;
        var val = parseInt(input.value) + delta;
        if (val < 1) val = 1;
        if (val > 99) val = 99;
        input.value = val;
    };

    // Expose basePath for other scripts
    window.basePath = basePath;

    // Search form escape handler
    var searchForm = document.querySelector('.search-form');
    if (searchForm) {
        var searchInput = searchForm.querySelector('input');
        if (searchInput) {
            searchInput.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') this.blur();
            });
        }
    }

    // Add notification CSS animation
    var style = document.createElement('style');
    style.textContent = '@keyframes notifSlideIn{from{opacity:0;transform:translateY(-16px)}to{opacity:1;transform:translateY(0)}}';
    document.head.appendChild(style);
})();
