import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import path from 'path';

export default defineConfig({
    plugins: [react()],
    root: 'resources/js/3d-store',
    base: '/dist/',
    build: {
        outDir: path.resolve(__dirname, 'public/dist'),
        emptyOutDir: true,
        manifest: true,
        rollupOptions: {
            input: path.resolve(__dirname, 'resources/js/3d-store/main.jsx'),
        },
    },
    server: {
        port: 5173,
        strictPort: true,
    },
});
