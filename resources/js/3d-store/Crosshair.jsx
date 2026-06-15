import React from 'react';

export default function Crosshair() {
    return (
        <div className="crosshair">
            <div className="crosshair-dot" />
            <svg className="crosshair-ring" width="28" height="28" viewBox="0 0 28 28">
                <circle cx="14" cy="14" r="12" fill="none" stroke="rgba(255,255,255,0.3)" strokeWidth="1" />
                <circle cx="14" cy="14" r="12" fill="none" stroke="rgba(201,169,110,0.4)" strokeWidth="1" strokeDasharray="4 4" />
            </svg>
        </div>
    );
}
