/**
 * Fallback runtime cuando el build de Vercel no embebe `VITE_API_BASE_URL`.
 * Sin esto, `/api/v1/*` pega al mismo origen (SPA) y las descargas binarias
 * terminan siendo `index.html` renombrado a `.xlsx`.
 */
export function resolveWebApiBaseUrlFromHostname(hostname: string): string | null {
  const host = hostname.trim().toLowerCase()
  if (host === '' || host === 'localhost' || host === '127.0.0.1') {
    return null
  }

  const isDevelopFront =
    host.includes('dev') ||
    host.includes('desarrollo') ||
    host === 'partesatencionpaqsystemsdev.vercel.app'

  if (isDevelopFront) {
    return 'https://backenddevpartesatencionpaqsystems.on-forge.com/api/v1'
  }

  const isPartesFront =
    host.endsWith('.vercel.app') ||
    host.endsWith('.partesatencion.paqsystems.com') ||
    host.endsWith('.partesatenciones.paqsystems.com')

  if (isPartesFront) {
    return 'https://backendpartesatencionpaqsystems.on-forge.com/api/v1'
  }

  return null
}

export function resolveWebApiBaseUrl(envBaseUrl?: string | null): string | undefined {
  const fromEnv = envBaseUrl?.trim() ?? ''
  if (fromEnv !== '') {
    return fromEnv
  }

  if (typeof window === 'undefined') {
    return undefined
  }

  return resolveWebApiBaseUrlFromHostname(window.location.hostname) ?? undefined
}
