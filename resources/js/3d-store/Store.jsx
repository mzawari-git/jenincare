import React, { useMemo } from 'react';
import Section from './Section';
import ProductShelf from './ProductShelf';
import PhotoGallery from './PhotoGallery';
import { SECTIONS } from './data/sections';

export default function Store({ shelves = [], onProductClick, onSectionChange, currentSection }) {
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
            <ambientLight intensity={0.9} />
            <hemisphereLight args={['#fff8f0', '#e8e0d8', 0.6]} />
            <directionalLight position={[5, 10, 5]} intensity={1.2} castShadow shadow-mapSize-width={2048} shadow-mapSize-height={2048} />
            <pointLight position={[-2, 3.5, 1]} intensity={0.6} color="#ffffff" />
            <pointLight position={[2, 3.5, 1]} intensity={0.6} color="#ffffff" />
            <pointLight position={[0, 3.5, -1]} intensity={0.5} color="#ffffff" />
            <pointLight position={[-1, 2, 0]} intensity={0.4} color="#ffffff" />
            <pointLight position={[1, 2, 0]} intensity={0.4} color="#ffffff" />

            {/* Floor: box 4m × 0.1m × 5m at origin */}
            <mesh position={[0, 0, 0]} receiveShadow>
                <boxGeometry args={[4, 0.1, 5]} />
                <meshStandardMaterial color="#f8f4ee" roughness={0.95} metalness={0} />
            </mesh>

            {/* Ceiling */}
            <mesh position={[0, 4, 0]}>
                <boxGeometry args={[4, 0.05, 5]} />
                <meshStandardMaterial color="#ffffff" roughness={1} metalness={0} />
            </mesh>

            {/* Left wall */}
            <mesh position={[-2, 2, 0]} receiveShadow>
                <boxGeometry args={[0.1, 4, 5]} />
                <meshStandardMaterial color="#f5f0e8" roughness={0.9} metalness={0} />
            </mesh>

            {/* Right wall */}
            <mesh position={[2, 2, 0]} receiveShadow>
                <boxGeometry args={[0.1, 4, 5]} />
                <meshStandardMaterial color="#f5f0e8" roughness={0.9} metalness={0} />
            </mesh>

            {/* Back wall at z=-2.5 */}
            <mesh position={[0, 2, -2.5]} receiveShadow>
                <boxGeometry args={[4, 4, 0.1]} />
                <meshStandardMaterial color="#f5f0e8" roughness={0.9} metalness={0} />
            </mesh>

            {/* Front-left wall */}
            <mesh position={[-1.375, 2, 2.5]} receiveShadow>
                <boxGeometry args={[1.25, 4, 0.1]} />
                <meshStandardMaterial color="#f5f0e8" roughness={0.9} metalness={0} />
            </mesh>

            {/* Front-right wall */}
            <mesh position={[1.375, 2, 2.5]} receiveShadow>
                <boxGeometry args={[1.25, 4, 0.1]} />
                <meshStandardMaterial color="#f5f0e8" roughness={0.9} metalness={0} />
            </mesh>

            {/* Photo gallery on back wall */}
            <PhotoGallery />

            {/* Sections */}
            {SECTIONS.map((section) => (
                <Section key={section.id} section={section} isActive={currentSection === section.id} />
            ))}

            {/* Product shelves */}
            {Object.entries(shelvesBySection).map(([sectionId, shelves]) => (
                <React.Fragment key={sectionId}>
                    {shelves.map((shelf) => (
                        <ProductShelf key={shelf.id} shelf={shelf} onClick={() => onProductClick(shelf)} />
                    ))}
                </React.Fragment>
            ))}
        </>
    );
}
