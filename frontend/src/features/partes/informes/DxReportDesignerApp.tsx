import { useEffect, useRef } from 'react'
import './dxReportingRuntimeSetup'
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
import { enableReportDesignerAiServices } from './enableReportDesignerAi'
import { parseDxReportSavedArgs } from './parseDxReportSavedArgs'

export type DxReportDesignerAppProps = {
  reportUrl: string
  host: string
  getDesignerModelAction: string
  knownReportCodes?: string[]
  onDxReportSaved?: (event: PartesDxReportSavedEvent) => void | Promise<void>
}

type DxDesignerSender = {
  OpenReport: (url: string) => void
  GetDesignerModel?: () => {
    navigateByReports?: {
      currentTab?: () => { context?: () => { url?: () => string } } | null
    }
  }
}

type DxDesignerHandle = {
  instance: () => DxDesignerSender | null | undefined
}

/**
 * DX solo llama a SetData (Save sin diálogo) si la pestaña tiene `context().url()`.
 * El wrapper React puede montar antes de `OpenReport`; re-sincronizamos al cambiar variante.
 */
function syncDesignerReportUrl(sender: DxDesignerSender | null | undefined, url: string): void {
  const trimmed = url.trim()
  if (!sender || trimmed === '') {
    return
  }

  const currentUrl = sender.GetDesignerModel?.()?.navigateByReports?.currentTab?.()?.context?.()?.url?.()
  if (currentUrl === trimmed) {
    return
  }

  sender.OpenReport(trimmed)
}

/** Chunk aislado: CSS + widget DX Reporting (carga lazy desde el host Partes). */
export default function DxReportDesignerApp({
  reportUrl,
  host,
  getDesignerModelAction,
  knownReportCodes = [],
  onDxReportSaved,
}: DxReportDesignerAppProps) {
  const pendingLayoutRef = useRef<string | undefined>(undefined)
  const designerRef = useRef<DxDesignerHandle | null>(null)

  const useDevMode = import.meta.env.DEV

  useEffect(() => {
    let cancelled = false
    let attempts = 0
    const maxAttempts = 60

    const trySync = () => {
      if (cancelled) {
        return
      }
      attempts += 1
      const sender = designerRef.current?.instance?.() ?? undefined
      if (sender) {
        syncDesignerReportUrl(sender, reportUrl)
        return
      }
      if (attempts < maxAttempts) {
        window.setTimeout(trySync, 150)
      }
    }

    trySync()

    return () => {
      cancelled = true
    }
  }, [reportUrl])

  return (
    <ReportDesigner
      ref={designerRef}
      reportUrl={reportUrl}
      height="100%"
      width="100%"
      {...(useDevMode ? { developmentMode: true } : {})}
    >
      <RequestOptions host={host} getDesignerModelAction={getDesignerModelAction} />
      <Callbacks
        BeforeRender={() => {
          enableReportDesignerAiServices()
          syncDesignerReportUrl(designerRef.current?.instance?.() ?? undefined, reportUrl)
        }}
        ReportOpened={() => {
          syncDesignerReportUrl(designerRef.current?.instance?.() ?? undefined, reportUrl)
        }}
        ReportSaving={(...callbackArgs: unknown[]) => {
          const parsed = parseDxReportSavedArgs(callbackArgs)
          if (parsed.layoutDefinition) {
            pendingLayoutRef.current = parsed.layoutDefinition
          }
        }}
        ReportSaved={(...callbackArgs: unknown[]) => {
          if (!onDxReportSaved) {
            return
          }
          const parsed = parseDxReportSavedArgs(callbackArgs)
          const layoutDefinition = parsed.layoutDefinition ?? pendingLayoutRef.current
          pendingLayoutRef.current = undefined
          void onDxReportSaved({
            url: parsed.url,
            layoutDefinition,
            isNew: parsed.url !== '' && isUnknownEmissionReportCode(parsed.url, knownReportCodes),
          })
        }}
      />
    </ReportDesigner>
  )
}
