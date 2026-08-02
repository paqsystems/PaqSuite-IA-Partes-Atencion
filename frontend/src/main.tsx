import './init-devextreme-license'
import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import { I18nextProvider } from 'react-i18next'
import { syncDevExtremeLocale } from '@paqsuite/react-core'
import '@paqsuite/react-core/auth.css'
import '@paqsuite/react-core/shell.css'
import { AppRouter } from './app/AppRouter.tsx'
import { ThemeProvider } from './app/providers/ThemeProvider'
import { installApiAuthFetch } from './features/auth/installApiAuthFetch'
import i18n from './i18n/i18n'
import './index.css'

installApiAuthFetch()
syncDevExtremeLocale('es')

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <I18nextProvider i18n={i18n}>
      <ThemeProvider>
        <AppRouter />
      </ThemeProvider>
    </I18nextProvider>
  </StrictMode>,
)
