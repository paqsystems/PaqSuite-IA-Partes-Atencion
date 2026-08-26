/** Config del host ASP.NET DevExpress Reporting (diseñador GEN-15). */

export const DEFAULT_DX_REPORTING_HOST = 'http://127.0.0.1:5055'
export const DEFAULT_DX_GET_DESIGNER_MODEL_ACTION = 'DXXRD/GetDesignerModel'
export const DEFAULT_DX_REPORT_URL = 'partes.consultaDetallada.principal'

export type DxReportingConfig = {
  /** URI del servicio Reporting (proxy Vite o URL directa). */
  host: string
  getDesignerModelAction: string
  reportUrl: string
  /** false si falta host usable. */
  configured: boolean
}

function trimSlash(value: string): string {
  return value.replace(/\/+$/, '')
}

function ensureTrailingSlash(value: string): string {
  const trimmed = value.trim()
  if (!trimmed) {
    return ''
  }
  return trimmed.endsWith('/') ? trimmed : `${trimmed}/`
}

/**
 * Host que consume el cliente DX.
 * En Vite, el proxy `/DXXRD` apunta a `VITE_DX_REPORTING_HOST`;
 * el cliente usa el origin actual para evitar CORS.
 */
export function resolveDxReportingClientHost(
  reportingServiceUrl: string | undefined,
  locationOrigin: string,
  useSameOriginProxy: boolean,
): string {
  if (useSameOriginProxy) {
    return ensureTrailingSlash(locationOrigin)
  }
  if (!reportingServiceUrl?.trim()) {
    return ''
  }
  return ensureTrailingSlash(reportingServiceUrl)
}

type DxReportingEnv = {
  VITE_DX_REPORTING_HOST?: string
  VITE_DX_REPORTING_DIRECT?: string
  VITE_DX_REPORTING_GET_DESIGNER_MODEL_ACTION?: string
  VITE_DX_REPORTING_REPORT_URL?: string
}

export function readDxReportingConfig(
  env: DxReportingEnv = import.meta.env as DxReportingEnv,
): DxReportingConfig {
  const serviceUrl = env.VITE_DX_REPORTING_HOST?.trim() || ''
  const direct = String(env.VITE_DX_REPORTING_DIRECT ?? '').toLowerCase() === 'true'
  const useSameOriginProxy = Boolean(serviceUrl) && !direct
  const locationOrigin =
    typeof window !== 'undefined' && window.location?.origin
      ? window.location.origin
      : 'http://localhost:3010'

  const host = resolveDxReportingClientHost(serviceUrl, locationOrigin, useSameOriginProxy)
  const getDesignerModelAction =
    env.VITE_DX_REPORTING_GET_DESIGNER_MODEL_ACTION?.trim() || DEFAULT_DX_GET_DESIGNER_MODEL_ACTION
  const reportUrl = env.VITE_DX_REPORTING_REPORT_URL?.trim() || DEFAULT_DX_REPORT_URL

  return {
    host,
    getDesignerModelAction,
    reportUrl,
    configured: Boolean(serviceUrl && host),
  }
}

export function reportingProxyTarget(env: Record<string, string>): string {
  const raw = env.VITE_DX_REPORTING_HOST?.trim() || DEFAULT_DX_REPORTING_HOST
  return trimSlash(raw)
}
