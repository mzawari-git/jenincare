import React, { useMemo } from 'react';
import { useThree } from '@react-three/fiber';
import * as THREE from 'three';
import Section from './Section';
import ProductShelf from './ProductShelf';
import { SECTIONS } from './data/sections';

// Store boundaries: 4m wide (X: -2..2) x 5m deep (Z: 0..5) x 4m tall
const W = 4;
const D = 5;
const H = 4;
const CX = 0;
const CZ = 2.5;
const WALL_THICK = 0.15;

function Wall({ position, size, color = '#1a1a2e', opacity = 0.85 }) {
    return (
        <mesh position={position} receiveShadow>
            <boxGeometry args={size} />
            <meshStandardMaterial color={color} roughness={0.9} metalness={0.1} transparent opacity={opacity} />
        </mesh>
    );
}

export default function Store({ shelves = [], onProductClick, onSectionChange, currentSection }) {
    const { scene } = useThree();

    const ambientLight = useMemo(() => <ambientLight intensity={0.4} />, []);

    const sunLight = useMemo(
        () => (
            <directionalLight
                position={[5, 15, 2]}
                intensity={0.8}
                castShadow
                shadow-mapSize-width={2048}
                shadow-mapSize-height={2048}
            />
        ),
        []
    );

    const ground = useMemo(
        () => (
            <mesh rotation={[-Math.PI / 2, 0, 0]} position={[CX, -0.01, CZ]} receiveShadow>
                <planeGeometry args={[W + 0.2, D + 0.2]} />
                <meshStandardMaterial color="#12121f" roughness={0.95} metalness={0.05} />
            </mesh>
        ),
        []
    );

    const ceiling = useMemo(
        () => (
            <mesh rotation={[Math.PI / 2, 0, 0]} position={[CX, H, CZ]}>
                <planeGeometry args={[W + 0.2, D + 0.2]} />
                <meshStandardMaterial color="#0a0a14" roughness={1} metalness={0} transparent opacity={0.4} />
            </mesh>
        ),
        []
    );

    const shelvesBySection = useMemo(() => {
        const map = {};
        shelves.forEach((s) => {
            if (!map[s.section]) map[s.section] = [];
            map[s.section].push(s);
        });
        return map;
    }, [shelves]);

    return (
        <>
            {ambientLight}
            <hemisphereLight args={['#4a4a8a', '#1a1a2e', 0.3]} />
            <pointLight position={[-3, 4, 1]} intensity={0.4} color="#ff88cc" />
            <pointLight position={[3, 4, 1]} intensity={0.4} color="#88ccff" />
            {ground}
            {ceiling}

            <gridHelper args={[4, 5, '#2a2a4a', '#1a1a3a']} position={[CX, 0, CZ]} />

            {/* ── Perimeter walls (4m wide x 5m deep room) ── */}
            {/* Left wall */}
            <Wall position={[-W / 2, H / 2, CZ]} size={[WALL_THICK, H, D]} />
            {/* Right wall */}
            <Wall position={[W / 2, H / 2, CZ]} size={[WALL_THICK, H, D]} />
            {/* Back wall */}
            <Wall position={[CX, H / 2, D]} size={[W, H, WALL_THICK]} />
            {/* Front wall — two segments with entrance gap */}
            <Wall position={[-1.375, H / 2, 0]} size={[1.25, H, WALL_THICK]} />
            <Wall position={[1.375, H / 2, 0]} size={[1.25, H, WALL_THICK]} />

            {/* Sections */}
            {SECTIONS.map((section) => (
                <Section
                    key={section.id}
                    section={section}
                    isActive={currentSection === section.id}
                />
            ))}

            {/* Product shelves */}
            {Object.entries(shelvesBySection).map(([sectionId, shelves]) => (
                <React.Fragment key={sectionId}>
                    {shelves.map((shelf) => (
                        <ProductShelf
                            key={shelf.id}
                            shelf={shelf}
                            onClick={() => onProductClick(shelf)}
                        />
                    ))}
                </React.Fragment>
            ))}
        </>
    );
}
