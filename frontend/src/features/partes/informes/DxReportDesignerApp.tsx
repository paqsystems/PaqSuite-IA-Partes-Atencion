import ReportDesigner, { Callbacks, RequestOptions } from 'devexpress-reporting-react/dx-report-designer'
import 'devextreme/dist/css/dx.light.css'
import '@devexpress/analytics-core/dist/css/dx-analytics.common.css'
import '@devexpress/analytics-core/dist/css/dx-analytics.light.css'
import '@devexpress/analytics-core/dist/css/dx-querybuilder.css'
import 'devexpress-reporting/dist/css/dx-webdocumentviewer.css'
import 'devexpress-reporting/dist/css/dx-reportdesigner.css'
import 'ace-builds/css/ace.css'
import 'ace-builds/css/theme/dreamweaver.css'
import 'ace-builds/css/theme/ambiance.css'
import type { PartesDxReportSavedEvent } from './DxReportDesignerPanel'
import { isUnknownEmissionReportCode } from './emissionReportCatalog'
import { parseDxReportSavedArgs } from './parseDxReportSavedArgs'

export type DxReportDesignerAppProps = {
  reportUrl: string
  host: string
  getDesignerModelAction: string
  knownReportCodes?: string[]
  onDxReportSaved?: (event: PartesDxReportSavedEvent) => void | Promise<void>
}

/** Chunk aislado: CSS + widget DX Reporting (carga lazy desde el host Partes). */
export default function DxReportDesignerApp({
  reportUrl,
  host,
  getDesignerModelAction,
  knownReportCodes = [],
  onDxReportSaved,
}: DxReportDesignerAppProps) {
  return (
    <ReportDesigner reportUrl={reportUrl} height="100%" width="100%" developmentMode>
      <RequestOptions host={host} getDesignerModelAction={getDesignerModelAction} />
      <Callbacks
        ReportSaved={(...callbackArgs: unknown[]) => {
          if (!onDxReportSaved) {
            return
          }
          const parsed = parseDxReportSavedArgs(callbackArgs)
          void onDxReportSaved({
            url: parsed.url,
            layoutDefinition: parsed.layoutDefinition,
            isNew: parsed.url !== '' && isUnknownEmissionReportCode(parsed.url, knownReportCodes),
          })
        }}
      />
    </ReportDesigner>
  )
}
