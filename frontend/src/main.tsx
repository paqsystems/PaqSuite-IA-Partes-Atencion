import './init-devextreme-license'
import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { I18nextProvider } from 'react-i18next'
import {
  bootstrapApiBaseUrl,
  isNativeApp,
  syncDevExtremeLocale,
} from '@paqsuite/react-core'
import '@paqsuite/react-core/auth.css'
import '@paqsuite/react-core/shell.css'
import { AppRouter } from './app/AppRouter.tsx'
import { ThemeProvider } from './app/providers/ThemeProvider'
import { installApiAuthFetch } from './features/auth/installApiAuthFetch'
import { installCapacitorPreferencesAdapter } from './features/auth/installCapacitorPreferencesAdapter'
import { bootstrapPlatformCliente } from './features/auth/platformContext'
import i18n from './i18n/i18n'
import './index.css'
import './features/partes/mobile/partesMobileProcess.css'

async function bootstrap(): Promise<void> {
  bootstrapPlatformCliente()
  installApiAuthFetch()
  await installCapacitorPreferencesAdapter()
  await bootstrapApiBaseUrl({
    envBaseUrl: import.meta.env.VITE_API_BASE_URL,
    projectSlug: 'partesatencion',
    isNative: isNativeApp(),
  })
  syncDevExtremeLocale('es')

  if (isNativeApp()) {
    document.documentElement.classList.add('pq-native-app')
  }

  createRoot(document.getElementById('root')!).render(
    <StrictMode>
      <I18nextProvider i18n={i18n}>
        <ThemeProvider>
          <AppRouter />
        </ThemeProvider>
      </I18nextProvider>
    </StrictMode>,
  )
}

void bootstrap()
