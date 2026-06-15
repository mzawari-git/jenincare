import React, { useState, useEffect, useMemo } from 'react';
import { useThree } from '@react-three/fiber';
import * as THREE from 'three';

const BASE = document.getElementById('store-3d-root')?.dataset?.baseUrl || '/';

const PHOTOS = Array.from({ length: 10 }, (_, i) => {
    const num = String(i + 2).padStart(2, '0');
    return `${BASE}images/panoramas/001_${num}.jpg`;
});

export default function PhotoGallery({ images = PHOTOS }) {
    const [currentIndex, setCurrentIndex] = useState(0);
    const { invalidate } = useThree();

    const mat = useMemo(() => {
        const m = new THREE.MeshStandardMaterial({
            color: '#e8e0d8',
            roughness: 0.3,
            metalness: 0,
        });
        return m;
    }, []);

    useEffect(() => {
        const url = images[currentIndex];
        const loader = new THREE.TextureLoader();
        loader.load(url, (tex) => {
            tex.colorSpace = THREE.SRGBColorSpace;
            if (mat.map && mat.map !== tex) mat.map.dispose();
            mat.map = tex;
            mat.needsUpdate = true;
            invalidate();
        });
    }, [currentIndex, images, mat, invalidate]);

    useEffect(() => {
        const handler = (e) => {
            if (e.key === 'ArrowRight') {
                setCurrentIndex(i => (i + 1) % images.length);
            } else if (e.key === 'ArrowLeft') {
                setCurrentIndex(i => (i - 1 + images.length) % images.length);
            }
        };
        document.addEventListener('keydown', handler);
        return () => document.removeEventListener('keydown', handler);
    }, [images.length]);

    return (
        <group position={[0, 1.5, -2.44]}>
            {/* Photo — faces +Z (into the room) */}
            <mesh material={mat}>
                <planeGeometry args={[3.4, 2.6]} />
            </mesh>
            {/* Frame border */}
            {[[-1.7, 0], [1.7, 0], [0, -1.3], [0, 1.3]].map(([dx, dy], i) => (
                <mesh key={i} position={[dx, dy, 0.02]}>
                    <planeGeometry args={[dx === 0 ? 3.46 : 0.06, dy === 0 ? 2.66 : 0.06]} />
                    <meshStandardMaterial color="#d4c8b8" />
                </mesh>
            ))}
        </group>
    );
}
