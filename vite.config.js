import { defineConfig } from 'vite'
import react from '@vitejs/plugin-react'

export default defineConfig({
  plugins: [react()],
  server: {
    host: '0.0.0.0', // Exponer en todas las interfaces
    port: 3000, // Puerto React
    strictPort: true, // Fallar si puerto ocupado
    open: false, // No abrir navegador automáticamente
    
    // ✅ PROXY PARA TODOS LOS ARCHIVOS PHP
    proxy: {
      // Proxy específico para archivos PHP principales
      '/crudpropiedades.php': {
        target: 'http://192.168.1.17',
        changeOrigin: true,
        secure: false,
        configure: (proxy, options) => {
          proxy.on('proxyReq', (proxyReq, req, res) => {
            console.log('🔄 Proxying:', req.url, '→', options.target + req.url);
          });
        }
      },
      
      '/usuarioPropiedades.php': {
        target: 'http://192.168.1.17',
        changeOrigin: true,
        secure: false
      },
      
      '/gestor.php': {
        target: 'http://192.168.1.17',
        changeOrigin: true,
        secure: false
      },
      
      '/login_consulta.php': {
        target: 'http://192.168.1.17',
        changeOrigin: true,
        secure: false
      },
      
      '/indexfiltro.php': {
        target: 'http://192.168.1.17',
        changeOrigin: true,
        secure: false
      },
      
      // Proxy general para cualquier archivo PHP
      '^/.*\\.php': {
        target: 'http://192.168.1.17',
        changeOrigin: true,
        secure: false
      },
      
      // Carpetas de recursos
      '/setup': {
        target: 'http://192.168.1.17',
        changeOrigin: true,
        secure: false
      },
      '/propiedades': {
        target: 'http://192.168.1.17',
        changeOrigin: true,
        secure: false
      },
      '/IMG': {
        target: 'http://192.168.1.17',
        changeOrigin: true,
        secure: false
      },
      '/css': {
        target: 'http://192.168.1.17',
        changeOrigin: true,
        secure: false
      },
      '/js': {
        target: 'http://192.168.1.17',
        changeOrigin: true,
        secure: false
      }
    }
  }
})