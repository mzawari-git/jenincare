/**
 * Virtual Store 360° Viewer with Pannellum + Cart integration
 * Works with the existing window.addToCart() function in app.js
 */

function initVirtualStore(imagePath, hotspots, connections) {
    var basePath = window.basePath || '';

    // Build Pannellum hotspot config
    var hotSpots = hotspots.map(function(h) {
        return {
            pitch: h.pitch,
            yaw: h.yaw,
            type: 'info',
            text: h.label,
            clickHandlerFunc: function() {
                showProductPopup(h);
            },
            cssClass: 'custom-hotspot'
        };
    });

    // Add navigation connections as scene hotspots
    connections.forEach(function(c) {
        if (c.to_scene_slug) {
            var navLabel = c.label_ar || 'انتقل إلى ' + c.to_scene_name;
            var navYaw = c.direction === 'left' ? -90 : c.direction === 'right' ? 90 : c.direction === 'back' ? 180 : 0;
            hotSpots.push({
                pitch: -10,
                yaw: navYaw,
                type: 'scene',
                text: '➡ ' + navLabel,
                sceneId: c.to_scene_id,
                clickHandlerFunc: function() {
                    window.location.href = basePath + '/virtual-store/scene/' + c.to_scene_slug;
                },
                cssClass: 'nav-hotspot'
            });
        }
    });

    // Initialize Pannellum viewer
    var viewer = pannellum.viewer('panorama', {
        type: 'equirectangular',
        panorama: imagePath,
        autoLoad: true,
        autoRotate: -2,
        compass: true,
        showZoomCtrl: false,
        mouseZoom: true,
        draggable: true,
        hotSpots: hotSpots,
        hotSpotDebug: false,
        northOffset: 0,
        onLoad: function() {
            viewer.setPitch(0);
            viewer.setYaw(0);
        }
    });

    // Store viewer reference
    window.__panoramaViewer = viewer;
}

var productPopup = null;
var showProduct = false;
var productPopupX = 0;
var productPopupY = 0;
var productData = { id: 0, name: '', price: '', image: '', url: '', isOnSale: false };

function showProductPopup(hotspot) {
    productData = {
        id: hotspot.product_id,
        name: hotspot.product_name,
        price: hotspot.product_price,
        image: hotspot.product_image || (window.basePath || '') + '/images/placeholder.png',
        url: (window.basePath || '') + '/product/' + hotspot.product_slug,
        isOnSale: hotspot.is_on_sale
    };

    var popup = document.getElementById('product-popup');
    if (!popup) return;

    // Get viewer center position
    var panorama = document.getElementById('panorama');
    var rect = panorama.getBoundingClientRect();
    productPopupX = Math.min(rect.width / 2 - 130, rect.width - 280);
    productPopupY = rect.height / 2 - 100;

    popup.style.display = 'block';
    popup.style.top = productPopupY + 'px';
    popup.style.left = productPopupX + 'px';

    // Update Alpine.js data
    if (window.Alpine) {
        var store = document.querySelector('[x-data="virtualStore()"]');
        if (store && store.__x) {
            store.__x.$data.showProduct = true;
            store.__x.$data.product = productData;
            store.__x.$data.productPopupX = productPopupX;
            store.__x.$data.productPopupY = productPopupY;
        }
    }
}

function addToCartFromStore(productId, event) {
    if (event) event.preventDefault();
    if (typeof window.addToCart === 'function') {
        window.addToCart(productId, 1, event ? event.currentTarget : null);
    } else {
        // Fallback: call the API directly
        fetch((window.basePath || '') + '/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content || '',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ product_id: productId, quantity: 1 })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                var badge = document.getElementById('cart-count-v3') || document.getElementById('cart-count');
                if (badge) badge.textContent = data.cart_count;
                showStoreNotification('success', '✓ تمت الإضافة إلى السلة');
            }
        });
    }
}

function closeProductPopup() {
    var popup = document.getElementById('product-popup');
    if (popup) popup.style.display = 'none';
    if (window.Alpine) {
        var store = document.querySelector('[x-data="virtualStore()"]');
        if (store && store.__x) {
            store.__x.$data.showProduct = false;
        }
    }
}

function showStoreNotification(type, message) {
    var colors = { success: '#4caf50', error: '#f44336', info: '#2196f3' };
    var bg = colors[type] || '#333';
    var el = document.createElement('div');
    el.style.cssText = 'position:fixed;bottom:80px;left:50%;transform:translateX(-50%);background:' + bg + ';color:#fff;padding:10px 24px;border-radius:30px;z-index:9999;font-size:14px;box-shadow:0 4px 15px rgba(0,0,0,0.3);animation:fadeInUp 0.3s ease';
    el.textContent = message;
    document.body.appendChild(el);
    setTimeout(function() { el.style.opacity = '0'; el.style.transition = 'opacity 0.3s'; setTimeout(function() { el.remove(); }, 300); }, 2500);
}

// Close popup on ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeProductPopup();
});

// Close popup when clicking outside
document.addEventListener('click', function(e) {
    var popup = document.getElementById('product-popup');
    if (popup && popup.style.display !== 'none' && !popup.contains(e.target) && !e.target.closest('.custom-hotspot')) {
        closeProductPopup();
    }
});

// Alpine.js component for reactive popup
document.addEventListener('alpine:init', function() {
    if (window.Alpine) {
        Alpine.data('virtualStore', function() {
            return {
                showProduct: false,
                productPopupX: 0,
                productPopupY: 0,
                product: { id: 0, name: '', price: '', image: '', url: '', isOnSale: false },
                addToCart: function(productId, event) {
                    addToCartFromStore(productId, event);
                    closeProductPopup();
                }
            };
        });
    }
});
