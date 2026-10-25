import {
  applySmartCaptureActions,
  buildSmartCaptureTurnRequest,
  postSmartCaptureTurn,
  type PendingChoice,
  type SmartCaptureSendPayload,
  type SmartCaptureTurnAction,
} from '@paqsuite/react-core'
import type { PaqueteFormState } from './cargaPaquetesFormTypes'
import { buildPartesPaqueteDraftContext } from './buildPartesPaqueteDraftContext'
import { parseDuracionToMinutos } from './partesTareaDuration'
import { isValidPaqueteDuracionMinutos } from './partesPaqueteDuration'
import { resolveSmartCaptureReplyText } from './partesSmartCaptureTurn'

const turnUrl = '/api/v1/partes/tareas/asistente/turn'

const setFieldMap: Record<string, keyof PaqueteFormState> = {
  clienteId: 'clienteId',
  fecha: 'fecha',
  duracionMinutos: 'duracionMinutos',
  observacion: 'observacion',
}

export type PartesPaqueteSmartCaptureHandlers = {
  form: PaqueteFormState
  editingId: number | null
  esSupervisor: boolean
  clientes: Array<{ id: number; code?: string; nombre?: string }>
  tramoMinutos?: number
  pendingChoice: PendingChoice | null
  activeCredentialId: number | null
  supportsVision: boolean
  setForm: (updater: (prev: PaqueteFormState) => PaqueteFormState) => void
  setPendingChoice: (next: PendingChoice | null) => void
  onSave: () => void | Promise<void>
  onAssistantReply: (text: string) => void
  onError: (message: string) => void
  resolveMessage: (key: string) => string
  signal?: AbortSignal
}

export async function handlePartesPaqueteSmartCaptureSend(
  payload: SmartCaptureSendPayload,
  handlers: PartesPaqueteSmartCaptureHandlers
): Promise<void> {
  if (payload.credentialId === null || handlers.activeCredentialId === null) {
    handlers.onError(handlers.resolveMessage('smartCapture.configurationRequired'))
    return
  }

  const draftContext = buildPartesPaqueteDraftContext({
    form: handlers.form,
    editingId: handlers.editingId,
    esSupervisor: handlers.esSupervisor,
    clientes: handlers.clientes,
    tramoMinutos: handlers.tramoMinutos,
  })

  const body = buildSmartCaptureTurnRequest({
    message: payload.message,
    modality: payload.modality,
    credentialId: payload.credentialId,
    draftContext,
    pendingChoice: handlers.pendingChoice,
    images: payload.images,
    supportsVision: handlers.supportsVision,
  })

  const result = await postSmartCaptureTurn(turnUrl, body, { signal: handlers.signal })
  if (result.kind === 'ok') {
    const turn = result.envelope.resultado
    handlers.onAssistantReply(
      resolveSmartCaptureReplyText(turn.replyText, handlers.resolveMessage)
    )
    handlers.setPendingChoice(turn.pendingChoice)
    await applySmartCaptureActions(turn.actions ?? [], {
      applyAction: async (action) => {
        await applyPaqueteAction(action, handlers)
      },
    })
    return
  }

  if (result.kind === 'envelopeError') {
    handlers.onError(handlers.resolveMessage(result.envelope.respuesta) || result.envelope.respuesta)
    return
  }
  handlers.onError(handlers.resolveMessage('partes.smartCapture.turnError'))
}

async function applyPaqueteAction(
  action: SmartCaptureTurnAction,
  handlers: PartesPaqueteSmartCaptureHandlers
): Promise<void> {
  if (action.action === 'save') {
    await handlers.onSave()
    return
  }
  if (action.action === 'setField') {
    const fieldKey = String(action.payload.field ?? '')
    const mapped = setFieldMap[fieldKey]
    if (!mapped) {
      return
    }
    const value = action.payload.value
    if (mapped === 'duracionMinutos') {
      const parsed = parseDuracionToMinutos(value)
      if (parsed == null || !isValidPaqueteDuracionMinutos(parsed)) {
        return
      }
      handlers.setForm((prev) => ({ ...prev, duracionMinutos: parsed }))
      return
    }
    if (mapped === 'clienteId') {
      handlers.setForm((prev) => ({
        ...prev,
        clienteId: value == null ? null : Number(value),
      }))
      return
    }
    handlers.setForm((prev) => ({
      ...prev,
      [mapped]: value == null ? '' : String(value),
    }))
  }
}
