import type { LocaleCode, MenuSidebarLabels } from '@paqsuite/react-core'

/** Chrome del sidebar GEN-07 (toolbar, búsqueda, estados). */
const es: Record<string, string> = {
  'menu.controls': 'Controles de menú',
  'menu.toggleVisible': 'Mostrar u ocultar menú',
  'menu.toggleExpand': 'Expandir o contraer ramas',
  'menu.expandAll': 'Expandir todas las ramas',
  'menu.collapseAll': 'Contraer todas las ramas',
  'menu.toggleDisplayMode': 'Con o sin ramificación',
  'menu.viewOperational': 'Ver solo opciones operativas',
  'menu.viewAllBranches': 'Ver todas las ramas',
  'menu.searchPlaceholder': 'Buscar en el menú…',
  'menu.empty': 'No hay opciones de menú disponibles.',
  'menu.loadError': 'No se pudo cargar el menú.',
  'menu.retry': 'Reintentar',
}

const en: Record<string, string> = {
  'menu.controls': 'Menu controls',
  'menu.toggleVisible': 'Show or hide menu',
  'menu.toggleExpand': 'Expand or collapse branches',
  'menu.expandAll': 'Expand all branches',
  'menu.collapseAll': 'Collapse all branches',
  'menu.toggleDisplayMode': 'With or without branching',
  'menu.viewOperational': 'Show operational options only',
  'menu.viewAllBranches': 'Show all branches',
  'menu.searchPlaceholder': 'Search menu…',
  'menu.empty': 'No menu options available.',
  'menu.loadError': 'Failed to load menu.',
  'menu.retry': 'Retry',
}

const pt: Record<string, string> = {
  'menu.controls': 'Controles do menu',
  'menu.toggleVisible': 'Mostrar ou ocultar o menu',
  'menu.toggleExpand': 'Expandir ou recolher ramos',
  'menu.expandAll': 'Expandir todos os ramos',
  'menu.collapseAll': 'Recolher todos os ramos',
  'menu.toggleDisplayMode': 'Com ou sem ramificação',
  'menu.viewOperational': 'Ver apenas opções operacionais',
  'menu.viewAllBranches': 'Ver todos os ramos',
  'menu.searchPlaceholder': 'Buscar no menu…',
  'menu.empty': 'Não há opções de menu disponíveis.',
  'menu.loadError': 'Erro ao carregar o menu.',
  'menu.retry': 'Tentar novamente',
}

const fr: Record<string, string> = {
  'menu.controls': 'Contrôles du menu',
  'menu.toggleVisible': 'Afficher ou masquer le menu',
  'menu.toggleExpand': 'Développer ou réduire les branches',
  'menu.expandAll': 'Développer toutes les branches',
  'menu.collapseAll': 'Réduire toutes les branches',
  'menu.toggleDisplayMode': 'Avec ou sans ramification',
  'menu.viewOperational': 'Voir uniquement les options opérationnelles',
  'menu.viewAllBranches': 'Voir toutes les branches',
  'menu.searchPlaceholder': 'Rechercher dans le menu…',
  'menu.empty': 'Aucune option de menu disponible.',
  'menu.loadError': 'Erreur lors du chargement du menu.',
  'menu.retry': 'Réessayer',
}

const it: Record<string, string> = {
  'menu.controls': 'Controlli del menu',
  'menu.toggleVisible': 'Mostra o nascondi il menu',
  'menu.toggleExpand': 'Espandi o comprimi i rami',
  'menu.expandAll': 'Espandi tutti i rami',
  'menu.collapseAll': 'Comprimi tutti i rami',
  'menu.toggleDisplayMode': 'Con o senza ramificazione',
  'menu.viewOperational': 'Mostra solo opzioni operative',
  'menu.viewAllBranches': 'Mostra tutti i rami',
  'menu.searchPlaceholder': 'Cerca nel menu…',
  'menu.empty': 'Nessuna opzione di menu disponibile.',
  'menu.loadError': 'Errore nel caricamento del menu.',
  'menu.retry': 'Riprova',
}

export const shellI18nCatalogs: Record<LocaleCode, Record<string, string>> = {
  es,
  en,
  pt,
  fr,
  it,
}

export type AppTranslateFn = (key: string, fallback?: string) => string

/** Traductor del host: catálogo shell + claves de producto (`productI18nCatalogs`). */
export function createAppTranslator(
  locale: LocaleCode,
  productCatalog?: Record<string, string>,
): AppTranslateFn {
  const shellCatalog = shellI18nCatalogs[locale] ?? shellI18nCatalogs.es
  return (key: string, fallback?: string) => {
    const productHit = productCatalog?.[key]
    if (productHit) {
      return productHit
    }
    const shellHit = shellCatalog[key]
    if (shellHit) {
      return shellHit
    }
    if (fallback) {
      return fallback
    }
    return key
  }
}

export function buildMenuSidebarLabels(t: AppTranslateFn): Partial<MenuSidebarLabels> {
  return {
    controls: t('menu.controls', 'Controles de menú'),
    toggleVisible: t('menu.toggleVisible', 'Mostrar u ocultar menú'),
    toggleExpand: t('menu.toggleExpand', 'Expandir o contraer ramas'),
    expandAll: t('menu.expandAll', 'Expandir todas las ramas'),
    collapseAll: t('menu.collapseAll', 'Contraer todas las ramas'),
    toggleDisplayMode: t('menu.toggleDisplayMode', 'Con o sin ramificación'),
    viewOperational: t('menu.viewOperational', 'Ver solo opciones operativas'),
    viewAllBranches: t('menu.viewAllBranches', 'Ver todas las ramas'),
    searchPlaceholder: t('menu.searchPlaceholder', 'Buscar en el menú…'),
    empty: t('menu.empty', 'No hay opciones de menú disponibles.'),
    loadError: t('menu.loadError', 'No se pudo cargar el menú.'),
    retry: t('menu.retry', 'Reintentar'),
  }
}

export type ShellOutletContext = {
  locale: LocaleCode
  t: AppTranslateFn
}
