import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [vue()],
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        watch: {
            usePolling: process.env.CHOKIDAR_USEPOLLING === 'true',
        },
        hmr: {
            host: process.env.VITE_HMR_HOST || 'localhost',
            port: 5173,
        },
    },
    build: {
        outDir: 'public/build',
        manifest: 'manifest.json',
        emptyOutDir: true,
        rollupOptions: {
            output: {
                entryFileNames: 'assets/[name]-[hash].js',
                chunkFileNames: 'assets/[name]-[hash].js',
                assetFileNames: 'assets/[name]-[hash][extname]',
            },
        },
    },
});
