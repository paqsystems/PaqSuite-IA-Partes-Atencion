import i18n from 'i18next'
import { initReactI18next } from 'react-i18next'
import * as paqsuiteReactCore from '@paqsuite/react-core'
import {
  getGuestLocale,
  normalizeLocale,
  registerGridI18nResources,
  setGuestLocale,
  syncDevExtremeLocale,
  type LocaleCode,
} from '@paqsuite/react-core'
import commonEs from './locales/es/common.json'
import commonEn from './locales/en/common.json'
import commonPt from './locales/pt/common.json'
import commonFr from './locales/fr/common.json'
import commonIt from './locales/it/common.json'
import { empresasAdminHostFallback } from './empresasAdminHostFallback'

const initialLocale: LocaleCode = normalizeLocale(getGuestLocale()) ?? 'es'

// Recarga de catálogos JSON (login.hint y locales) — Vite no siempre HMR-ea .json.

void i18n.use(initReactI18next).init({
  resources: {
    es: { common: commonEs },
    en: { common: commonEn },
    pt: { common: commonPt },
    fr: { common: commonFr },
    it: { common: commonIt },
  },
  lng: initialLocale,
  fallbackLng: 'es',
  defaultNS: 'common',
  // Claves planas con puntos (`partes.smartCapture.*`, `chatAssistant.*`, …).
  keySeparator: false,
  nsSeparator: ':',
  interpolation: { escapeValue: false },
})

registerGridI18nResources(i18n, 'common')
// Fallback host con overwrite: el SDK EmpresasAdmin exige estas claves vía prop `t`
// (sin `t` el SDK hace `(key) => key` y se ven claves crudas en UI).
for (const [locale, bundle] of Object.entries(empresasAdminHostFallback)) {
  i18n.addResourceBundle(locale, 'common', bundle, true, true)
}
const appearancePaqsuiteI18nCatalogs = (
  paqsuiteReactCore as {
    appearancePaqsuiteI18nCatalogs?: Record<string, Record<string, string>>
  }
).appearancePaqsuiteI18nCatalogs
if (appearancePaqsuiteI18nCatalogs) {
  for (const [locale, bundle] of Object.entries(appearancePaqsuiteI18nCatalogs)) {
    i18n.addResourceBundle(locale, 'common', bundle, true, true)
  }
}
const registerEmpresasAdminI18nResources = (
  paqsuiteReactCore as {
    registerEmpresasAdminI18nResources?: (
      instance: typeof i18n,
      namespace: string,
    ) => void
  }
).registerEmpresasAdminI18nResources
// SDK completo (incl. appearance.paqsuite.*) sobrescribe cuando el export existe.
registerEmpresasAdminI18nResources?.(i18n, 'common')
syncDevExtremeLocale(initialLocale)

/** Aplica idioma guest: i18n + DX + persistencia local. */
export async function applyGuestLocale(next: LocaleCode): Promise<void> {
  setGuestLocale(next)
  await i18n.changeLanguage(next)
  syncDevExtremeLocale(next)
}

export default i18n
