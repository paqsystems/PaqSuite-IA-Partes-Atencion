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
import {
  ackLayoutBackup,
  fetchLayoutContent,
  restoreLayoutBackup,
} from './layoutPendingReviewApi'

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
  const [pendingScReview, setPendingScReview] = useState(false)

  const onAppliedRefresh = useCallback(() => {
    setDesignerRemountKey((prev) => prev + 1)
  }, [])

  const onPendingReview = useCallback((pending: boolean) => {
    setPendingScReview(pending)
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
              const reportUrl = context.reportCode ?? null

              const commitPending = async () => {
                if (!reportUrl) {
                  throw new Error(t('partes.emissionDesign.reportUrlRequired'))
                }
                if (!context.onDxReportSaved) {
                  throw new Error(t('partes.emissionDesign.pendingSaveFailed'))
                }

                const content = await fetchLayoutContent(reportUrl)
                if (!content.ok) {
                  throw new Error(content.error || t('partes.emissionDesign.pendingSaveFailed'))
                }

                await context.onDxReportSaved({
                  url: reportUrl,
                  layoutDefinition: content.layoutXml,
                  isNew: false,
                })

                const ack = await ackLayoutBackup(reportUrl)
                if (!ack.ok) {
                  throw new Error(ack.error || t('partes.emissionDesign.pendingSaveFailed'))
                }

                setPendingScReview(false)
              }

              const discardPending = async () => {
                if (!reportUrl) {
                  throw new Error(t('partes.emissionDesign.reportUrlRequired'))
                }

                const restored = await restoreLayoutBackup(reportUrl)
                if (!restored.ok) {
                  throw new Error(restored.error || t('partes.emissionDesign.pendingDiscardFailed'))
                }

                setPendingScReview(false)
                setDesignerRemountKey((prev) => prev + 1)
              }

              return (
                <div style={{ display: 'flex', flexDirection: 'column', gap: 0 }}>
                  <DxReportDesignerPanel
                    key={`dx-${designerRemountKey}-${context.reportCode ?? 'none'}`}
                    context={context}
                  />
                  {showSc ? (
                    <EmissionDesignSmartCaptureSlot
                      processCode={context.processCode}
                      reportUrl={reportUrl}
                      remountToken={designerRemountKey}
                      onAppliedRefresh={onAppliedRefresh}
                      pendingReview={pendingScReview}
                      onPendingReview={onPendingReview}
                      onCommitPending={commitPending}
                      onDiscardPending={discardPending}
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
