const partesCanonicalHostSuffix = '.partesatencion.paqsystems.com'

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

/**
 * Primer label de `{cliente}.partesatencion.paqsystems.com` (deploy canónico).
 * Develop: demo.… → DEMO. Prod: paq.… → PAQ. Labels reservados → null.
 */
export function clienteCodeFromCanonicalHostname(hostname: string): string | null {
  const host = hostname.trim().toLowerCase()
  if (!host.endsWith(partesCanonicalHostSuffix)) {
    return null
  }

  const label = host.slice(0, host.length - partesCanonicalHostSuffix.length)
  if (label === '' || label.includes('.') || reservedCanonicalLabels.has(label)) {
    return null
  }

  return label
}

export function resolveLandingClienteCode(input: {
  hostname: string
  search: string
  overrideTenant?: string
}): string | null {
  const fromOverride = input.overrideTenant?.trim() ?? ''
  if (fromOverride !== '') {
    return fromOverride
  }

  return (
    clienteCodeFromSearchParams(input.search) ??
    clienteCodeFromCanonicalHostname(input.hostname)
  )
}
