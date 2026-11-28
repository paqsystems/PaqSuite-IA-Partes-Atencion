/** Menú web `carga-diaria` → ruta native v1 (RN-MV1-TR-09). */
export function mapNativeMenuRoute(routeName: string): string {
  if (routeName === '/partes/carga-diaria' || routeName.startsWith('/partes/carga-diaria/')) {
    return '/partes/carga'
  }
  return routeName
}