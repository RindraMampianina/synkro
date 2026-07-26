/// <reference types="vitest/config" />
import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

const mercureTarget = process.env.VITE_MERCURE_PROXY_TARGET || 'http://localhost:3000'

export default defineConfig({
  plugins: [react()],
  server: {
    proxy: {
      '/mercure': {
        target: mercureTarget,
        changeOrigin: true,
        rewrite: (path) => path.replace(/^\/mercure/, ''),
      },
    },
  },
  test: {
    globals: true,
    environment: 'jsdom',
    setupFiles: './src/test/setup.ts',
    css: false,
    // forks is flaky on GitHub Actions runners (worker startup timeouts)
    pool: 'threads',
    fileParallelism: !process.env.CI,
    maxWorkers: process.env.CI ? 2 : undefined,
  },
})
