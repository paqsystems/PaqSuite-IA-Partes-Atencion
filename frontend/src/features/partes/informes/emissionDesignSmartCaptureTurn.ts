import {
  applySmartCaptureActions,
  buildSmartCaptureTurnRequest,
  postSmartCaptureTurn,
  type PendingChoice,
  type SmartCaptureSendPayload,
  type SmartCaptureTurnAction,
} from '@paqsuite/react-core'
import { LAYOUT_APPLY_URL } from './layoutPendingReviewApi'

const turnUrlFor = (processCode: string) =>
  `/api/v1/emissions/design/processes/${encodeURIComponent(processCode)}/assistant/turn`

/** @deprecated prefer LAYOUT_APPLY_URL — se mantiene por tests existentes */
export const APPLY_URL = LAYOUT_APPLY_URL

export type EmissionDesignSmartCaptureHandlers = {
  processCode: string
  reportUrl: string | null
  pendingChoice: PendingChoice | null
  activeCredentialId: number | null
  locale?: string | null
  confirmDirtyApply: () => Promise<boolean>
  onAssistantReply: (text: string) => void
  onError: (message: string) => void
  resolveMessage: (key: string) => string
  onAppliedRefresh: () => void
  /** L-B-10: tras apply con cambios, marcar revisión Guardar/Descartar. */
  onPendingReview?: (pending: boolean) => void
  setPendingChoice: (next: PendingChoice | null) => void
  signal?: AbortSignal
}

function normalizeApplyPayload(payload: unknown): Record<string, unknown> {
  if (payload && typeof payload === 'object' && !Array.isArray(payload)) {
    return payload as Record<string, unknown>
  }
  return {}
}

function toApplyActions(actions: SmartCaptureTurnAction[]): Array<Record<string, unknown>> {
  return actions.map((item) => ({
    type: item.action,
    action: item.action,
    payload: normalizeApplyPayload(item.payload),
  }))
}

function resolveDesignReply(text: string, resolveMessage: (key: string) => string): string {
  const trimmed = text.trim()
  if (!trimmed) {
    return resolveMessage('partes.emissionDesign.actionsPlanned')
  }
  if (trimmed.startsWith('partes.emissionDesign.') || trimmed.startsWith('partes.smartCapture.')) {
    const resolved = resolveMessage(trimmed)
    return resolved !== trimmed ? resolved : trimmed
  }
  return trimmed
}

