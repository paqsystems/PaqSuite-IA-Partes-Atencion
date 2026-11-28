import type { AuthSession } from '../../auth/authTypes'
import { partesMobilePolicy } from './partesMobilePolicy'

const nativeHomeCatalog: Array<{
  path: string
  skipForCliente?: boolean
}> = [
  { path: '/partes/carga', skipForCliente: true },
  { path: '/partes/informes/consulta-detallada' },
  { path: '/partes/informes/paquete-horas' },
]

export function resolveNativeMobileHomePath(session: AuthSession | null): string {
  const isCliente = session?.partes?.tipoFuncional === 'cliente'
  for (const entry of nativeHomeCatalog) {
    if (entry.skipForCliente && isCliente) {
      continue
    }
    if (partesMobilePolicy.isAllowed(entry.path)) {
      return entry.path
    }
  }
  return '/partes/informes/consulta-detallada'
}