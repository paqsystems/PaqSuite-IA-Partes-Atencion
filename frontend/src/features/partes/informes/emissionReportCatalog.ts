/** Alinea código de catálogo GEN con el Url que persiste DX (sidecar SafeUrl). */

export function catalogEmissionReportCode(code: unknown): string {
  if (typeof code === 'string') {
    return code.trim()
  }
  if (typeof code === 'number' && Number.isFinite(code)) {
    return String(code)
  }
  return ''
}

export function catalogCodeFromDxUrl(url: string | null | undefined | number): string {
  const trimmed = catalogEmissionReportCode(url).replace(/\\/g, '/')
  if (trimmed.length === 0) {
    return ''
  }
  const lastSlash = trimmed.lastIndexOf('/')
  const last = lastSlash >= 0 ? trimmed.slice(lastSlash + 1) : trimmed
  return last.replace(/\.repx$/i, '').trim()
}

export function normalizeEmissionReportCode(url: string | null | undefined | number): string {
  const trimmed = catalogEmissionReportCode(url).replace(/\\/g, '/')
  if (trimmed.length === 0) {
    return ''
  }
  const lastSlash = trimmed.lastIndexOf('/')
  const last = lastSlash >= 0 ? trimmed.slice(lastSlash + 1) : trimmed
  const chars = Array.from(last).map((ch) =>
    /\p{L}|\p{N}/u.test(ch) || ch === '-' || ch === '_' || ch === ':' ? ch : '-',
  )
  const safe = chars.join('')
  return safe.trim().length > 0 ? safe : 'report'
}

export function codesReferToSameEmissionReport(
  left: string | null | undefined,
  right: string | null | undefined,
): boolean {
  const a = normalizeEmissionReportCode(left).toLowerCase()
  const b = normalizeEmissionReportCode(right).toLowerCase()
  return a.length > 0 && a === b
}

export function isUnknownEmissionReportCode(url: string, knownReportCodes: readonly string[]): boolean {
  const incoming = normalizeEmissionReportCode(url)
  if (incoming.length === 0) {
    return false
  }
  return !knownReportCodes.some((code) => codesReferToSameEmissionReport(code, incoming))
}

export function displayNameFromReportUrl(url: string): string {
  const trimmed = url.trim()
  const last = trimmed.includes('.') ? trimmed.slice(trimmed.lastIndexOf('.') + 1) : trimmed
  return last.replace(/[-_]+/g, ' ').trim() || trimmed
}
