import ReportDesigner, { RequestOptions } from 'devexpress-reporting-react/dx-report-designer'
import 'devextreme/dist/css/dx.light.css'
import '@devexpress/analytics-core/dist/css/dx-analytics.common.css'
import '@devexpress/analytics-core/dist/css/dx-analytics.light.css'
import '@devexpress/analytics-core/dist/css/dx-querybuilder.css'
import 'devexpress-reporting/dist/css/dx-webdocumentviewer.css'
import 'devexpress-reporting/dist/css/dx-reportdesigner.css'
import 'ace-builds/css/ace.css'
import 'ace-builds/css/theme/dreamweaver.css'
import 'ace-builds/css/theme/ambiance.css'

export type DxReportDesignerAppProps = {
  reportUrl: string
  host: string
  getDesignerModelAction: string
}

/** Chunk aislado: CSS + widget DX Reporting (carga lazy desde el host Partes). */
export default function DxReportDesignerApp({
  reportUrl,
  host,
  getDesignerModelAction,
}: DxReportDesignerAppProps) {
  return (
    <ReportDesigner reportUrl={reportUrl} height="100%" width="100%" developmentMode>
      <RequestOptions host={host} getDesignerModelAction={getDesignerModelAction} />
    </ReportDesigner>
  )
}
