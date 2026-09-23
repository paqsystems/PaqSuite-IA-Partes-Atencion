import i18n from 'i18next'
import { initReactI18next } from 'react-i18next'
import {
  getGuestLocale,
  normalizeLocale,
  setGuestLocale,
  syncDevExtremeLocale,
  type LocaleCode,
} from '@paqsuite/react-core'
import commonEs from './locales/es/common.json'
import commonEn from './locales/en/common.json'
import commonPt from './locales/pt/common.json'
import commonFr from './locales/fr/common.json'
import commonIt from './locales/it/common.json'

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

syncDevExtremeLocale(initialLocale)

/** Aplica idioma guest: i18n + DX + persistencia local. */
export async function applyGuestLocale(next: LocaleCode): Promise<void> {
  setGuestLocale(next)
  await i18n.changeLanguage(next)
  syncDevExtremeLocale(next)
}

export default i18n
