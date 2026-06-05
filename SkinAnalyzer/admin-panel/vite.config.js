import { defineConfig, loadEnv } from 'vite'
import vue from '@vitejs/plugin-vue'
import AutoImport from 'unplugin-auto-import/vite'
import Components from 'unplugin-vue-components/vite'
import { fileURLToPath, URL } from 'node:url'

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')

  return {
  base: env.VITE_BASE_PATH || '/skin-admin/',
  plugins: [
    vue(),
    AutoImport({
      imports: [
        'vue',
        'vue-router',
        'pinia',
        { '@vueuse/core': ['useStorage', 'useDark', 'useToggle', 'useMediaQuery'] }
      ],
      dts: 'src/auto-imports.d.ts'
    }),
    Components({
      dts: 'src/components.d.ts'
    })
  ],
  resolve: {
    alias: {
      '@': fileURLToPath(new URL('./src', import.meta.url))
    }
  },
  server: {
    port: parseInt(env.VITE_DEV_PORT || '3000'),
    proxy: {
      '/api': {
        target: env.VITE_API_PROXY_TARGET || 'https://jenincare.shop',
        changeOrigin: true,
        secure: true
      },
      '/ws': {
        target: env.VITE_WS_URL || 'wss://jenincare.shop',
        ws: true,
        changeOrigin: true
      }
    }
  },
  build: {
    outDir: env.VITE_OUT_DIR || 'dist',
    assetsDir: 'assets',
    sourcemap: env.VITE_SOURCEMAP === 'true'
  }
}
})
