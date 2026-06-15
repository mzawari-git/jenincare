import React, { useMemo, useState } from 'react';
import { Html } from '@react-three/drei';
import * as THREE from 'three';

export default function ProductShelf({ shelf, onClick }) {
    const [hovered, setHovered] = useState(false);
    const { position, rotation, product } = shelf;

    const productName = product?.name_ar || shelf.productName || 'منتج';
    const price = product?.price ?? shelf.price ?? 0;
    const rating = product?.rating ?? shelf.rating ?? 0;
    const isOnSale = product?.is_on_sale ?? false;

    // Shelf base
    const shelfModel = useMemo(
        () => (
            <group position={position} rotation={rotation}>
                {/* Shelf base */}
                <mesh position={[0, 0.4, 0]} castShadow>
                    <boxGeometry args={[1.2, 0.05, 0.6]} />
                    <meshStandardMaterial color="#3a3a5a" roughness={0.7} metalness={0.3} />
                </mesh>

                {/* Shelf back */}
                <mesh position={[0, 0.8, -0.3]} castShadow>
                    <boxGeometry args={[1.2, 0.8, 0.05]} />
                    <meshStandardMaterial color="#2a2a4a" roughness={0.8} metalness={0.2} />
                </mesh>

                {/* Shelf sides */}
                {[-0.55, 0.55].map((x, i) => (
                    <mesh key={i} position={[x, 0.4, 0]} castShadow>
                        <boxGeometry args={[0.05, 0.8, 0.6]} />
                        <meshStandardMaterial color="#2a2a4a" roughness={0.8} metalness={0.2} />
                    </mesh>
                ))}

                {/* Product box */}
                <mesh
                    position={[0, 0.85, 0.05]}
                    castShadow
                    onPointerOver={() => setHovered(true)}
                    onPointerOut={() => setHovered(false)}
                    onClick={(e) => {
                        e.stopPropagation();
                        onClick();
                    }}
                >
                    <boxGeometry args={[0.6, 0.5, 0.3]} />
                    <meshStandardMaterial
                        color={hovered ? '#ff6b9d' : (isOnSale ? '#EF4444' : '#8B5CF6')}
                        roughness={0.3}
                        metalness={0.6}
                        emissive={hovered ? '#ff6b9d' : (isOnSale ? '#EF4444' : '#000000')}
                        emissiveIntensity={hovered ? 0.2 : (isOnSale ? 0.1 : 0)}
                    />
                </mesh>

                {/* Sale badge */}
                {isOnSale && (
                    <Html position={[0.45, 1.1, 0.05]} center style={{ pointerEvents: 'none' }}>
                        <div className="sale-badge-3d">خصم</div>
                    </Html>
                )}

                {/* Glow ring on hover */}
                {hovered && (
                    <mesh position={[0, 0.85, 0.05]}>
                        <boxGeometry args={[0.7, 0.6, 0.4]} />
                        <meshStandardMaterial
                            color="#ff6b9d"
                            transparent
                            opacity={0.15}
                            side={THREE.BackSide}
                        />
                    </mesh>
                )}

                {/* Price tag */}
                <Html position={[0, 1.3, 0]} center style={{ pointerEvents: 'none' }}>
                    <div className="product-tag">
                        <div className="product-name">{productName}</div>
                        <div className="product-price">
                            <span className="price-value">{price}</span>
                            <span className="price-currency"> ر.س</span>
                        </div>
                        <div className="product-rating">
                            {'★'.repeat(Math.floor(rating))}{'☆'.repeat(5 - Math.floor(rating))}
                            <span className="rating-value"> {rating}</span>
                        </div>
                    </div>
                </Html>
            </group>
        ),
        [position, rotation, productName, price, rating, isOnSale, hovered, onClick]
    );

    return shelfModel;
}
