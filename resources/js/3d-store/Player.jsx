import React, { useEffect, useRef, useMemo } from 'react';
import { useFrame, useThree } from '@react-three/fiber';
import { PointerLockControls } from '@react-three/drei';
import * as THREE from 'three';

const PLAYER_RADIUS = 0.3;
const PLAYER_HEIGHT = 1.6;

// Collision boxes mirroring the store layout from Store.jsx
const W = 4;
const D = 5;
const H = 4;
const CX = 0;
const CZ = 2.5;
const WT = 0.15;

const COLLISION_BOXES = [
    // Perimeter walls
    { x: -W / 2, z: CZ, sx: WT, sz: D },        // left
    { x: W / 2, z: CZ, sx: WT, sz: D },          // right
    { x: CX, z: D, sx: W, sz: WT },               // back
    { x: -1.375, z: 0, sx: 1.25, sz: WT },        // front-left
    { x: 1.375, z: 0, sx: 1.25, sz: WT },         // front-right

    // Section back walls (matching Section.jsx positions)
    // Skincare + creams (left side, Z ≈ 0.7 and Z ≈ 2.6)
    { x: -2.2, z: 0.7, sx: 2.5, sz: 0.2 },
    { x: -2.2, z: 2.6, sx: 2.5, sz: 0.2 },
    // Devices + salon (right side)
    { x: 2.2, z: 0.7, sx: 2.5, sz: 0.2 },
    { x: 2.2, z: 2.6, sx: 2.5, sz: 0.2 },
    // Offers (center back)
    { x: 0, z: 4.0, sx: 2.0, sz: 0.2 },
];

function checkCollision(x, z, boxes) {
    for (const b of boxes) {
        const halfW = b.sx / 2 + PLAYER_RADIUS;
        const halfD = b.sz / 2 + PLAYER_RADIUS;
        if (Math.abs(x - b.x) < halfW && Math.abs(z - b.z) < halfD) {
            return true;
        }
    }
    return false;
}

export default function Player() {
    const { camera } = useThree();
    const controlsRef = useRef();
    const keys = useRef({ w: false, a: false, s: false, d: false, shift: false });
    const velocity = useRef(new THREE.Vector3());
    const isLocked = useRef(false);
    const boxes = useRef(COLLISION_BOXES);

    useEffect(() => {
        const handleKeyDown = (e) => {
            switch (e.code) {
                case 'KeyW': keys.current.w = true; break;
                case 'KeyA': keys.current.a = true; break;
                case 'KeyS': keys.current.s = true; break;
                case 'KeyD': keys.current.d = true; break;
                case 'ShiftLeft':
                case 'ShiftRight': keys.current.shift = true; break;
            }
        };

        const handleKeyUp = (e) => {
            switch (e.code) {
                case 'KeyW': keys.current.w = false; break;
                case 'KeyA': keys.current.a = false; break;
                case 'KeyS': keys.current.s = false; break;
                case 'KeyD': keys.current.d = false; break;
                case 'ShiftLeft':
                case 'ShiftRight': keys.current.shift = false; break;
            }
        };

        document.addEventListener('keydown', handleKeyDown);
        document.addEventListener('keyup', handleKeyUp);

        return () => {
            document.removeEventListener('keydown', handleKeyDown);
            document.removeEventListener('keyup', handleKeyUp);
        };
    }, []);

    useEffect(() => {
        const handleLockChange = () => {
            isLocked.current = document.pointerLockElement !== null;
        };
        document.addEventListener('pointerlockchange', handleLockChange);
        return () => document.removeEventListener('pointerlockchange', handleLockChange);
    }, []);

    useFrame((_, delta) => {
        const speed = keys.current.shift ? 3 : 1.5;
        const dir = new THREE.Vector3();
        const forward = new THREE.Vector3(0, 0, -1).applyQuaternion(camera.quaternion);
        forward.y = 0;
        forward.normalize();
        const right = new THREE.Vector3(1, 0, 0).applyQuaternion(camera.quaternion);
        right.y = 0;
        right.normalize();

        if (keys.current.w) dir.add(forward);
        if (keys.current.s) dir.sub(forward);
        if (keys.current.a) dir.sub(right);
        if (keys.current.d) dir.add(right);

        if (dir.length() > 0) {
            dir.normalize();

            const newX = camera.position.x + dir.x * speed * delta;
            const newZ = camera.position.z + dir.z * speed * delta;

            // Clamp to ground
            camera.position.y = PLAYER_HEIGHT;

            // Try X movement independently, then Z (slide along walls)
            if (!checkCollision(newX, camera.position.z, boxes.current)) {
                camera.position.x = newX;
            }
            if (!checkCollision(camera.position.x, newZ, boxes.current)) {
                camera.position.z = newZ;
            }

            // Hard bounds as safety net
            camera.position.x = Math.max(-2.2, Math.min(2.2, camera.position.x));
            camera.position.z = Math.max(-0.5, Math.min(5.5, camera.position.z));
        }
    });

    return <PointerLockControls ref={controlsRef} />;
}
