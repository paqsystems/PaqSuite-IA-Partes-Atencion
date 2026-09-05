import path from 'node:path'
import react from '@vitejs/plugin-react'
import { loadEnv, type Plugin } from 'vite'
import { defineConfig } from 'vitest/config'

/** Vite 8 sirve CJS de ace/knockout sin `export default`; Reporting los importa así. */
function reportingCjsDefaultExportPlugin(): Plugin {
  return {
    name: 'reporting-cjs-default-export',
    enforce: 'pre',
    transform(code, id) {
      const file = id.split('?')[0].replaceAll('\\', '/')
      const isAce = file.includes('/ace-builds/') && file.endsWith('.js')
      const isKnockout = file.includes('/knockout/') && file.endsWith('.js')
      if (!isAce && !isKnockout) {
        return null
      }
      if (/\bexport\s+default\b/.test(code)) {
        return null
      }
      if (isAce) {
        return {
          code: `${code}\nexport default (typeof globalThis !== 'undefined' && globalThis.ace) ? globalThis.ace : undefined;\n`,
          map: null,
        }
      }
      return {
        code: `${code}\nexport default (typeof globalThis !== 'undefined' && globalThis.ko) ? globalThis.ko : undefined;\n`,
        map: null,
      }
    },
  }
}

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
    plugins: [react(), reportingCjsDefaultExportPlugin()],
    // Vite 8: CJS de Reporting/ace/prop-types rompe `import X from` sin este flag.
    legacy: { inconsistentCjsInterop: true },
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
    optimizeDeps: {
      // Reporting se excluye (CJS/ace). Hay que prebundlear los módulos DX
      // que el diseñador importa: si aparecen en el primer Confirm, Vite
      // recarga y el widget queda en blanco.
      include: [
        'prop-types',
        'knockout',
        'devextreme/common/core/events/pointer',
        'devextreme/common/core/events/transform',
        'devextreme/core/component_registrator',
        'devextreme/core/devices',
        'devextreme/core/trial_panel',
        'devextreme/core/utils/browser',
        'devextreme/core/version',
        'devextreme/data/array_store',
        'devextreme/data/custom_store',
        'devextreme/data/data_source',
        'devextreme/events',
        'devextreme/integration/knockout',
        'devextreme/localization',
        'devextreme/localization/date',
        'devextreme/ui/accordion',
        'devextreme/ui/button',
        'devextreme/ui/button_group',
        'devextreme/ui/check_box',
        'devextreme/ui/color_box',
        'devextreme/ui/color_box/color_view',
        'devextreme/ui/data_grid',
        'devextreme/ui/date_box',
        'devextreme/ui/drop_down_box',
        'devextreme/ui/drop_down_button',
        'devextreme/ui/gallery',
        'devextreme/ui/list',
        'devextreme/ui/load_indicator',
        'devextreme/ui/load_panel',
        'devextreme/ui/menu',
        'devextreme/ui/notify',
        'devextreme/ui/number_box',
        'devextreme/ui/popover',
        'devextreme/ui/popup',
        'devextreme/ui/radio_group',
        'devextreme/ui/scroll_view',
        'devextreme/ui/select_box',
        'devextreme/ui/slider',
        'devextreme/ui/tab_panel',
        'devextreme/ui/tag_box',
        'devextreme/ui/text_area',
        'devextreme/ui/text_box',
        'devextreme/ui/text_box/ui.text_editor.base',
        'devextreme/ui/validation_engine',
        'devextreme/ui/validation_group',
        'devextreme/ui/validation_summary',
        'devextreme/ui/validator',
      ],
      exclude: [
        'ace-builds',
        '@devexpress/analytics-core',
        'devexpress-reporting',
        'devexpress-reporting-react',
      ],
    },
    test: {
      environment: 'jsdom',
      globals: true,
      setupFiles: ['./src/test/setupTests.ts'],
      include: ['src/**/*.test.ts', 'src/**/*.test.tsx'],
    },
  }
})
