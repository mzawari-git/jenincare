import React from 'react';

const BASE = window.basePath || '';

export default function ProductPopup({ product, onClose, onAddToCart }) {
    if (!product) return null;

    const name = product.name_ar || product.productName || 'منتج';
    const price = product.price ?? 0;
    const rating = product.rating ?? 0;
    const slug = product.slug || '';
    const image = product.image || '';
    const reviewsCount = product.reviews_count ?? 0;

    return (
        <div className="product-popup-overlay" onClick={onClose}>
            <div className="product-popup" onClick={(e) => e.stopPropagation()}>
                <button className="popup-close" onClick={onClose}>
                    ✕
                </button>

                <div className="popup-header">
                    <div className="popup-product-icon">
                        {image ? (
                            <img
                                src={image}
                                alt={name}
                                className="popup-product-img"
                                onError={(e) => { e.target.style.display = 'none'; }}
                            />
                        ) : (
                            <div className="popup-icon-box" style={{ backgroundColor: '#8B5CF6' }}>
                                <span>JD</span>
                            </div>
                        )}
                    </div>
                    <div className="popup-info">
                        <h4 className="popup-title">{name}</h4>
                        <div className="popup-rating">
                            {'★'.repeat(Math.floor(rating))}
                            {'☆'.repeat(5 - Math.floor(rating))}
                            <span className="popup-rating-value"> ({rating})</span>
                            {reviewsCount > 0 && (
                                <span className="popup-reviews-count"> | {reviewsCount} تقييم</span>
                            )}
                        </div>
                    </div>
                </div>

                <div className="popup-details">
                    <div className="popup-price">
                        <span className="popup-price-value">{price}</span>
                        <span className="popup-price-currency"> ر.س</span>
                    </div>
                    <p className="popup-description">
                        منتج عالي الجودة من متجر جنين للتجميل. يتميز بفعالية مثبتة وجودة ممتازة.
                    </p>
                    <div className="popup-features">
                        <span className="popup-badge">✓ جودة عالية</span>
                        <span className="popup-badge">✓ توصيل سريع</span>
                        <span className="popup-badge">✓ ضمان</span>
                    </div>
                </div>

                <div className="popup-actions">
                    <button className="popup-btn-add" onClick={onAddToCart}>
                        🛒 أضف إلى السلة
                    </button>
                    {slug && (
                        <a href={`${BASE}/product/${slug}`} className="popup-btn-details">
                            عرض التفاصيل
                        </a>
                    )}
                </div>
            </div>
        </div>
    );
}
