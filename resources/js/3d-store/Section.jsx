import React, { useMemo } from 'react';
import { Text } from '@react-three/drei';
import * as THREE from 'three';

const FONT_URL = (() => {
    const root = document.getElementById('store-3d-root');
    const base = root?.dataset?.baseUrl || '/';
    return `${base}fonts/NotoSansArabic-Bold.ttf`;
})();

export default function Section({ section, isActive }) {
    const { position, size, color, name_ar, name_en } = section;

    const floor = useMemo(
        () => (
            <mesh position={[position[0], 0.01, position[2]]} receiveShadow>
                <boxGeometry args={[size[0], 0.02, size[2]]} />
                <meshStandardMaterial
                    color={color}
                    transparent
                    opacity={0.25}
                    roughness={0.8}
                />
            </mesh>
        ),
        [position, size, color]
    );

    const border = useMemo(
        () => (
            <mesh position={[position[0], 0.02, position[2]]}>
                <boxGeometry args={[size[0] + 0.1, 0.01, size[2] + 0.1]} />
                <meshStandardMaterial
                    color={color}
                    transparent
                    opacity={isActive ? 0.6 : 0.25}
                    emissive={color}
                    emissiveIntensity={isActive ? 0.3 : 0}
                />
            </mesh>
        ),
        [position, size, color, isActive]
    );

    // Display wall on entrance-facing side (+Z)
    const wall = useMemo(
        () => (
            <mesh position={[position[0], 2, position[2] + size[2] / 2 + 0.5]} receiveShadow>
                <boxGeometry args={[size[0] + 0.5, 4, 0.2]} />
                <meshStandardMaterial
                    color="#e8e0d8"
                    roughness={0.9}
                    metalness={0}
                    transparent
                    opacity={0.85}
                />
            </mesh>
        ),
        [position, size]
    );

    return (
        <group>
            {floor}
            {border}
            {wall}

            {/* Section Sign */}
            <group position={[position[0], 3.2, position[2] + size[2] / 2 + 0.3]}>
                <mesh>
                    <planeGeometry args={[4, 0.8]} />
                    <meshStandardMaterial
                        color="#f0ece4"
                        transparent
                        opacity={0.9}
                        side={THREE.DoubleSide}
                    />
                </mesh>
                <Text
                    position={[0, 0, 0.01]}
                    fontSize={0.4}
                    color={color}
                    anchorX="center"
                    anchorY="middle"
                    font={FONT_URL}
                >
                    {name_ar}
                </Text>
            </group>

            {/* Pillars */}
            {[
                [-size[0] / 2 + 0.3, 0, -size[2] / 2 + 0.3],
                [size[0] / 2 - 0.3, 0, -size[2] / 2 + 0.3],
                [-size[0] / 2 + 0.3, 0, size[2] / 2 - 0.3],
                [size[0] / 2 - 0.3, 0, size[2] / 2 - 0.3],
            ].map((p, i) => (
                <mesh key={i} position={[position[0] + p[0], 2, position[2] + p[2]]}>
                    <cylinderGeometry args={[0.08, 0.1, 4, 8]} />
                    <meshStandardMaterial color="#ddd8d0" metalness={0.1} roughness={0.8} />
                </mesh>
            ))}

            {/* Section accent light */}
            <pointLight position={[position[0], 3.8, position[2]]} intensity={0.2} color={color} />
        </group>
    );
}
