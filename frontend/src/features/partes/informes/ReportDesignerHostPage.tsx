import { EmissionReportDesignerPage, isNativeApp, ProcessPageLayout } from '@paqsuite/react-core'
import { useMemo } from 'react'
import { useTranslation } from 'react-i18next'
import { useSearchParams } from 'react-router-dom'
import { getAuthToken } from '../../auth/authSessionStore'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'
import { useProcessMenuTitle } from '../../auth/useProcessMenuTitle'
import { DxReportDesignerPanel } from './DxReportDesignerPanel'
import { readInitialProcessCodeFromSearch } from './reportDesignerInitialProcess'

/**
 * Host Partes del diseñador GEN-15.
 * Monta `EmissionReportDesignerPage` del SDK sin `processCode` fijo.
 */
export function ReportDesignerHostPage() {
  const { t } = useTranslation()
  const [searchParams] = useSearchParams()
  const initialProcessCode = readInitialProcessCodeFromSearch(searchParams)
  const native = isNativeApp()
  const pageTitle = useProcessMenuTitle(t('emissions.designer.title'), '/emisiones/disenador')
  const accessToken = getAuthToken() ?? undefined
  const platform = useMemo(() => buildAuthPlatformHeaders(), [])

  return (
    <ProcessPageLayout title={pageTitle}>
    <div data-testid="partesEmisionesDisenadorPage" style={{ padding: 16 }}>
      <EmissionReportDesignerPage
        initialProcessCode={initialProcessCode}
        hasDesignPermission={!native}
        isNative={native}
        accessToken={accessToken}
        platform={platform}
        t={(key) => t(key)}
        renderDesigner={(context) => <DxReportDesignerPanel context={context} />}
      />
    </div>
    </ProcessPageLayout>
  )
}

export default ReportDesignerHostPage
