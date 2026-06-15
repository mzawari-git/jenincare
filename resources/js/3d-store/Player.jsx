import React, { useEffect, useRef } from 'react';
import { useFrame, useThree } from '@react-three/fiber';
import { PointerLockControls } from '@react-three/drei';
import * as THREE from 'three';

const PLAYER_HEIGHT = 1.6;

export default function Player({ joystickRef }) {
    const { camera } = useThree();
    const controlsRef = useRef();
    const keys = useRef({ w: false, a: false, s: false, d: false, shift: false });
    const speed = useRef(0);
    const bob = useRef(0);
    const locked = useRef(false);

    useEffect(() => {
        const setKey = (e, v) => {
            switch (e.code) {
                case 'KeyW': keys.current.w = v; break;
                case 'KeyA': keys.current.a = v; break;
                case 'KeyS': keys.current.s = v; break;
                case 'KeyD': keys.current.d = v; break;
                case 'ShiftLeft': case 'ShiftRight': keys.current.shift = v; break;
            }
        };
        const down = (e) => setKey(e, true);
        const up = (e) => setKey(e, false);
        const onLockChange = () => {
            locked.current = document.pointerLockElement !== null;
            if (!locked.current) {
                keys.current = { w: false, a: false, s: false, d: false, shift: false };
                speed.current = 0;
            }
        };
        document.addEventListener('keydown', down);
        document.addEventListener('keyup', up);
        document.addEventListener('pointerlockchange', onLockChange);
        document.addEventListener('pointerlockerror', onLockChange);
        return () => {
            document.removeEventListener('keydown', down);
            document.removeEventListener('keyup', up);
            document.removeEventListener('pointerlockchange', onLockChange);
            document.removeEventListener('pointerlockerror', onLockChange);
        };
    }, []);

    useFrame((_, dt) => {
        camera.position.y = PLAYER_HEIGHT;
        const topSpeed = keys.current.shift ? 3.5 : 1.8;
        const forward = new THREE.Vector3(0, 0, -1).applyQuaternion(camera.quaternion);
        forward.y = 0; forward.normalize();
        const right = new THREE.Vector3(1, 0, 0).applyQuaternion(camera.quaternion);
        right.y = 0; right.normalize();
        const wish = new THREE.Vector3();

        if (locked.current) {
            if (keys.current.w) wish.add(forward);
            if (keys.current.s) wish.sub(forward);
            if (keys.current.a) wish.sub(right);
            if (keys.current.d) wish.add(right);
        }

        if (joystickRef?.current) {
            const jx = joystickRef.current.x;
            const jy = joystickRef.current.y;
            const mag = Math.sqrt(jx * jx + jy * jy);
            if (mag > 0.15) {
                wish.add(forward.clone().multiplyScalar(-jy));
                wish.add(right.clone().multiplyScalar(jx));
            }
        }

        const target = wish.length() > 0 ? topSpeed : 0;
        speed.current = target > 0
            ? Math.min(speed.current + 10 * dt, target)
            : Math.max(speed.current - 7 * dt, 0);

        if (speed.current > 0.01) {
            wish.normalize();
            const step = speed.current * dt;
            let nx = camera.position.x + wish.x * step;
            let nz = camera.position.z + wish.z * step;
            nx = Math.max(-1.7, Math.min(1.7, nx));
            const frontMax = (nx < -0.45 || nx > 0.45) ? 2.2 : 3.0;
            nz = Math.max(-2.2, Math.min(frontMax, nz));
            camera.position.x = nx;
            camera.position.z = nz;
        }

        if (speed.current > 0.1) {
            bob.current += dt * speed.current * 6;
            camera.position.y = PLAYER_HEIGHT + Math.sin(bob.current) * 0.04;
        }
    });

    return <PointerLockControls ref={controlsRef} />;
}
