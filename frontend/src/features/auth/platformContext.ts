import {
  isDevOrNonCanonicalHostname,
  isNativeApp,
  persistClienteCode,
  readPersistedClienteCode,
  resolveClienteCode,
} from '@paqsuite/react-core'
import { getAuthSession, invalidateSessionIfClienteMismatch } from './authSessionStore'
import {
  isVercelFrontDoorHostname,
  resolveLandingClienteCode,
  shouldHonorLandingCliente,
} from './partesCanonicalCliente'

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

export function readStoredClienteCode(): string {
  const persisted = readPersistedClienteCode(browserPersistence)
  return (persisted.sessionCliente || persisted.cookieCliente || '').trim()
}

/**
 * Persiste `{cliente}` mientras la URL de landing todavía tiene `?cliente=`
 * (antes de que React Router reemplace `/` → `/login` y pierda el query).
 * Si había sesión de otro tenant en el mismo vercel.app, la invalida.
 */
export function bootstrapPlatformCliente(): string {
  const cliente = resolvePlatformCliente()
  invalidateSessionIfClienteMismatch(cliente)
  return cliente
}

export function resolvePlatformCliente(overrideTenant?: string): string {
  const hostname =
    typeof window !== 'undefined' ? window.location.hostname : 'localhost'
  const search = typeof window !== 'undefined' ? window.location.search : ''
  const referrer = typeof document !== 'undefined' ? document.referrer : ''
  const isDev = import.meta.env.DEV
  const persisted = readPersistedClienteCode(browserPersistence)
  const explicit =
    overrideTenant?.trim() ||
    (isNativeApp() ? persisted.sessionCliente || persisted.cookieCliente : null)

  if (explicit) {
    return persistClienteCode(explicit, browserPersistence)
  }

  const landingCliente = resolveLandingClienteCode({
    hostname,
    search,
    referrer,
    overrideTenant,
  })

  if (landingCliente && shouldHonorLandingCliente({ hostname, isDevBuild: isDev })) {
    return persistClienteCode(landingCliente, browserPersistence)
  }

  const vercelFrontDoor = isVercelFrontDoorHostname(hostname)
  const cliente = resolveClienteCode({
    isDevOrNonCanonicalUrl:
      !vercelFrontDoor && isDevOrNonCanonicalHostname(hostname, isDev),
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
