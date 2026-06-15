import React, { Suspense, useState, useEffect } from 'react';
import { Canvas } from '@react-three/fiber';
import Store from './Store';
import Player from './Player';
import MiniMap from './MiniMap';
import ProductPopup from './ProductPopup';
import AIAssistant from './AIAssistant';
import { PRODUCT_SHELVES } from './data/sections';

const BASE = window.basePath || '';

export default function App() {
    const [selectedProduct, setSelectedProduct] = useState(null);
    const [currentSection, setCurrentSection] = useState('entrance');
    const [cartItems, setCartItems] = useState([]);
    const [showMap, setShowMap] = useState(false);
    const [notification, setNotification] = useState(null);
    const [shelves, setShelves] = useState(PRODUCT_SHELVES);

    useEffect(() => {
        fetch(BASE + '/api/store-3d/shelves')
            .then((r) => r.json())
            .then((res) => {
                const productsBySection = res.data || {};
                const merged = PRODUCT_SHELVES.map((shelf) => {
                    const products = productsBySection[shelf.section] || [];
                    const idx = PRODUCT_SHELVES.filter((s) => s.section === shelf.section).indexOf(shelf);
                    const product = products[idx] || null;
                    return { ...shelf, product };
                });
                setShelves(merged);
            })
            .catch(() => {});
    }, []);

    const handleProductClick = (shelf) => {
        setSelectedProduct(shelf.product || shelf);
    };

    const handleAddToCart = (item) => {
        const basePath = window.basePath || '';
        const csrf = document.querySelector('meta[name=csrf-token]')?.content || '';
        const productId = item.product ? item.product.id : (item.productId || item.id);

        fetch(basePath + '/cart/add', {
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
                    setCartItems((prev) => [...prev, item]);
                    setNotification({ type: 'success', message: '✓ تمت الإضافة إلى السلة' });
                    setTimeout(() => setNotification(null), 2500);
                }
            })
            .catch(() => {
                setNotification({ type: 'error', message: '✗ فشلت الإضافة' });
                setTimeout(() => setNotification(null), 2500);
            });

        setSelectedProduct(null);
    };

    const handleSectionChange = (sectionId) => {
        setCurrentSection(sectionId);
    };

    const handleAIClick = () => {
        const sections = document.querySelectorAll('.section-sign');
        if (sections.length > 0) {
            sections[0].scrollIntoView({ behavior: 'smooth' });
        }
    };

    return (
        <div style={{ width: '100vw', height: '100vh', position: 'relative', overflow: 'hidden' }}>
            <Canvas
                camera={{ fov: 75, near: 0.1, far: 100, position: [0, 1.6, 0.3] }}
                shadows
                gl={{ antialias: true }}
                style={{ width: '100%', height: '100%' }}
            >
                <Suspense fallback={null}>
                    <Store
                        shelves={shelves}
                        onProductClick={handleProductClick}
                        onSectionChange={handleSectionChange}
                        currentSection={currentSection}
                    />
                    <Player />
                </Suspense>
            </Canvas>

            <div className="store-3d-ui">
                <div className="store-header">
                    <h3 className="store-title">جولة افتراضية</h3>
                    <div className="store-controls">
                        <button className="store-btn" onClick={() => setShowMap(!showMap)} title="الخريطة">
                            🗺️
                        </button>
                        <button className="store-btn" onClick={() => window.location.href = window.basePath + '/shop'} title="المتجر العادي">
                            🛍️
                        </button>
                    </div>
                </div>

                <div className="section-label">
                    <span className="section-badge" style={{ backgroundColor: getSectionColor(currentSection) }}>
                        {getSectionName(currentSection)}
                    </span>
                </div>

                <div className="store-controls-hint">
                    <span>WASD للحركة | الماوس للتوجيه</span>
                </div>

                {showMap && (
                    <MiniMap
                        currentSection={currentSection}
                        onSectionClick={(id) => {
                            setCurrentSection(id);
                            setShowMap(false);
                        }}
                    />
                )}

                {selectedProduct && (
                    <ProductPopup
                        product={selectedProduct.product || selectedProduct}
                        onClose={() => setSelectedProduct(null)}
                        onAddToCart={() => handleAddToCart(selectedProduct)}
                    />
                )}

                <AIAssistant
                    onNavigate={handleAIClick}
                    currentSection={currentSection}
                    onSectionChange={handleSectionChange}
                />
            </div>

            <div className={`notification-container ${notification ? 'visible' : ''}`}>
                {notification && (
                    <div className={`notification notification-${notification.type}`}>
                        {notification.message}
                    </div>
                )}
            </div>
        </div>
    );
}

function getSectionColor(id) {
    const colors = {
        entrance: '#8B5CF6',
        skincare: '#EC4899',
        devices: '#3B82F6',
        creams: '#F59E0B',
        salon: '#10B981',
        offers: '#EF4444',
    };
    return colors[id] || '#666';
}

function getSectionName(id) {
    const names = {
        entrance: 'المدخل',
        skincare: 'العناية بالبشرة',
        devices: 'أجهزة التجميل',
        creams: 'الكريمات والسيرومات',
        salon: 'تجهيز الصالونات',
        offers: 'العروض الخاصة',
    };
    return names[id] || id;
}
