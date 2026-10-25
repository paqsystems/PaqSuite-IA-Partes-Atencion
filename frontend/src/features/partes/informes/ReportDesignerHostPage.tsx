import { EmissionReportDesignerPage, isNativeApp, ProcessPageLayout } from '@paqsuite/react-core'
import { useTranslation } from 'react-i18next'
import { useProcessMenuTitle } from '../../auth/useProcessMenuTitle'
import { useSearchParams } from 'react-router-dom'
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

  return (
    <ProcessPageLayout title={pageTitle}>
    <div data-testid="partesEmisionesDisenadorPage" style={{ padding: 16 }}>
      <EmissionReportDesignerPage
        initialProcessCode={initialProcessCode}
        hasDesignPermission={!native}
        isNative={native}
        t={(key) => t(key)}
        renderDesigner={(context) => <DxReportDesignerPanel context={context} />}
      />
    </div>
    </ProcessPageLayout>
  )
}

export default ReportDesignerHostPage
