import { useCallback } from 'react'
import {
  MenuSidebar,
  isNativeApp,
  useMenuPresentation,
  type BuildPlatformHeadersInput,
  type MenuNode,
} from '@paqsuite/react-core'
import { useTranslation } from 'react-i18next'
import { getAuthSession, getAuthToken } from '../auth/authSessionStore'
import { partesMobilePolicy } from './mobile/partesMobilePolicy'

/** Exportado para tests unitarios. */
export function filterMenuForCliente(nodes: MenuNode[]): MenuNode[] {
  return filterMenuNodes(nodes, (node) => {
    const route = node.routeName ?? ''
    if (route.startsWith('/archivos/partes')) {
      return true
    }
    if (route === '/partes/carga-diaria' || route === '/partes/proceso-masivo' || route === '/partes/carga') {
      return true
    }
    if (route.startsWith('/admin/') || route.startsWith('/parametros/')) {
      return true
    }
    if (node.menuKey === 'seguridad' || node.menuKey === 'parametros') {
      return true
    }
    return false
  })
}

/** Oculta proceso masivo si el actor no es supervisor. */
export function filterMenuForNonSupervisor(nodes: MenuNode[]): MenuNode[] {
  return filterMenuNodes(
    nodes,
    (node) => node.routeName === '/partes/proceso-masivo' || node.menuKey === 'partes_proceso_masivo',
  )
}

function filterMenuNodes(
  nodes: MenuNode[],
  shouldHideProcess: (node: MenuNode) => boolean,
): MenuNode[] {
  return nodes
    .map((node) => {
      const children = filterMenuNodes(node.children ?? [], shouldHideProcess)
      if (node.nodeType === 'process' && shouldHideProcess(node)) {
        return null
      }
      if (node.nodeType !== 'process' && children.length === 0 && (node.children?.length ?? 0) > 0) {
        return null
      }
      return { ...node, children }
    })
    .filter((node): node is MenuNode => node !== null)
}

type PartesMenuSidebarProps = {
  platform: BuildPlatformHeadersInput
  className?: string
  presentation: ReturnType<typeof useMenuPresentation>
  onNavigate?: (routeName: string) => void
  onItemsLoaded?: (items: MenuNode[]) => void
}

/** Menú shell GEN-07 + filtros AC cliente / supervisor / mobile. */
export function PartesMenuSidebar({
  platform,
  className,
  presentation,
  onNavigate,
  onItemsLoaded,
}: PartesMenuSidebarProps) {
  const { t } = useTranslation()
  const session = getAuthSession()
  const hideMaestros = session?.partes?.tipoFuncional === 'cliente'
  const hideMasivo = !session?.partes?.esSupervisor

  const transformItems = useCallback(
    (raw: MenuNode[]) => {
      let next = raw
      if (hideMaestros) {
        next = filterMenuForCliente(next)
      } else if (hideMasivo) {
        next = filterMenuForNonSupervisor(next)
      }
      if (isNativeApp()) {
        next = partesMobilePolicy.filterItems(next)
      }
      return next
    },
    [hideMaestros, hideMasivo],
  )

  return (
    <MenuSidebar
      platform={platform}
      className={className}
      accessToken={getAuthToken()}
      presentation={presentation}
      transformItems={transformItems}
      onItemsLoaded={onItemsLoaded}
      onNavigate={onNavigate}
      labels={{
        controls: t('menu.controls'),
        toggleVisible: t('menu.toggleVisible'),
        toggleExpand: t('menu.toggleExpand'),
        expandAll: t('menu.expandAll'),
        collapseAll: t('menu.collapseAll'),
        toggleDisplayMode: t('menu.toggleDisplayMode'),
        viewOperational: t('menu.viewOperational'),
        viewAllBranches: t('menu.viewAllBranches'),
        searchPlaceholder: t('menu.searchPlaceholder'),
        empty: t('menu.empty'),
        loadError: t('menu.loadError'),
        retry: t('menu.retry'),
      }}
    />
  )
}
