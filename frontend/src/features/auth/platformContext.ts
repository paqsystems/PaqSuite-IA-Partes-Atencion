import {
  isDevOrNonCanonicalHostname,
  persistClienteCode,
  readPersistedClienteCode,
  resolveClienteCode,
} from '@paqsuite/react-core'
import { getAuthSession } from './authSessionStore'
import { resolveLandingClienteCode } from './partesCanonicalCliente'

const browserPersistence = {
  getCookie: (name: string) => {
    if (typeof document === 'undefined') {
      return null
    }
    const match = document.cookie.match(new RegExp(`(?:^|; )${name}=([^;]*)`))
    return match ? decodeURIComponent(match[1]) : null
  },
  setCookie: (name: string, value: string) => {
    if (typeof document === 'undefined') {
      return
    }
    document.cookie = `${name}=${encodeURIComponent(value)}; path=/; SameSite=Lax`
  },
  getSession: (key: string) => {
    if (typeof sessionStorage === 'undefined') {
      return null
    }
    return sessionStorage.getItem(key)
  },
  setSession: (key: string, value: string) => {
    if (typeof sessionStorage === 'undefined') {
      return
    }
    sessionStorage.setItem(key, value)
  },
}

export function resolvePlatformCliente(overrideTenant?: string): string {
  const hostname =
    typeof window !== 'undefined' ? window.location.hostname : 'localhost'
  const search = typeof window !== 'undefined' ? window.location.search : ''
  const isDev = import.meta.env.DEV

  const persisted = readPersistedClienteCode(browserPersistence)
  const landingCliente = resolveLandingClienteCode({
    hostname,
    search,
    overrideTenant,
  })

  const cliente = resolveClienteCode({
    isDevOrNonCanonicalUrl: isDevOrNonCanonicalHostname(hostname, isDev),
    queryCliente: landingCliente,
    cookieCliente: persisted.cookieCliente,
    sessionCliente: persisted.sessionCliente,
  })

  return persistClienteCode(cliente, browserPersistence)
}

export function buildAuthPlatformHeaders(tenantOverride?: string) {
  const session = getAuthSession()
  const cliente = resolvePlatformCliente(tenantOverride)

  return {
    cliente,
    companyId: session?.activeCompanyId ?? session?.empresas[0]?.id,
    tenancy: session?.tenancy,
  }
}
