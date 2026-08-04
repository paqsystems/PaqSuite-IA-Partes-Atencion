import {
  readViteAuthHeroEnv,
  resolveAuthHeroConfig,
  type AuthHeroConfig,
} from '@paqsuite/react-core'

type TranslateFn = (key: string) => string

/**
 * Hero auth: título/tagline desde i18n (responde al cambio de idioma).
 * Logo sigue siendo build-time (env). No usar VITE_AUTH_PRODUCT_TAGLINE como
 * fuente runtime si el producto es multilingual — pisa el locale.
 */
export function resolvePartesAuthHero(t: TranslateFn): AuthHeroConfig {
  const env = readViteAuthHeroEnv()
  return resolveAuthHeroConfig({
    productTitle: t('login.title'),
    productTagline: t('login.subtitle'),
    companyLogoUrl: env.companyLogoUrl,
    fallbackTitle: 'Partes de Atención',
    fallbackTagline: t('login.subtitle'),
  })
}