export async function handleEmissionDesignSmartCaptureSend(
  payload: SmartCaptureSendPayload,
  handlers: EmissionDesignSmartCaptureHandlers,
): Promise<void> {
  if (!handlers.reportUrl) {
    handlers.onError(handlers.resolveMessage('partes.emissionDesign.reportUrlRequired'))
    return
  }

  const credentialId = payload.credentialId ?? handlers.activeCredentialId
  if (credentialId === null) {
    handlers.onError(handlers.resolveMessage('partes.emissionDesign.configurationRequired'))
    return
  }

  const base = buildSmartCaptureTurnRequest({
    message: payload.message,
    modality: payload.modality,
    credentialId,
    draftContext: {
      processCode: handlers.processCode,
      reportUrl: handlers.reportUrl,
      locale: handlers.locale ?? 'es',
      emissionDesign: {
        processCode: handlers.processCode,
        semanticSchema: `emission-designer-schema://${handlers.processCode}`,
      },
    },
    pendingChoice: handlers.pendingChoice,
    images: payload.images ?? [],
    supportsVision: false,
  })

  const body = {
    ...base,
    reportUrl: handlers.reportUrl,
    locale: handlers.locale ?? 'es',
  }

  try {
    const result = await postSmartCaptureTurn(turnUrlFor(handlers.processCode), body, {
      signal: handlers.signal,
    })

    if (result.kind === 'ok') {
      const turn = result.envelope.resultado
      const reply = resolveDesignReply(turn.replyText ?? '', handlers.resolveMessage)

      if (turn.configurationRequired) {
        handlers.onAssistantReply(
          resolveDesignReply(
            turn.replyText || 'partes.emissionDesign.configurationRequired',
            handlers.resolveMessage,
          ),
        )
        handlers.setPendingChoice(turn.pendingChoice ?? null)
        return
      }

      handlers.onAssistantReply(reply)
      handlers.setPendingChoice(turn.pendingChoice ?? null)

      const actions = Array.isArray(turn.actions) ? turn.actions : []
      if (actions.length === 0) {
        return
      }

      const confirmed = await handlers.confirmDirtyApply()
      if (!confirmed) {
        handlers.onAssistantReply(handlers.resolveMessage('partes.emissionDesign.applyCancelled'))
        return
      }

      const applyRes = await fetch(LAYOUT_APPLY_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
        body: JSON.stringify({
          reportUrl: handlers.reportUrl,
          processCode: handlers.processCode,
          actions: toApplyActions(actions),
        }),
        signal: handlers.signal,
      })

      const applyJson = (await applyRes.json().catch(() => null)) as
        | {
            ok?: boolean
            applied?: number
            pendingReview?: boolean
            error?: string
            details?: string[]
          }
        | null

      if (!applyRes.ok || !applyJson?.ok) {
        handlers.onError(
          applyJson?.error
            ? String(applyJson.error)
            : handlers.resolveMessage('partes.emissionDesign.applyFailed'),
        )
        return
      }

      await applySmartCaptureActions(actions, {
        applyAction: async () => undefined,
      })

      const details = (applyJson.details ?? []).map((d) => String(d))
      const noopSinDetalle = details.some((d) =>
        d.includes('addPageHeaderColumnLabels:sin_columnas_detalle'),
      )
      const noopYaColocados = details.some((d) =>
        d.includes('addPageHeaderColumnLabels:ya_colocados'),
      )
      const noopEmpresaYaColocado = details.some(
        (d) =>
          d.includes('setPageHeaderEmpresa:ya_colocados') ||
          d.includes('setPageHeaderEmpresa:ya_mismo_texto') ||
          d === 'noop:setPageHeaderEmpresa',
      )

      if ((applyJson.applied ?? 0) === 0 && noopSinDetalle) {
        handlers.onAssistantReply(
          handlers.resolveMessage('partes.emissionDesign.pageHeader.noDetailColumns'),
        )
        return
      }

      if ((applyJson.applied ?? 0) === 0 && noopYaColocados) {
        handlers.onAssistantReply(
          handlers.resolveMessage('partes.emissionDesign.pageHeader.columnLabelsAlreadyPlaced'),
        )
        return
      }

      if ((applyJson.applied ?? 0) === 0 && noopEmpresaYaColocado) {
        handlers.onAssistantReply(
          handlers.resolveMessage('partes.emissionDesign.pageHeader.empresaAlreadyPlaced'),
        )
        return
      }

      const noopFechaYaColocado = details.some(
        (d) =>
          d.includes('setPageHeaderFecha:ya_colocados') || d === 'noop:setPageHeaderFecha',
      )

      if ((applyJson.applied ?? 0) === 0 && noopFechaYaColocado) {
        handlers.onAssistantReply(
          handlers.resolveMessage('partes.emissionDesign.pageHeader.fechaAlreadyPlaced'),
        )
        return
      }

      const noopTitlePanelYaColocado = details.some(
        (d) =>
          d.includes('wrapReportTitleInPanel:ya_colocados') ||
          d === 'noop:wrapReportTitleInPanel',
      )

      if ((applyJson.applied ?? 0) === 0 && noopTitlePanelYaColocado) {
        handlers.onAssistantReply(
          handlers.resolveMessage('partes.emissionDesign.pageHeader.titlePanelAlreadyPlaced'),
        )
        return
      }

      const noopTitleFormatoYa = details.some((d) =>
        d.includes('setControlAppearance:ya_formateado'),
      )

      if ((applyJson.applied ?? 0) === 0 && noopTitleFormatoYa) {
        handlers.onAssistantReply(
          handlers.resolveMessage('partes.emissionDesign.appearance.titleAlreadyFormatted'),
        )
        return
      }

      const noopGroupYaAgrupado = details.some(
        (d) =>
          d.includes('addGroupByField:ya_agrupado') || d === 'noop:addGroupByField',
      )

      if ((applyJson.applied ?? 0) === 0 && noopGroupYaAgrupado) {
        handlers.onAssistantReply(
          handlers.resolveMessage('partes.emissionDesign.group.alreadyGrouped'),
        )
        return
      }

      const noopWatermarkYa = details.some((d) =>
        d.includes('setReportWatermarkImage:ya_existe'),
      )
      if ((applyJson.applied ?? 0) === 0 && noopWatermarkYa) {
        handlers.onAssistantReply(
          handlers.resolveMessage('partes.emissionDesign.images.watermarkAlreadySet'),
        )
        return
      }

      const noopLogoYa = details.some((d) =>
        d.includes('setPageHeaderLogo:ya_colocados'),
      )
      if ((applyJson.applied ?? 0) === 0 && noopLogoYa) {
        handlers.onAssistantReply(
          handlers.resolveMessage('partes.emissionDesign.images.logoAlreadyPlaced'),
        )
        return
      }

      const noopBarCodeYa = details.some((d) =>
        d.includes('addDetailBarCode:ya_existe'),
      )
      if ((applyJson.applied ?? 0) === 0 && noopBarCodeYa) {
        handlers.onAssistantReply(
          handlers.resolveMessage('partes.emissionDesign.barCode.alreadyPlaced'),
        )
        return
      }

      const noopRemoveDetailYaAusente = details.some((d) =>
        d.includes('removeDetailField:ya_ausente'),
      )
      if ((applyJson.applied ?? 0) === 0 && noopRemoveDetailYaAusente) {
        handlers.onAssistantReply(
          handlers.resolveMessage('partes.emissionDesign.removeDetailField.noop.ya_ausente'),
        )
        return
      }

      // Solo banner Guardar/Descartar si el sidecar dejó backup (pendingReview).
      if (applyJson.pendingReview) {
        handlers.onPendingReview?.(true)
        handlers.onAssistantReply(handlers.resolveMessage('partes.emissionDesign.pendingReviewHint'))
      } else {
        handlers.onAssistantReply(handlers.resolveMessage('partes.emissionDesign.appliedRefresh'))
      }

      handlers.onAppliedRefresh()
      return
    }

    if (result.kind === 'envelopeError') {
      handlers.onError(
        handlers.resolveMessage(result.envelope.respuesta) || result.envelope.respuesta,
      )
      return
    }

    handlers.onError(handlers.resolveMessage('partes.emissionDesign.turnError'))
  } catch {
    handlers.onError(handlers.resolveMessage('partes.emissionDesign.turnError'))
  }
}

export { turnUrlFor }
