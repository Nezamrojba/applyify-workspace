import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'
import path from 'node:path'

export default defineConfig({
  plugins: [vue()],
  server: { host: true, port: 5173 },
  preview: { port: 5173 },
  css: { postcss: './postcss.config.js' },
  resolve: { alias: { '@': path.resolve(__dirname, './src') } }
})
