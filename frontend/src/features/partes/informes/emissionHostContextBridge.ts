import type { ConsultaDetalladaHostContext } from './consultaDetalladaHostContext'

let emissionHostContextSnapshot: ConsultaDetalladaHostContext | null = null

export function setEmissionHostContextSnapshot(hostContext: ConsultaDetalladaHostContext): void {
  emissionHostContextSnapshot = hostContext
}

export function getEmissionHostContextSnapshot(): ConsultaDetalladaHostContext | null {
  return emissionHostContextSnapshot
}

export function isEmissionHostContextUrl(url: string, method: string): boolean {
  if (method.toUpperCase() !== 'POST') {
    return false
  }
  return url.includes('/api/v1/emissions/jobs') || url.includes('/api/v1/emissions/preview')
}
