import {
  bootstrapClienteFromWindow,
  buildPlatformHeaders,
  isNativeApp,
  persistClienteCode,
  readPersistedClienteCode,
  type BuildPlatformHeadersInput,
} from '@paqsuite/react-core'
import { getAuthSession, invalidateSessionIfClienteMismatch } from './authSessionStore'
import {
  clienteCodeFromSearchParams,
  isVercelFrontDoorHostname,
  resolveLandingClienteCode,
  shouldHonorLandingCliente,
} from './partesCanonicalCliente'

/**
 * En Vite/localhost el SDK fuerza DEMO. Si el host define
 * `VITE_TENANT_DEFAULT_CLIENT`, ese valor (o `?cliente=`) gana.
 */
export function resolveLocalEnvClienteOverride(
  envDefault: string,
  queryCliente: string | null,
): string | null {
  const env = envDefault.trim()
  if (env === '') {
    return null
  }
  const query = queryCliente?.trim() ?? ''
  return query !== '' ? query : env
}

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

function windowHostname(): string {
  return typeof window !== 'undefined' ? window.location.hostname : 'localhost'
}

function windowSearch(): string {
  return typeof window !== 'undefined' ? window.location.search : ''
}

function windowReferrer(): string {
  return typeof document !== 'undefined' ? document.referrer : ''
}

/**
 * Landing canónico / Vercel (SoT MONO) y luego `bootstrapClienteFromWindow`.
 * Llamar en `main.tsx` antes del Router.
 */
export function bootstrapPlatformCliente(): string {
  const cliente = resolvePlatformCliente()
  invalidateSessionIfClienteMismatch(cliente)
  return cliente
}

export function resolvePlatformCliente(overrideTenant?: string): string {
  const hostname = windowHostname()
  const search = windowSearch()
  const isDev = import.meta.env.DEV
  const explicit = overrideTenant?.trim()

  if (explicit) {
    return persistClienteCode(explicit, browserPersistence)
  }

  if (!isNativeApp()) {
    const landingCliente = resolveLandingClienteCode({
      hostname,
      search,
      referrer: windowReferrer(),
      overrideTenant,
    })
    if (landingCliente && shouldHonorLandingCliente({ hostname, isDevBuild: isDev })) {
      persistClienteCode(landingCliente, browserPersistence)
    }
  }

  const localEnvCliente = resolveLocalEnvClienteOverride(
    String(import.meta.env.VITE_TENANT_DEFAULT_CLIENT ?? ''),
    clienteCodeFromSearchParams(search),
  )
  const host = hostname.trim().toLowerCase()
  const isLocalHost =
    host === 'localhost' ||
    host === '127.0.0.1' ||
    host === '0.0.0.0' ||
    host.endsWith('.local')
  if (localEnvCliente && !isNativeApp() && isLocalHost) {
    return persistClienteCode(localEnvCliente, browserPersistence)
  }

  const vercelFrontDoor = isVercelFrontDoorHostname(hostname)
  return bootstrapClienteFromWindow({
    persistence: browserPersistence,
    isDevBuild: vercelFrontDoor ? false : isDev,
    hostname,
    search,
  })
}

/** Input para `apiRequest.platform` / páginas SDK. `companyId` solo MULTI. */
export function buildAuthPlatformHeaders(tenantOverride?: string): BuildPlatformHeadersInput {
  const session = getAuthSession()
  const cliente = resolvePlatformCliente(tenantOverride)
  const tenancy = session?.tenancy ?? 'single'
  const input: BuildPlatformHeadersInput = {
    cliente,
    tenancy,
  }

  if (tenancy === 'multi') {
    const companyId = session?.activeCompanyId ?? session?.empresas[0]?.id
    if (companyId !== undefined && companyId !== null) {
      input.companyId = companyId
    }
  }

  return input
}

/** Headers HTTP GEN-19 (`X-Paq-Cliente`, `X-Company-Id` si MULTI). */
export function buildAuthHttpHeaders(tenantOverride?: string): Record<string, string> {
  return buildPlatformHeaders(buildAuthPlatformHeaders(tenantOverride))
}
