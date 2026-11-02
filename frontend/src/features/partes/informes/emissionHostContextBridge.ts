import type { ConsultaDetalladaHostContext } from './consultaDetalladaHostContext'
import type { PaqueteHorasHostContext } from './paqueteHorasHostContext'

export type EmissionHostContextSnapshot = ConsultaDetalladaHostContext | PaqueteHorasHostContext

let emissionHostContextSnapshot: EmissionHostContextSnapshot | null = null

export function setEmissionHostContextSnapshot(hostContext: EmissionHostContextSnapshot): void {
  emissionHostContextSnapshot = hostContext
}

export function getEmissionHostContextSnapshot(): EmissionHostContextSnapshot | null {
  return emissionHostContextSnapshot
}

export function isEmissionHostContextUrl(url: string, method: string): boolean {
  if (method.toUpperCase() !== 'POST') {
    return false
  }
  return url.includes('/api/v1/emissions/jobs') || url.includes('/api/v1/emissions/preview')
}
