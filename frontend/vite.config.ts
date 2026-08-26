import path from 'node:path'
import react from '@vitejs/plugin-react'
import { loadEnv } from 'vite'
import { defineConfig } from 'vitest/config'

export default defineConfig(({ mode }) => {
  const env = loadEnv(mode, process.cwd(), '')
  const apiProxyTarget =
    env.VITE_API_PROXY_TARGET ||
    process.env.VITE_API_PROXY_TARGET ||
    'http://127.0.0.1:8010'
  const dxReportingTarget = (
    env.VITE_DX_REPORTING_HOST ||
    process.env.VITE_DX_REPORTING_HOST ||
    'http://127.0.0.1:5055'
  ).replace(/\/+$/, '')

  return {
    plugins: [react()],
    resolve: {
      dedupe: ['react', 'react-dom', 'devextreme', 'devextreme-react'],
      alias: {
        react: path.resolve(__dirname, 'node_modules/react'),
        'react-dom': path.resolve(__dirname, 'node_modules/react-dom'),
        'react/jsx-runtime': path.resolve(__dirname, 'node_modules/react/jsx-runtime.js'),
        'react/jsx-dev-runtime': path.resolve(
          __dirname,
          'node_modules/react/jsx-dev-runtime.js',
        ),
        devextreme: path.resolve(__dirname, 'node_modules/devextreme'),
        'devextreme-react': path.resolve(__dirname, 'node_modules/devextreme-react'),
      },
    },
    server: {
      port: 3000,
      proxy: {
        '/api': {
          target: apiProxyTarget,
          changeOrigin: true,
        },
        // ASP.NET DevExpress Reporting (diseñador / viewer / query builder)
        '/DXXRD': { target: dxReportingTarget, changeOrigin: true },
        '/DXXRDV': { target: dxReportingTarget, changeOrigin: true },
        '/DXXQB': { target: dxReportingTarget, changeOrigin: true },
      },
    },
    test: {
      environment: 'jsdom',
      globals: true,
      setupFiles: ['./src/test/setupTests.ts'],
      include: ['src/**/*.test.ts', 'src/**/*.test.tsx'],
    },
  }
})
