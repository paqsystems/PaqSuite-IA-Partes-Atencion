import { useCallback, useMemo, useState } from 'react'
import Popup from 'devextreme-react/popup'
import Button from 'devextreme-react/button'
import SelectBox from 'devextreme-react/select-box'
import LoadPanel from 'devextreme-react/load-panel'
import {
  useEmission,
  resolveAvailableChannels,
  shouldOfferPreview,
  EmissionGrupoEmpresarioSelect,
  type EmissionChannel,
  type EmissionCompletePayload,
  type EmissionMode,
} from '@paqsuite/react-core'
import { deliverEmissionJob } from './emissionJobDelivery'

export type PartesEmissionDialogProps = {
  processCode: string
  visible: boolean
  onClose: () => void
  isNative?: boolean
  permiteConsolidado?: boolean
  onComplete?: (payload: EmissionCompletePayload) => void
  t?: (key: string) => string
}

/**
 * Ventana Emitir del host.
 * El diálogo GEN del SDK 2.3.x arma el campo del SelectBox con un template sin TextBox;
 * en DX 25.2+ eso dispara E1010 y deja la solapa en blanco. Aquí se usa displayExpr de campo.
 */
export function PartesEmissionDialog({
  processCode,
  visible,
  onClose,
  isNative = false,
  permiteConsolidado = false,
  onComplete,
  t,
}: PartesEmissionDialogProps) {
  const translate = t ?? ((key: string) => key)
  const [deliveryErrorKey, setDeliveryErrorKey] = useState<string | null>(null)

  const handleComplete = useCallback(
    (payload: EmissionCompletePayload) => {
      onComplete?.(payload)
      if (payload.status !== 'done') {
        return
      }
      void deliverEmissionJob({
        jobId: payload.jobId,
        channel: payload.channel,
        fileName: payload.fileName,
      }).catch(() => {
        setDeliveryErrorKey('emissions.error.download')
      })
    },
    [onComplete],
  )

  const emission = useEmission({ processCode, isNative, onComplete: handleComplete })

  const channelItems = useMemo(() => {
    if (!emission.process) {
      return []
    }
    return resolveAvailableChannels(emission.process, isNative).map((channel) => ({
      id: channel,
      label: translate(`emissions.channel.${channel}`),
    }))
  }, [emission.process, isNative, translate])

  const modeItems = useMemo(() => {
    const modes: EmissionMode[] = []
    if (emission.process?.modes.consolidated) {
      modes.push('consolidated')
    }
    if (emission.process?.modes.segmented) {
      modes.push('segmented')
    }
    return modes.map((mode) => ({
      id: mode,
      label: translate(`emissions.mode.${mode}`),
    }))
  }, [emission.process, translate])

  const offerPreview = emission.process ? shouldOfferPreview(emission.process, isNative) : false

  const handleDownload = () => {
    if (!emission.job || emission.job.status !== 'done') {
      return
    }
    setDeliveryErrorKey(null)
    void deliverEmissionJob({
      jobId: emission.job.jobId,
      channel: emission.job.channel,
      fileName: emission.job.fileName,
    }).catch(() => {
      setDeliveryErrorKey('emissions.error.download')
    })
  }

  const displayErrorKey = deliveryErrorKey ?? emission.errorKey

  return (
    <Popup
      visible={visible}
      onHiding={onClose}
      showTitle
      title={translate('emissions.dialog.title')}
      width={isNative ? '100%' : 820}
      height="auto"
      wrapperAttr={{ 'data-testid': 'emissions.dialog' }}
    >
      <LoadPanel visible={emission.loading} />
      <div style={{ display: 'flex', flexDirection: 'column', gap: 12 }}>
        <SelectBox
          dataSource={channelItems}
          value={emission.selection.channel}
          valueExpr="id"
          displayExpr="label"
          placeholder={translate('emissions.channel')}
          onValueChanged={(event) =>
            emission.setSelection({ channel: (event.value as EmissionChannel) ?? null })
          }
          elementAttr={{ 'data-testid': 'emissions.channel' }}
        />

        {modeItems.length > 1 ? (
          <SelectBox
            dataSource={modeItems}
            value={emission.selection.mode}
            valueExpr="id"
            displayExpr="label"
            placeholder={translate('emissions.mode')}
            onValueChanged={(event) =>
              emission.setSelection({ mode: (event.value as EmissionMode) ?? 'consolidated' })
            }
            elementAttr={{ 'data-testid': 'emissions.mode' }}
          />
        ) : null}

        <EmissionGrupoEmpresarioSelect
          enabled={permiteConsolidado}
          value={emission.selection.grupoEmpresarioId ?? null}
          onValueChange={(grupoEmpresarioId) => emission.setSelection({ grupoEmpresarioId })}
          t={translate}
        />

        {emission.process && emission.process.reports.length > 0 ? (
          <SelectBox
            dataSource={emission.process.reports}
            valueExpr="id"
            displayExpr="name"
            value={emission.selection.reportId ?? null}
            placeholder={translate('emissions.report')}
            onValueChanged={(event) =>
              emission.setSelection({ reportId: (event.value as number) ?? undefined })
            }
            elementAttr={{ 'data-testid': 'emissions.report' }}
          />
        ) : null}

        {offerPreview ? (
          <div data-testid="emissions.previewArea">
            <Button
              text={translate('emissions.preview')}
              icon="find"
              disabled={!emission.selection.channel}
              onClick={() => void emission.runPreview()}
              elementAttr={{ 'data-testid': 'emissions.preview' }}
            />
            {emission.preview && !emission.previewStale ? (
              <iframe
                title={translate('emissions.preview')}
                src={emission.preview.contentUrl}
                style={{ width: '100%', height: 420, border: '1px solid #ddd', marginTop: 8 }}
                data-testid="emissions.previewFrame"
              />
            ) : null}
            {emission.preview && emission.previewStale ? (
              <p data-i18n-key="emissions.preview.stale">{translate('emissions.preview.stale')}</p>
            ) : null}
          </div>
        ) : null}

        {displayErrorKey ? <p data-i18n-key={displayErrorKey}>{translate(displayErrorKey)}</p> : null}

        {emission.job && emission.job.status === 'done' && emission.job.channel !== 'mail' ? (
          <Button
            text={translate('emissions.download')}
            icon="download"
            onClick={handleDownload}
            elementAttr={{ 'data-testid': 'emissions.download' }}
          />
        ) : null}

        <div style={{ display: 'flex', gap: 8, justifyContent: 'flex-end' }}>
          <Button
            text={translate('emissions.close')}
            onClick={onClose}
            elementAttr={{ 'data-testid': 'emissions.close' }}
          />
          <Button
            text={translate('emissions.emit')}
            type="default"
            stylingMode="contained"
            disabled={!emission.canEmit}
            onClick={() => {
              setDeliveryErrorKey(null)
              void emission.emit()
            }}
            elementAttr={{ 'data-testid': 'emissions.emit' }}
          />
        </div>
      </div>
    </Popup>
  )
}
