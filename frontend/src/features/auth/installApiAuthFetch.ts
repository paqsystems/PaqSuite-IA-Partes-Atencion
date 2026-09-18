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

    const isBinaryDownload =
      /\/excel-import\/processes\/[^/?#]+\/template(?:\?|#|$)/.test(url) ||
      /\/excel-import\/batches\/[^/?#]+\/errors\/export(?:\?|#|$)/.test(url)

    if (!headers.has('Accept')) {
      headers.set(
        'Accept',
        isBinaryDownload
          ? 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/octet-stream,*/*'
          : 'application/json'
      )
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

    const response = await originalFetch(input, { ...init, headers })

    if (isBinaryDownload && response.ok) {
      const buffer = await response.arrayBuffer()
      const responseHeaders = new Headers(response.headers)
      responseHeaders.set(
        'Content-Type',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      )
      responseHeaders.delete('Content-Encoding')

      return new Response(buffer, {
        status: response.status,
        statusText: response.statusText,
        headers: responseHeaders,
      })
    }

    return response
  }

  ;(window as Window & { __paqApiAuthFetch?: boolean }).__paqApiAuthFetch = true
}
