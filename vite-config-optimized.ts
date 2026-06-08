import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import tailwindcss from 'tailwindcss';
import singleFile from 'vite-plugin-singlefile';

export default defineConfig({
  plugins: [react(), singleFile()],
  
  build: {
    minify: 'terser',
    terserOptions: {
      compress: {
        drop_console: true,
        drop_debugger: true,
      },
    },
    
    rollupOptions: {
      output: {
        manualChunks: {
          'react-vendor': ['react', 'react-dom', 'react-router-dom'],
          'chart-vendor': ['recharts'],
          'ui-components': ['clsx', 'lucide-react', 'tailwind-merge'],
        },
      },
    },
    
    chunkSizeWarningLimit: 500,
    cssCodeSplit: true,
    sourcemap: false,
    reportCompressedSize: true,
  },
  
  css: {
    postcss: {
      plugins: [
        tailwindcss,
        require('autoprefixer'),
      ],
    },
  },
  
  server: {
    middlewareMode: false,
    hmr: {
      protocol: 'ws',
      host: 'localhost',
      port: 5173,
    },
  },
  
  optimizeDeps: {
    include: ['react', 'react-dom', 'react-router-dom', 'recharts'],
    exclude: [],
  },
});
