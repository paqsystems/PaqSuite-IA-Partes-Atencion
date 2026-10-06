import {
  readViteAuthHeroEnv,
  resolveAuthHeroFromProductI18n,
  resolveProductBrandingString,
  productTitleI18nKey,
  type AuthHeroConfig,
  type LocaleCode,
} from '@paqsuite/react-core'
import { productI18nCatalogs } from './productI18n'

const fallbackTitle = 'Partes de Atención'

export function resolveAppProductTitle(locale: LocaleCode): string {
  return resolveProductBrandingString(
    locale,
    productI18nCatalogs,
    productTitleI18nKey,
    fallbackTitle,
  )
}

export function resolveAppAuthHero(locale: LocaleCode): AuthHeroConfig {
  const envHero = readViteAuthHeroEnv()

  return resolveAuthHeroFromProductI18n({
    locale,
    productCatalogs: productI18nCatalogs,
    companyLogoUrl: envHero.companyLogoUrl,
    fallbackTitle,
    fallbackTagline: '',
  })
}
