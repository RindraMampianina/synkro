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
})