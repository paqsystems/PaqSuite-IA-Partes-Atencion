/**
 * Prefijo de códigos DX (reportUrl / report_code) por processCode GEN.
 * Mantiene el Field List ligado al schema del proceso tras Save As.
 */
const REPORT_CODE_PREFIX_BY_PROCESS: Record<string, string> = {
  'partes.informes.consultaDetallada': 'partes.consultaDetallada',
}

export function reportCodePrefixForProcess(processCode: string): string | null {
  const prefix = REPORT_CODE_PREFIX_BY_PROCESS[processCode]?.trim()
  return prefix || null
}

/** Slug seguro para un segmento de report_code / reportUrl DX. */
export function slugifyReportSegment(value: string): string {
  const trimmed = value.trim().toLowerCase()
  const withoutExtension = trimmed.replace(/\.repx$/i, '')
  const withoutDiacritics = withoutExtension.normalize('NFD').replace(/[\u0300-\u036f]/g, '')
  const slug = withoutDiacritics
    .replace(/[^a-z0-9._-]+/g, '-')
    .replace(/-+/g, '-')
    .replace(/^[.-]+|[.-]+$/g, '')
  return slug || `report-${Date.now()}`
}

/**
 * Normaliza el URL del diálogo Save As al código de storage del proceso.
 * Si el usuario escribe solo un nombre, se antepone el prefijo del proceso.
 * Si ya trae el prefijo (o es un Save de un código existente), se deja estable.
 */
export function normalizeSaveAsReportCode(processCode: string, saveAsUrl: string): string {
  const raw = saveAsUrl.trim()
  if (!raw) {
    const prefix = reportCodePrefixForProcess(processCode)
    return prefix ? `${prefix}.nuevo` : `report-${Date.now()}`
  }

  const prefix = reportCodePrefixForProcess(processCode)
  if (!prefix) {
    return slugifyReportSegment(raw).slice(0, 64)
  }

  const lower = raw.toLowerCase()
  const prefixLower = prefix.toLowerCase()
  if (lower === prefixLower || lower.startsWith(`${prefixLower}.`)) {
    return raw.slice(0, 64)
  }

  const segment = slugifyReportSegment(raw)
  return `${prefix}.${segment}`.slice(0, 64)
}

export function isKnownReportCode(url: string, knownReportCodes: string[] | undefined): boolean {
  const needle = url.trim().toLowerCase()
  if (!needle) {
    return false
  }
  return (knownReportCodes ?? []).some((code) => code.trim().toLowerCase() === needle)
}
