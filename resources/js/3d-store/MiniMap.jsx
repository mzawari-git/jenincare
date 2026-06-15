import React from 'react';
import { SECTIONS } from './data/sections';

export default function MiniMap({ currentSection, onSectionClick }) {
    const mapWidth = 240;
    const mapHeight = 300;

    // Store spans X: -2.2 to 2.2, Z: -0.5 to 5.5
    const scaleX = (x) => {
        return ((x + 2.2) / 4.4) * mapWidth;
    };

    const scaleZ = (z) => {
        return ((z + 0.5) / 6) * mapHeight;
    };

    return (
        <div className="minimap-container">
            <div className="minimap-title">🗺️ خريطة المتجر</div>
            <svg width={mapWidth} height={mapHeight} viewBox={`0 0 ${mapWidth} ${mapHeight}`}>
                {/* Background */}
                <rect width={mapWidth} height={mapHeight} rx={8} fill="#0f0f1a" opacity={0.9} />

                {/* Sections */}
                {SECTIONS.map((section) => {
                    const cx = scaleX(section.position[0]);
                    const cy = scaleZ(section.position[2]);
                    const w = scaleX(section.size[0]) - scaleX(0);
                    const h = scaleZ(section.size[2]) - scaleZ(0);
                    const isActive = currentSection === section.id;

                    return (
                        <g
                            key={section.id}
                            onClick={() => onSectionClick(section.id)}
                            style={{ cursor: 'pointer' }}
                        >
                            <rect
                                x={cx - w / 2}
                                y={cy - h / 2}
                                width={w}
                                height={h}
                                rx={4}
                                fill={section.color}
                                opacity={isActive ? 0.8 : 0.3}
                                stroke={section.color}
                                strokeWidth={isActive ? 2 : 0.5}
                            />
                            <text
                                x={cx}
                                y={cy + 4}
                                textAnchor="middle"
                                fill="white"
                                fontSize={9}
                                fontWeight={isActive ? 'bold' : 'normal'}
                            >
                                {section.name_ar}
                            </text>
                        </g>
                    );
                })}

                {/* Player */}
                <circle
                    cx={scaleX(0)}
                    cy={scaleZ(0)}
                    r={5}
                    fill="#22D3EE"
                    stroke="#fff"
                    strokeWidth={2}
                />
            </svg>
        </div>
    );
}
