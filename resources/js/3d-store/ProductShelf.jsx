import React, { useMemo, useState, useEffect, useRef } from 'react';
import * as THREE from 'three';

const BASE = document.getElementById('store-3d-root')?.dataset?.baseUrl || '/';

export default function ProductShelf({ shelf, onClick }) {
    const [hovered, setHovered] = useState(false);
    const [tex, setTex] = useState(null);
    const { position, rotation, product } = shelf;

    const productName = product?.name_ar || shelf.productName || 'منتج';
    const price = product?.price ?? shelf.price ?? 0;
    const isOnSale = product?.is_on_sale ?? false;
    const imageUrl = product?.main_image_url || product?.image || null;

    useEffect(() => {
        if (!imageUrl) return;
        setTex(null);
        const loader = new THREE.TextureLoader();
        loader.load(
            imageUrl.startsWith('http') ? imageUrl : BASE + imageUrl,
            (t) => { t.colorSpace = THREE.SRGBColorSpace; setTex(t); },
            undefined,
            () => setTex(null)
        );
    }, [imageUrl]);

    const shelfModel = useMemo(
        () => (
            <group position={position} rotation={rotation}>
                {/* Shelf base — white */}
                <mesh position={[0, -0.08, 0]} castShadow>
                    <boxGeometry args={[1.2, 0.04, 0.6]} />
                    <meshStandardMaterial color="#f0ece4" roughness={0.8} metalness={0} />
                </mesh>

                {/* Shelf back panel */}
                <mesh position={[0, 0.45, -0.3]} castShadow>
                    <boxGeometry args={[1.2, 0.9, 0.03]} />
                    <meshStandardMaterial color="#e8e0d8" roughness={0.9} metalness={0} />
                </mesh>

                {/* Product image plane */}
                <mesh
                    position={[0, 0.5, 0.08]}
                    onPointerOver={() => setHovered(true)}
                    onPointerOut={() => setHovered(false)}
                    onClick={(e) => { e.stopPropagation(); onClick(); }}
                >
                    <planeGeometry args={[0.9, 0.9]} />
                    <meshStandardMaterial
                        map={tex}
                        color={tex ? '#ffffff' : (hovered ? '#ff6b9d' : (isOnSale ? '#EF4444' : '#8B5CF6'))}
                        roughness={tex ? 0.5 : 0.3}
                        metalness={tex ? 0.05 : 0.5}
                        side={THREE.DoubleSide}
                        emissive={tex ? '#222233' : '#000000'}
                        emissiveIntensity={0.15}
                    />
                </mesh>

                {/* Glow on hover */}
                {hovered && (
                    <mesh position={[0, 0.5, 0.08]}>
                        <planeGeometry args={[1.0, 1.0]} />
                        <meshStandardMaterial color="#ff88cc" transparent opacity={0.2} side={THREE.DoubleSide} />
                    </mesh>
                )}
            </group>
        ),
        [position, rotation, isOnSale, hovered, tex, onClick]
    );

    return shelfModel;
}
