import React, { useState, useEffect } from 'react';

const BASE = window.basePath || '';

export default function CartOverlay({ items, onClose, onUpdateQuantity, onRemoveItem }) {
    const [localItems, setLocalItems] = useState(items);
    const [loading, setLoading] = useState(false);

    useEffect(() => {
        setLocalItems(items);
    }, [items]);

    useEffect(() => {
        document.exitPointerLock();
        const handler = (e) => {
            if (e.key === 'Escape') onClose();
        };
        document.addEventListener('keydown', handler);
        return () => document.removeEventListener('keydown', handler);
    }, [onClose]);

    const subtotal = localItems.reduce((sum, item) => {
        const price = item.product?.price ?? item.price ?? 0;
        return sum + price * (item.quantity || 1);
    }, 0);

    const handleQtyChange = (item, delta) => {
        const newQty = Math.max(1, (item.quantity || 1) + delta);
        onUpdateQuantity(item, newQty);
        setLocalItems((prev) =>
            prev.map((i) => (i.id === item.id ? { ...i, quantity: newQty } : i))
        );
    };

    const handleRemove = (item) => {
        onRemoveItem(item);
        setLocalItems((prev) => prev.filter((i) => i.id !== item.id));
    };

    return (
        <div className="cart-overlay" onClick={onClose}>
            <div className="cart-modal" onClick={(e) => e.stopPropagation()}>
                <div className="cart-header">
                    <h2 className="cart-title">سلة التسوق</h2>
                    <button className="cart-close" onClick={onClose}>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
                    </button>
                </div>

                {localItems.length === 0 ? (
                    <div className="cart-empty">
                        <div className="cart-empty-icon">
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#C9A96E" strokeWidth="1.5"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 002 1.61h9.72a2 2 0 002-1.61L23 6H6"/></svg>
                        </div>
                        <p className="cart-empty-text">سلتك فارغة</p>
                        <p className="cart-empty-hint">تجول في المتجر وأضف المنتجات</p>
                    </div>
                ) : (
                    <>
                        <div className="cart-items">
                            {localItems.map((item, idx) => {
                                const product = item.product || item;
                                const name = product.name_ar || product.productName || 'منتج';
                                const price = product.price ?? 0;
                                const image = product.main_image_url || product.image || '';
                                const qty = item.quantity || 1;

                                return (
                                    <div key={item.id || idx} className="cart-item">
                                        <div className="cart-item-image">
                                            {image ? (
                                                <img src={image} alt={name} onError={(e) => { e.target.style.display = 'none'; }} />
                                            ) : (
                                                <div className="cart-item-placeholder" style={{ backgroundColor: '#C9A96E' }}>JD</div>
                                            )}
                                        </div>
                                        <div className="cart-item-info">
                                            <div className="cart-item-name">{name}</div>
                                            <div className="cart-item-price">{price.toLocaleString()} ر.س</div>
                                        </div>
                                        <div className="cart-item-qty">
                                            <button className="qty-btn" onClick={() => handleQtyChange(item, -1)}>−</button>
                                            <span className="qty-value">{qty}</span>
                                            <button className="qty-btn" onClick={() => handleQtyChange(item, 1)}>+</button>
                                        </div>
                                        <div className="cart-item-total">{(price * qty).toLocaleString()} ر.س</div>
                                        <button className="cart-item-remove" onClick={() => handleRemove(item)}>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2"><path d="M3 6h18M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a2 2 0 012-2h4a2 2 0 012 2v2"/></svg>
                                        </button>
                                    </div>
                                );
                            })}
                        </div>

                        <div className="cart-footer">
                            <div className="cart-subtotal">
                                <span>المجموع</span>
                                <span className="cart-subtotal-value">{subtotal.toLocaleString()} ر.س</span>
                            </div>
                            <a href={`${BASE}/cart`} className="cart-checkout-btn">
                                إتمام الطلب
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" style={{ marginRight: 8 }}><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                            </a>
                            <button className="cart-continue-btn" onClick={onClose}>
                                متابعة التسوق
                            </button>
                        </div>
                    </>
                )}
            </div>
        </div>
    );
}
