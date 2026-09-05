import config from 'devextreme/core/config'

/**
 * Licencia DevExtreme — MUST ejecutarse antes de montar widgets.
 * DX 26.1 exige clave LCP (`VITE_DEVEXPRESS_LICENSE_KEY`, mismo valor que smoke Framework).
 * El JWT `eyJ…` de 25.2 no vale en 26.1 y dispara el banner de evaluación.
 */
function readLicenseKey(): string {
  const candidates = [
    import.meta.env.VITE_DEVEXPRESS_LICENSE_KEY,
    import.meta.env.VITE_DEVEXTREME_LICENSE,
  ]
  for (const raw of candidates) {
    const key = String(raw ?? '')
      .trim()
      .replace(/^['"]|['"]$/g, '')
      .replace(/[\r\n\t]/g, '')
    if (!key) {
      continue
    }
    if (key.startsWith('eyJ')) {
      continue
    }
    return key
  }
  return ''
}

const licenseKey = readLicenseKey()

if (import.meta.env.PROD && licenseKey === '') {
  throw new Error(
    '[DevExtreme] Licencia faltante en produccion. Configure VITE_DEVEXPRESS_LICENSE_KEY (DX 26.1) para generar un build valido.',
  )
}

if (licenseKey) {
  config({ licenseKey })
}
