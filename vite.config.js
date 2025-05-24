import { defineConfig } from 'vite';
import path from 'path';

export default defineConfig({
    build: {
        outDir: 'resources/dist',
        emptyOutDir: true,
        lib: {
            entry: path.resolve(__dirname, 'resources/js/app.ts'),
            name: 'LivewireDebugbar',
            fileName: () => 'app.js',
            formats: ['iife']
        },
        rollupOptions: {
            external: [],
            output: {
                globals: {},
                assetFileNames: (assetInfo) => {
                    if (assetInfo.name && assetInfo.name.endsWith('.css')) {
                        return 'app.css';
                    }
                    return assetInfo.name || 'asset';
                }
            }
        },
        cssCodeSplit: false,
        minify: process.env.NODE_ENV === 'production' ? 'terser' : false,
        sourcemap: process.env.NODE_ENV !== 'production',
        target: 'es2020'
    },
    css: {
        postcss: {
            plugins: [
                require('tailwindcss'),
                require('autoprefixer'),
            ],
        },
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, 'resources/js')
        }
    }
});
