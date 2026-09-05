import { isNativeApp } from '@paqsuite/react-core'
import { useTranslation } from 'react-i18next'
import { useSearchParams } from 'react-router-dom'
import { DxReportDesignerPanel, type PartesReportDesignerContext } from './DxReportDesignerPanel'
import { EmissionReportDesignerPage } from './EmissionReportDesignerPage'
import { readInitialProcessCodeFromSearch } from './reportDesignerInitialProcess'

/**
 * Host Partes del diseñador GEN-15.
 * Monta `EmissionReportDesignerPage` sin `processCode` fijo: lista + confirmación Must (también N=1).
 */
export function ReportDesignerHostPage() {
  const { t } = useTranslation()
  const [searchParams] = useSearchParams()
  const initialProcessCode = readInitialProcessCodeFromSearch(searchParams)
  const native = isNativeApp()

  return (
    <div data-testid="partesEmisionesDisenadorPage" style={{ padding: 16 }}>
      <h2>{t('emissions.designer.title')}</h2>
      <EmissionReportDesignerPage
        initialProcessCode={initialProcessCode}
        hasDesignPermission={!native}
        isNative={native}
        t={(key) => t(key)}
        renderDesigner={(context) => (
          <DxReportDesignerPanel context={context as PartesReportDesignerContext} />
        )}
      />
    </div>
  )
}

export default ReportDesignerHostPage
