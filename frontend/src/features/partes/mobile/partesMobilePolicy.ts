type MenuItemLike = {
  routeName?: string | null
  menuKey?: string
  children?: MenuItemLike[]
}

/** Allowlist exacta native Partes (TR-007). */
export const partesMobileAllowlist = [
  '/partes',
  '/partes/consulta',
  '/partes/carga',
  '/partes/informes/paquete-horas',
  '/dashboard',
  '/change-password',
  '/select-empresa',
] as const

const partesMobileDenylistPrefixes = [
  '/archivos/partes',
  '/partes/proceso-masivo',
  '/partes/carga-diaria',
  '/partes/informes/consulta-detallada',
  '/partes/informes/consultas-agrupadas',
  '/admin',
  '/parametros',
] as const

function isDenied(routeName: string): boolean {
  return partesMobileDenylistPrefixes.some(
    (entry) => routeName === entry || routeName.startsWith(`${entry}/`)
  )
}

export function isPartesMobileRouteAllowed(routeName: string): boolean {
  const route = routeName.trim()
  if (!route || isDenied(route)) {
    return false
  }
  return (partesMobileAllowlist as readonly string[]).includes(route)
}

function filterMenuItems<T extends MenuItemLike>(items: T[]): T[] {
  const result: T[] = []
  for (const item of items) {
    const children = item.children ? filterMenuItems(item.children as T[]) : []
    const route = item.routeName?.trim() || ''
    const keepProcess = route !== '' && isPartesMobileRouteAllowed(route)
    if (keepProcess || children.length > 0) {
      result.push({ ...item, children })
    }
  }
  return result
}

export const partesMobilePolicy = {
  isAllowed: (routeName: string) => isPartesMobileRouteAllowed(routeName),
  filterItems: filterMenuItems,
}
