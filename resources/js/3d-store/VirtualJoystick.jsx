import React, { useRef, useCallback, useEffect } from 'react';

export default function VirtualJoystick({ dirRef }) {
    const baseRef = useRef(null);
    const knobRef = useRef(null);
    const active = useRef(false);
    const baseCenter = useRef({ x: 0, y: 0 });
    const touchId = useRef(null);

    const getPos = useCallback((clientX, clientY) => {
        const dx = clientX - baseCenter.current.x;
        const dy = clientY - baseCenter.current.y;
        const maxR = 40;
        const dist = Math.sqrt(dx * dx + dy * dy);
        const clamped = Math.min(dist, maxR);
        const angle = Math.atan2(dy, dx);
        return {
            nx: (clamped / maxR) * Math.cos(angle),
            ny: (clamped / maxR) * Math.sin(angle),
            px: Math.cos(angle) * clamped,
            py: Math.sin(angle) * clamped,
        };
    }, []);

    const updateKnob = useCallback((px, py) => {
        if (knobRef.current) {
            knobRef.current.style.transform = `translate(${px}px, ${py}px)`;
        }
    }, []);

    const handlePointerDown = useCallback((e) => {
        e.preventDefault();
        const p = e.changedTouches ? e.changedTouches[0] : e;
        touchId.current = e.pointerId != null ? e.pointerId : (e.changedTouches ? e.changedTouches[0].identifier : null);
        const rect = baseRef.current.getBoundingClientRect();
        baseCenter.current = {
            x: rect.left + rect.width / 2,
            y: rect.top + rect.height / 2,
        };
        active.current = true;
        const { nx, ny, px, py } = getPos(p.clientX, p.clientY);
        dirRef.current = { x: nx, y: ny };
        updateKnob(px, py);
        if (baseRef.current) baseRef.current.setPointerCapture(e.pointerId);
    }, [getPos, dirRef, updateKnob]);

    const handlePointerMove = useCallback((e) => {
        if (!active.current) return;
        e.preventDefault();
        const p = e.changedTouches ? e.changedTouches[0] : e;
        const { nx, ny, px, py } = getPos(p.clientX, p.clientY);
        dirRef.current = { x: nx, y: ny };
        updateKnob(px, py);
    }, [getPos, dirRef, updateKnob]);

    const handlePointerUp = useCallback((e) => {
        if (!active.current) return;
        e.preventDefault();
        active.current = false;
        dirRef.current = { x: 0, y: 0 };
        updateKnob(0, 0);
        if (baseRef.current) {
            try { baseRef.current.releasePointerCapture(e.pointerId); } catch {}
        }
    }, [dirRef, updateKnob]);

    useEffect(() => {
        const el = baseRef.current;
        if (!el) return;
        el.addEventListener('pointerdown', handlePointerDown);
        el.addEventListener('pointermove', handlePointerMove);
        el.addEventListener('pointerup', handlePointerUp);
        el.addEventListener('pointercancel', handlePointerUp);
        return () => {
            el.removeEventListener('pointerdown', handlePointerDown);
            el.removeEventListener('pointermove', handlePointerMove);
            el.removeEventListener('pointerup', handlePointerUp);
            el.removeEventListener('pointercancel', handlePointerUp);
        };
    }, [handlePointerDown, handlePointerMove, handlePointerUp]);

    return (
        <div className="joystick-container">
            <div className="joystick-base" ref={baseRef}>
                <div className="joystick-knob" ref={knobRef} />
            </div>
        </div>
    );
}
