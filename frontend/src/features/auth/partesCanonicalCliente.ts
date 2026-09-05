const partesCanonicalHostSuffixes = [
  '.partesatencion.paqsystems.com',
  '.partesatenciones.paqsystems.com',
]

const reservedCanonicalLabels = new Set([
  'www',
  'api',
  'backend',
  'backenddev',
  'frontend',
  'mail',
])

export function clienteCodeFromSearchParams(search: string): string | null {
  const params = new URLSearchParams(
    search.startsWith('?') ? search.slice(1) : search,
  )
  const raw = params.get('cliente')?.trim() ?? ''
  return raw === '' ? null : raw
}

export function isVercelFrontDoorHostname(hostname: string): boolean {
  const host = hostname.trim().toLowerCase()
  return host === 'vercel.app' || host.endsWith('.vercel.app')
}

function stripWwwPrefix(hostname: string): string {
  const host = hostname.trim().toLowerCase()
  return host.startsWith('www.') ? host.slice(4) : host
}

/**
 * Primer label de `{cliente}.partesatencion.paqsystems.com` (deploy canónico).
 * Develop: demo.… → DEMO. Prod: paq.… → PAQ. Labels reservados → null.
 * Acepta `www.{cliente}.partesatencion.paqsystems.com`.
 */
export function clienteCodeFromCanonicalHostname(hostname: string): string | null {
  const host = stripWwwPrefix(hostname)
  const suffix = partesCanonicalHostSuffixes.find((item) => host.endsWith(item))
  if (!suffix) {
    return null
  }

  const label = host.slice(0, host.length - suffix.length)
  if (label === '' || label.includes('.') || reservedCanonicalLabels.has(label)) {
    return null
  }

  return label
}

export function clienteCodeFromReferrer(referrer: string): string | null {
  const raw = referrer.trim()
  if (raw === '') {
    return null
  }
  try {
    return clienteCodeFromCanonicalHostname(new URL(raw).hostname)
  } catch {
    return null
  }
}

/**
 * Redirect Plesk/Apache a `*.vercel.app?cliente=` pierde el host canónico.
 * Query → hostname → referrer (si el panel recorta el query string).
 */
export function resolveLandingClienteCode(input: {
  hostname: string
  search: string
  referrer?: string
  overrideTenant?: string
}): string | null {
  const fromOverride = input.overrideTenant?.trim() ?? ''
  if (fromOverride !== '') {
    return fromOverride
  }

  return (
    clienteCodeFromSearchParams(input.search) ??
    clienteCodeFromCanonicalHostname(input.hostname) ??
    clienteCodeFromReferrer(input.referrer ?? '')
  )
}

/** En `*.vercel.app` el query/referrer debe ganar; no forzar DEMO del SDK. */
export function shouldHonorLandingCliente(input: {
  hostname: string
  isDevBuild: boolean
}): boolean {
  if (isVercelFrontDoorHostname(input.hostname)) {
    return true
  }
  return !input.isDevBuild
}
