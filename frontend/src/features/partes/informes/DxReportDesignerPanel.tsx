import { Suspense, lazy, useMemo } from 'react'
import { useTranslation } from 'react-i18next'
import { readDxReportingConfig, resolveDxDesignerReportUrl } from './dxReportingConfig'

const LazyDxReportDesignerApp = lazy(() => import('./DxReportDesignerApp'))

export type PartesDxReportSavedEvent = {
  url: string
  layoutDefinition?: string
  isNew: boolean
}

/** Espejo del context GEN ReportDesignerHost (el paquete 2.3.x no reexporta el type completo). */
export type PartesReportDesignerContext = {
  reportId: number | null
  reportCode?: string | null
  processCode: string
  designerEndpoint: string
  knownReportCodes?: string[]
  onDxReportSaved?: (event: PartesDxReportSavedEvent) => void | Promise<void>
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

  const reportUrl = resolveDxDesignerReportUrl({
    reportCode: context.reportCode,
    reportUrlDefault: config.reportUrl,
    processCode: context.processCode,
  })

  return (
    <div
      data-testid="emissions.designer.dxHost"
      style={{ width: '100%', height: 'calc(100vh - 160px)', minHeight: 480 }}
    >
      <Suspense fallback={<div data-testid="emissions.designer.loading">{t('emissions.designer.loading')}</div>}>
        <LazyDxReportDesignerApp
          key={reportUrl}
          reportUrl={reportUrl}
          host={config.host}
          getDesignerModelAction={config.getDesignerModelAction}
          knownReportCodes={context.knownReportCodes ?? []}
          onDxReportSaved={context.onDxReportSaved}
        />
      </Suspense>
    </div>
  )
}
