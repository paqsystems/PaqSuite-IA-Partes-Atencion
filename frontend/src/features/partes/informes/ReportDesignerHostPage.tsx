import { EmissionReportDesignerPage, isNativeApp, ProcessPageLayout } from '@paqsuite/react-core'
import { useCallback, useMemo, useState } from 'react'
import { useTranslation } from 'react-i18next'
import { useSearchParams } from 'react-router-dom'
import { getAuthToken } from '../../auth/authSessionStore'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'
import { useProcessMenuTitle } from '../../auth/useProcessMenuTitle'
import { DxReportDesignerPanel } from './DxReportDesignerPanel'
import { EmissionDesignSmartCaptureSlot } from './EmissionDesignSmartCaptureSlot'
import { ReportDesignerPageErrorBoundary } from './ReportDesignerPageErrorBoundary'
import { readInitialProcessCodeFromSearch } from './reportDesignerInitialProcess'

const DESIGN_SC_PROCESS = 'partes.informes.consultaDetallada'

/**
 * Host Partes del diseñador GEN-15 + Smart Capture diseño (oleada 2 B).
 */
export function ReportDesignerHostPage() {
  const { t } = useTranslation()
  const [searchParams] = useSearchParams()
  const initialProcessCode = readInitialProcessCodeFromSearch(searchParams)
  const native = isNativeApp()
  const pageTitle = useProcessMenuTitle(t('emissions.designer.title'), '/emisiones/disenador')
  const accessToken = getAuthToken() ?? undefined
  const platform = useMemo(() => buildAuthPlatformHeaders(), [])
  const [designerRemountKey, setDesignerRemountKey] = useState(0)

  const onAppliedRefresh = useCallback(() => {
    setDesignerRemountKey((prev) => prev + 1)
  }, [])

  return (
    <ProcessPageLayout title={pageTitle}>
      <div
        data-testid="partesEmisionesDisenadorPage"
        style={{ padding: 16, minHeight: '60vh' }}
      >
        <ReportDesignerPageErrorBoundary translate={(key) => t(key)}>
          <EmissionReportDesignerPage
            initialProcessCode={initialProcessCode}
            hasDesignPermission={!native}
            isNative={native}
            accessToken={accessToken}
            platform={platform}
            t={(key) => t(key)}
            renderDesigner={(context) => {
              const showSc = !native && context.processCode === DESIGN_SC_PROCESS

              return (
                <div style={{ display: 'flex', flexDirection: 'column', gap: 0 }}>
                  <DxReportDesignerPanel
                    key={`dx-${designerRemountKey}-${context.reportCode ?? 'none'}`}
                    context={context}
                  />
                  {showSc ? (
                    <EmissionDesignSmartCaptureSlot
                      processCode={context.processCode}
                      reportUrl={context.reportCode ?? null}
                      remountToken={designerRemountKey}
                      onAppliedRefresh={onAppliedRefresh}
                    />
                  ) : null}
                </div>
              )
            }}
          />
        </ReportDesignerPageErrorBoundary>
      </div>
    </ProcessPageLayout>
  )
}

export default ReportDesignerHostPage
