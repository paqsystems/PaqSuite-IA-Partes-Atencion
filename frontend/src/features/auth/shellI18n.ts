import type { LocaleCode, MenuSidebarLabels } from '@paqsuite/react-core'
import i18n from '../../i18n/i18n'

export type AppTranslateFn = (key: string, fallback?: string) => string

/** Traductor del host: catálogo producto (`productI18n`) + i18n. */
export function createAppTranslator(
  locale: LocaleCode,
  productCatalog?: Record<string, string>,
): AppTranslateFn {
  return (key: string, fallback?: string) => {
    const productHit = productCatalog?.[key]
    if (productHit) {
      return productHit
    }
    const translated = i18n.t(key, { lng: locale, defaultValue: fallback ?? key })
    if (translated && translated !== key) {
      return String(translated)
    }
    if (fallback) {
      return fallback
    }
    return key
  }
}

const menuSidebarLabelKeys: Record<keyof MenuSidebarLabels, string> = {
  controls: 'menu.controls',
  toggleVisible: 'menu.toggleVisible',
  toggleExpand: 'menu.toggleExpand',
  expandAll: 'menu.expandAll',
  collapseAll: 'menu.collapseAll',
  toggleDisplayMode: 'menu.toggleDisplayMode',
  viewOperational: 'menu.viewOperational',
  viewAllBranches: 'menu.viewAllBranches',
  searchPlaceholder: 'menu.searchPlaceholder',
  empty: 'menu.empty',
  loadError: 'menu.loadError',
  retry: 'menu.retry',
}

/**
 * Labels chrome GEN-07 vía `t('menu.*')`.
 * El pin 2.4.24-beta.1 no exporta `menuSidebarLabelsFromT`; helper de host equivalente.
 */
export function menuSidebarLabelsFromT(t: (key: string) => string): MenuSidebarLabels {
  return {
    controls: t(menuSidebarLabelKeys.controls),
    toggleVisible: t(menuSidebarLabelKeys.toggleVisible),
    toggleExpand: t(menuSidebarLabelKeys.toggleExpand),
    expandAll: t(menuSidebarLabelKeys.expandAll),
    collapseAll: t(menuSidebarLabelKeys.collapseAll),
    toggleDisplayMode: t(menuSidebarLabelKeys.toggleDisplayMode),
    viewOperational: t(menuSidebarLabelKeys.viewOperational),
    viewAllBranches: t(menuSidebarLabelKeys.viewAllBranches),
    searchPlaceholder: t(menuSidebarLabelKeys.searchPlaceholder),
    empty: t(menuSidebarLabelKeys.empty),
    loadError: t(menuSidebarLabelKeys.loadError),
    retry: t(menuSidebarLabelKeys.retry),
  }
}

export type ShellOutletContext = {
  locale: LocaleCode
  t: AppTranslateFn
}
