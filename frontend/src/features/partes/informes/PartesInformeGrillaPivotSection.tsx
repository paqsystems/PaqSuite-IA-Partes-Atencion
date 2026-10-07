import { useCallback, useRef, useState, type ReactNode } from 'react'
import { useTranslation } from 'react-i18next'
import {
  ConsultaGrillaPivotShell,
  PivotLayoutSaveAsDialog,
  PivotLayoutsToolbarControls,
  usePivotLayouts,
  type PivotConsultaCatalogDto,
  type PivotGridBlockHandle,
} from '@paqsuite/react-core'
import { getAuthToken } from '../../auth/authSessionStore'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'

export type PartesInformeGrillaPivotSectionProps = {
  consultaId: string
  catalog: PivotConsultaCatalogDto
  locale: string
  native: boolean
  loadPivotDataset: () => Promise<Record<string, unknown>[]>
  renderGridView: (toolbarLeading: ReactNode) => ReactNode
  /** Acciones extra a la izquierda de la toolbar (p. ej. emitir informe). */
  toolbarLeadingExtras?: ReactNode
}

/**
 * Cableado GEN-12 para informes Partes: shell SDK + plantillas pivot en toolbar.
 */
export function PartesInformeGrillaPivotSection({
  consultaId,
  catalog,
  locale,
  native,
  loadPivotDataset,
  renderGridView,
  toolbarLeadingExtras,
}: PartesInformeGrillaPivotSectionProps) {
  const { t } = useTranslation()
  const pivotRef = useRef<PivotGridBlockHandle>(null)
  const [saveAsOpen, setSaveAsOpen] = useState(false)

  const captureState = useCallback(() => pivotRef.current?.captureState() ?? '', [])
  const applyState = useCallback((stateJson: string | null) => {
    pivotRef.current?.applyState(stateJson)
  }, [])

  const layouts = usePivotLayouts({
    consultaId,
    accessToken: getAuthToken(),
    platform: buildAuthPlatformHeaders(),
    captureState,
    applyState,
  })

  const mergeToolbarLeading = useCallback(
    (toolbarLeading: ReactNode) => {
      if (!toolbarLeadingExtras) {
        return renderGridView(toolbarLeading)
      }
      return renderGridView(
        <>
          {toolbarLeadingExtras}
          {toolbarLeading}
        </>,
      )
    },
    [renderGridView, toolbarLeadingExtras],
  )

  if (native) {
    return <>{renderGridView(null)}</>
  }

  return (
    <>
      <ConsultaGrillaPivotShell
        consultaId={consultaId}
        catalog={catalog}
        locale={locale}
        loadPivotDataset={loadPivotDataset}
        pivotRef={pivotRef}
        gridLabel={t('partes.common.grilla')}
        pivotLabel={t('partes.common.pivot')}
        toolbarLayoutsSlot={
          <PivotLayoutsToolbarControls
            layouts={layouts}
            onOpenSaveAs={() => setSaveAsOpen(true)}
          />
        }
        renderGridView={mergeToolbarLeading}
      />
      <PivotLayoutSaveAsDialog
        visible={saveAsOpen}
        busy={layouts.busy}
        errorMessage={layouts.errorMessage}
        onHiding={() => setSaveAsOpen(false)}
        onConfirm={(layoutName) => layouts.saveAs(layoutName)}
      />
    </>
  )
}
