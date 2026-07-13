import React, { Suspense, useState, useEffect, useRef, useCallback } from 'react';
import { Canvas } from '@react-three/fiber';
import Store from './Store';
import Player from './Player';
import MiniMap from './MiniMap';
import ProductPopup from './ProductPopup';
import CartOverlay from './CartOverlay';
import Crosshair from './Crosshair';
import VirtualJoystick from './VirtualJoystick';
import AIAssistant from './AIAssistant';
import { PRODUCT_SHELVES } from './data/sections';

const BASE = window.basePath || '';
const CAROUSEL_INTERVAL = 8000; // 8 seconds

export default function App() {
    const [selectedProduct, setSelectedProduct] = useState(null);
    const [currentSection, setCurrentSection] = useState('entrance');
    const [cartItems, setCartItems] = useState([]);
    const [cartCount, setCartCount] = useState(0);
    const [showMap, setShowMap] = useState(false);
    const [showCart, setShowCart] = useState(false);
    const [notification, setNotification] = useState(null);
    const [shelves, setShelves] = useState(PRODUCT_SHELVES);
    const [carouselOffset, setCarouselOffset] = useState(0);
    const allProductsRef = useRef({});
    const joystickRef = useRef({ x: 0, y: 0 });

    // Fetch products from API and build carousel
    useEffect(() => {
        fetch(BASE + '/api/store-3d/shelves')
            .then((r) => r.json())
            .then((res) => {
                const productsBySection = res.data || {};
                allProductsRef.current = productsBySection;
                mergeShelves(productsBySection, 0);
            })
            .catch(() => {});
    }, []);

    // Carousel: cycle products every CAROUSEL_INTERVAL
    useEffect(() => {
        if (Object.keys(allProductsRef.current).length === 0) return;
        const timer = setInterval(() => {
            setCarouselOffset((prev) => {
                const next = prev + 1;
                mergeShelves(allProductsRef.current, next);
                return next;
            });
        }, CAROUSEL_INTERVAL);
        return () => clearInterval(timer);
    }, []);

    function mergeShelves(productsBySection, offset) {
        const allFlat = productsBySection.all || [];
        const merged = PRODUCT_SHELVES.map((shelf) => {
            const sectionProds = productsBySection[shelf.section] || [];
            const shelfIndex = PRODUCT_SHELVES.filter((s) => s.section === shelf.section).indexOf(shelf);
            // Try section-specific products first, cycle with offset
            let product = null;
            if (sectionProds.length > 0) {
                const idx = (shelfIndex + offset) % sectionProds.length;
                product = sectionProds[idx];
            } else if (allFlat.length > 0) {
                // Fall back to flat all-products list
                const globalIdx = (shelfIndex + offset) % allFlat.length;
                product = allFlat[globalIdx];
            }
            return { ...shelf, product };
        });
        setShelves(merged);
    }

    useEffect(() => {
        fetch(BASE + '/cart/count')
            .then((r) => r.json())
            .then((data) => setCartCount(data.count || 0))
            .catch(() => {});
    }, []);

    const showNotification = useCallback((type, message) => {
        setNotification({ type, message });
        setTimeout(() => setNotification(null), 2500);
    }, []);

    const handleProductClick = useCallback((shelf) => {
        setSelectedProduct(shelf.product || shelf);
    }, []);

    const handleAddToCart = useCallback((item) => {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
        const productId = item.product ? item.product.id : (item.productId || item.id);
        const name = item.product?.name_ar || item.productName || 'منتج';
        const price = item.product?.price ?? item.price ?? 0;
        const image = item.product?.main_image_url || item.product?.image || item.image || '';

        fetch(BASE + '/cart/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ product_id: productId, quantity: 1 }),
        })
            .then((r) => r.json())
            .then((data) => {
                if (data.success) {
                    const badge = document.getElementById('cart-count-v3') || document.getElementById('cart-count');
                    if (badge) badge.textContent = data.cart_count;
                    setCartCount(data.cart_count);
                    setCartItems((prev) => [
                        ...prev,
                        { id: Date.now(), product: { name_ar: name, price, main_image_url: image }, quantity: 1 },
                    ]);
                    showNotification('success', '✓ تمت الإضافة إلى السلة');
                }
            })
            .catch(() => {
                showNotification('error', '✗ فشلت الإضافة');
            });

        setSelectedProduct(null);
    }, [showNotification]);

    const handleUpdateQuantity = useCallback((item, newQty) => {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
        const productId = item.product?.id || item.id;
        fetch(BASE + '/cart/update', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ product_id: productId, quantity: newQty }),
        }).catch(() => {});
        setCartItems((prev) =>
            prev.map((i) => (i.id === item.id ? { ...i, quantity: newQty } : i))
        );
    }, []);

    const handleRemoveItem = useCallback((item) => {
        const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
        const productId = item.product?.id || item.id;
        fetch(BASE + '/cart/remove', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ product_id: productId }),
        })
            .then((r) => r.json())
            .then((data) => {
                if (data.success) setCartCount(data.cart_count || 0);
            })
            .catch(() => {});
        setCartItems((prev) => prev.filter((i) => i.id !== item.id));
    }, []);

    const handleOpenCart = useCallback(() => {
        document.exitPointerLock();
        setShowCart(true);
    }, []);

    const handleCloseCart = useCallback(() => {
        setShowCart(false);
    }, []);

    const handleSectionChange = useCallback((id) => {
        setCurrentSection(id);
    }, []);

    const handleAIClick = useCallback(() => {
        const sections = document.querySelectorAll('.section-sign');
        if (sections.length > 0) sections[0].scrollIntoView({ behavior: 'smooth' });
    }, []);

    return (
        <div className="store-3d-wrapper">
            <Canvas
                camera={{ fov: 75, near: 0.1, far: 100, position: [0, 1.6, 2.0] }}
                shadows
                gl={{ antialias: true }}
                className="store-3d-canvas"
                onCreated={() => {
                    const el = document.getElementById('store-3d-loading');
                    if (el) el.style.display = 'none';
                }}
            >
                <Suspense fallback={null}>
                    <Store
                        shelves={shelves}
                        onProductClick={handleProductClick}
                        onSectionChange={handleSectionChange}
                        currentSection={currentSection}
                    />
                    <Player joystickRef={joystickRef} />
                </Suspense>
            </Canvas>

            <div className="store-ui">
                <div className="store-header">
                    <h3 className="store-title">
                        <span className="store-title-icon">◆</span>
                        جولة افتراضية
                    </h3>
                    <div className="store-controls">
                        <button className="store-btn" onClick={() => setShowMap(!showMap)} title="الخريطة">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        </button>
                        <button className="store-btn store-btn-cart" onClick={handleOpenCart} title="السلة">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
                            {cartCount > 0 && <span className="cart-badge">{cartCount}</span>}
                        </button>
                        <button className="store-btn" onClick={() => window.location.href = BASE + '/shop'} title="المتجر العادي">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                        </button>
                    </div>
                </div>

                <div className="section-label">
                    <span className="section-badge" style={{ backgroundColor: getSectionColor(currentSection) }}>
                        {getSectionName(currentSection)}
                    </span>
                </div>

                <Crosshair />

                <div className="store-controls-hint">
                    <span>WASD للحركة | الماوس للتوجيه</span>
                </div>
            </div>

            <VirtualJoystick dirRef={joystickRef} />

            {showMap && (
                <MiniMap currentSection={currentSection} onSectionClick={(id) => { setCurrentSection(id); setShowMap(false); }} />
            )}

            {selectedProduct && (
                <ProductPopup
                    product={selectedProduct.product || selectedProduct}
                    onClose={() => setSelectedProduct(null)}
                    onAddToCart={() => handleAddToCart(selectedProduct)}
                />
            )}

            {showCart && (
                <CartOverlay
                    items={cartItems}
                    onClose={handleCloseCart}
                    onUpdateQuantity={handleUpdateQuantity}
                    onRemoveItem={handleRemoveItem}
                />
            )}

            <AIAssistant onNavigate={handleAIClick} currentSection={currentSection} onSectionChange={handleSectionChange} />

            <div className={`notification-container ${notification ? 'visible' : ''}`}>
                {notification && (
                    <div className={`notification notification-${notification.type}`}>{notification.message}</div>
                )}
            </div>
        </div>
    );
}

function getSectionColor(id) {
    const colors = {
        entrance: '#8B5CF6', skincare: '#EC4899', devices: '#3B82F6',
        creams: '#F59E0B', salon: '#10B981', offers: '#EF4444',
    };
    return colors[id] || '#C9A96E';
}

function getSectionName(id) {
    const names = {
        entrance: 'المدخل', skincare: 'العناية بالبشرة', devices: 'أجهزة التجميل',
        creams: 'الكريمات والسيرومات', salon: 'تجهيز الصالونات', offers: 'العروض الخاصة',
    };
    return names[id] || id;
}
