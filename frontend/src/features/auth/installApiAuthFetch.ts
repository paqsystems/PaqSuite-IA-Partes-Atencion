import { clienteHeaderName } from '@paqsuite/react-core'
import { getAuthToken } from './authSessionStore'
import { resolvePlatformCliente } from './platformContext'
import {
  getEmissionHostContextSnapshot,
  isEmissionHostContextUrl,
} from '../partes/informes/emissionHostContextBridge'

/**
 * Inyecta Authorization + X-Paq-Cliente en fetch hacia /api/*
 * para componentes GEN (Chat / LLM) que llaman apiRequest sin headers del host.
 */
export function installApiAuthFetch(): void {
  if (typeof window === 'undefined' || (window as Window & { __paqApiAuthFetch?: boolean }).__paqApiAuthFetch) {
    return
  }

  const originalFetch = window.fetch.bind(window)

  window.fetch = async (input: RequestInfo | URL, init?: RequestInit): Promise<Response> => {
    const url =
      typeof input === 'string'
        ? input
        : input instanceof URL
          ? input.toString()
          : input.url

    const isApi = url.includes('/api/')
    if (!isApi) {
      return originalFetch(input, init)
    }

    const headers = new Headers(init?.headers ?? (input instanceof Request ? input.headers : undefined))

    if (!headers.has('Accept')) {
      headers.set('Accept', 'application/json')
    }

    const token = getAuthToken()
    if (token && !headers.has('Authorization')) {
      headers.set('Authorization', `Bearer ${token}`)
    }

    if (!headers.has(clienteHeaderName)) {
      headers.set(clienteHeaderName, resolvePlatformCliente())
    }

    const method = String(init?.method ?? (input instanceof Request ? input.method : 'GET'))
    if (isEmissionHostContextUrl(url, method)) {
      const snapshot = getEmissionHostContextSnapshot()
      if (snapshot) {
        let bodyObj: Record<string, unknown> = {}
        const rawBody = init?.body
        if (typeof rawBody === 'string' && rawBody !== '') {
          try {
            const parsed = JSON.parse(rawBody) as unknown
            if (parsed && typeof parsed === 'object') {
              bodyObj = parsed as Record<string, unknown>
            }
          } catch {
            bodyObj = {}
          }
        }
        bodyObj.hostContext = snapshot
        if (!headers.has('Content-Type')) {
          headers.set('Content-Type', 'application/json')
        }
        return originalFetch(input, { ...init, headers, body: JSON.stringify(bodyObj) })
      }
    }

    return originalFetch(input, { ...init, headers })
  }

  ;(window as Window & { __paqApiAuthFetch?: boolean }).__paqApiAuthFetch = true
}
