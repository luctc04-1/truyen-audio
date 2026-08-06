import { defineConfig, loadEnv } from 'vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin'
import { fileURLToPath, URL } from 'node:url';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const apiProxyTarget = (
        env.VITE_API_PROXY_TARGET ||
        env.APP_URL ||
        'http://truyen-audio.me'
    ).replace(/\/$/, '');

    return {
        plugins: [
            laravel({
                input: [
                    'resources/js/main.js',
                    'resources/css/app.css',
                ],
                refresh: true,
            }),

            vue(),
        ],
        resolve: {
            alias: {
                '@': fileURLToPath(new URL('./resources/js', import.meta.url)),
            },
        },
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
            proxy: {
                '/api': {
                    target: apiProxyTarget,
                    changeOrigin: true,
                    secure: false,
                },
            },
        },
    };
});
