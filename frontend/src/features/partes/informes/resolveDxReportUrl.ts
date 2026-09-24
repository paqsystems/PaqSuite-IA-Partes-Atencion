/**
 * El sidecar DX identifica el reporte por **código de storage** (p. ej.
 * `partes.consultaDetallada.principal`), no por el id numérico GEN.
 * Usar el id como reportUrl deja el Field List sin dataset del proceso.
 */

const PRINCIPAL_REPORT_BY_PROCESS: Record<string, string> = {
  'partes.informes.consultaDetallada': 'partes.consultaDetallada.principal',
}

export type ResolveDxReportUrlInput = {
  processCode: string
  /** Id GEN del reporte seleccionado (solo para API layout; no es reportUrl DX). */
  reportId?: number | null
  /** Código del reporte si el host ya lo resolvió. */
  reportCode?: string | null
  /** Default env / config (principal Partes). */
  fallbackReportUrl: string
}

export function resolveDxReportUrl(input: ResolveDxReportUrlInput): string {
  const explicit = input.reportCode?.trim()
  if (explicit) {
    return explicit
  }

  const mapped = PRINCIPAL_REPORT_BY_PROCESS[input.processCode]?.trim()
  if (mapped) {
    return mapped
  }

  const fallback = input.fallbackReportUrl?.trim()
  if (fallback && !/^\d+$/.test(fallback)) {
    return fallback
  }

  const processCode = input.processCode?.trim()
  return processCode || 'report'
}
