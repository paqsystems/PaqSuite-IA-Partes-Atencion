import { Suspense, lazy, useMemo } from 'react'
import { useTranslation } from 'react-i18next'
import { readDxReportingConfig } from './dxReportingConfig'

const LazyDxReportDesignerApp = lazy(() => import('./DxReportDesignerApp'))

/** Espejo del context GEN ReportDesignerHost (el paquete no reexporta el type). */
export type PartesReportDesignerContext = {
  reportId: number | null
  processCode: string
  designerEndpoint: string
}

type DxReportDesignerPanelProps = {
  context: PartesReportDesignerContext
}

/**
 * Inyecta el diseñador DevExtreme Reporting real (host ASP.NET vía DXXRD).
 * Requiere `VITE_DX_REPORTING_HOST` apuntando al servicio Reporting.
 */
export function DxReportDesignerPanel({ context }: DxReportDesignerPanelProps) {
  const { t } = useTranslation()
  const config = useMemo(() => readDxReportingConfig(), [])

  if (!config.configured) {
    return (
      <div data-testid="emissions.designer.hostMissing" role="status" style={{ padding: 12 }}>
        <p>{t('emissions.designer.hostMissing')}</p>
        <p style={{ opacity: 0.8, fontSize: 13 }}>{t('emissions.designer.hostMissingHint')}</p>
      </div>
    )
  }

  const reportUrl =
    context.reportId != null ? String(context.reportId) : config.reportUrl || context.processCode

  return (
    <div
      data-testid="emissions.designer.dxHost"
      style={{ width: '100%', height: 'calc(100vh - 160px)', minHeight: 480 }}
    >
      <Suspense fallback={<div data-testid="emissions.designer.loading">{t('emissions.designer.loading')}</div>}>
        <LazyDxReportDesignerApp
          reportUrl={reportUrl}
          host={config.host}
          getDesignerModelAction={config.getDesignerModelAction}
        />
      </Suspense>
    </div>
  )
}
