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
                {/* Shelf spotlight */}
                <pointLight position={[0, 1.5, 0.3]} intensity={0.4} color="#ffddaa" distance={2} />

                {/* Shelf base — white */}
                <mesh position={[0, -0.1, 0]} castShadow>
                    <boxGeometry args={[1.6, 0.05, 0.8]} />
                    <meshStandardMaterial color="#f0ece4" roughness={0.8} metalness={0} />
                </mesh>

                {/* Shelf back panel */}
                <mesh position={[0, 0.65, -0.4]} castShadow>
                    <boxGeometry args={[1.6, 1.6, 0.05]} />
                    <meshStandardMaterial color="#e8e0d8" roughness={0.9} metalness={0} />
                </mesh>

                {/* Shelf sides */}
                {[-0.75, 0.75].map((x, i) => (
                    <mesh key={i} position={[x, 0.65, 0]} castShadow>
                        <boxGeometry args={[0.05, 1.6, 0.8]} />
                        <meshStandardMaterial color="#e8e0d8" roughness={0.9} metalness={0} />
                    </mesh>
                ))}

                {/* Product image plane — large, double-sided, bright */}
                <mesh
                    position={[0, 0.8, 0.1]}
                    onPointerOver={() => setHovered(true)}
                    onPointerOut={() => setHovered(false)}
                    onClick={(e) => { e.stopPropagation(); onClick(); }}
                >
                    <planeGeometry args={[1.3, 1.3]} />
                    <meshStandardMaterial
                        map={tex}
                        color={tex ? '#ffffff' : (hovered ? '#ff6b9d' : (isOnSale ? '#EF4444' : '#8B5CF6'))}
                        roughness={tex ? 0.5 : 0.3}
                        metalness={tex ? 0.05 : 0.5}
                        side={THREE.DoubleSide}
                        emissive={tex ? '#222233' : '#000000'}
                        emissiveIntensity={0.2}
                    />
                </mesh>

                {/* Glow on hover */}
                {hovered && (
                    <mesh position={[0, 0.8, 0.1]}>
                        <planeGeometry args={[1.5, 1.5]} />
                        <meshStandardMaterial color="#ff88cc" transparent opacity={0.25} side={THREE.DoubleSide} />
                    </mesh>
                )}
            </group>
        ),
        [position, rotation, isOnSale, hovered, tex, onClick]
    );

    return shelfModel;
}
