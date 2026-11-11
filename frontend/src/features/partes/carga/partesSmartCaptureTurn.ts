import {
  applySmartCaptureActions,
  buildSmartCaptureTurnRequest,
  postSmartCaptureTurn,
  type PendingChoice,
  type SmartCaptureSendPayload,
  type SmartCaptureTurnAction,
} from '@paqsuite/react-core'
import type { FormState } from './cargaDiariaFormTypes'
import { buildPartesDraftContext } from './buildPartesDraftContext'
import { isValidDuracionMinutos, parseDuracionToMinutos } from './partesTareaDuration'

const turnUrl = '/api/v1/partes/tareas/asistente/turn'

const setFieldMap: Record<string, keyof FormState> = {
  clienteId: 'clienteId',
  asistenteId: 'usuarioId',
  tipoTareaId: 'tipoTareaId',
  fecha: 'fecha',
  duracionMinutos: 'duracionMinutos',
  observacion: 'observacion',
  sinCargo: 'sinCargo',
  presencial: 'presencial',
}

export type PartesSmartCaptureTurnHandlers = {
  form: FormState
  editingId: number | null
  cerrado: boolean
  esSupervisor: boolean
  clientes: Array<{ id: number; code?: string; nombre?: string }>
  asistentes: Array<{ id: number; code?: string; nombre?: string }>
  tipos: Array<{ id: number; code?: string; descripcion?: string }>
  tramoMinutos?: number
  locale?: string | null
  pendingChoice: PendingChoice | null
  activeCredentialId: number | null
  supportsVision: boolean
  setForm: (updater: (prev: FormState) => FormState) => void
  setPendingChoice: (next: PendingChoice | null) => void
  onClienteIdChange: (clienteId: number | null) => void | Promise<void>
  onSave: () => void | Promise<void>
  onAssistantReply: (text: string) => void
  onError: (message: string) => void
  resolveMessage: (key: string) => string
  signal?: AbortSignal
}

export async function handlePartesSmartCaptureSend(
  payload: SmartCaptureSendPayload,
  handlers: PartesSmartCaptureTurnHandlers
): Promise<void> {
  if (payload.credentialId === null || handlers.activeCredentialId === null) {
    handlers.onError(handlers.resolveMessage('smartCapture.configurationRequired'))
    return
  }

  const draftContext = buildPartesDraftContext({
    form: handlers.form,
    editingId: handlers.editingId,
    cerrado: handlers.cerrado,
    esSupervisor: handlers.esSupervisor,
    clientes: handlers.clientes,
    asistentes: handlers.asistentes,
    tipos: handlers.tipos,
    tramoMinutos: handlers.tramoMinutos,
    locale: handlers.locale,
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

  try {
    const result = await postSmartCaptureTurn(turnUrl, body, { signal: handlers.signal })
    if (result.kind === 'ok') {
      const turn = result.envelope.resultado
      const resolvedReply = resolveSmartCaptureReplyText(turn.replyText, handlers.resolveMessage)
      if (isSmartCaptureHardFailure(turn.replyText, turn.actions ?? [])) {
        handlers.onError(resolvedReply)
        handlers.setPendingChoice(turn.pendingChoice)
        return
      }
      handlers.onAssistantReply(resolvedReply)
      handlers.setPendingChoice(turn.pendingChoice)
      await applySmartCaptureActions(turn.actions ?? [], {
        applyAction: async (action) => {
          await applyPartesAction(action, handlers)
        },
      })
      return
    }

    if (result.kind === 'envelopeError') {
      handlers.onError(handlers.resolveMessage(result.envelope.respuesta) || result.envelope.respuesta)
      return
    }

    const transportKey =
      result.kind === 'transportError' && result.httpStatus && result.httpStatus < 500
        ? result.i18nKey || 'infra.transport'
        : 'partes.smartCapture.turnError'
    handlers.onError(handlers.resolveMessage(transportKey) || handlers.resolveMessage('partes.smartCapture.turnError'))
  } catch {
    handlers.onError(handlers.resolveMessage('partes.smartCapture.turnError'))
  }
}

export function isSmartCaptureHardFailure(
  replyText: string,
  actions: SmartCaptureTurnAction[]
): boolean {
  const onlyNoop = actions.length === 0 || actions.every((action) => action.action === 'noop')
  return onlyNoop && /(?:^|\s)partes\.smartCapture\.turnError(?:\s|$)/.test(replyText.trim())
}

/** Traduce claves `partes.smartCapture.*` / `smartCapture.*` sueltas o incrustadas en el reply. */
export function resolveSmartCaptureReplyText(
  text: string,
  resolveMessage: (key: string) => string
): string {
  const resolveKey = (key: string): string => {
    const resolved = resolveMessage(key)
    return resolved || key
  }

  return text
    .split('\n')
    .map((line) => {
      const trimmed = line.trim()
      if (/^(?:partes\.)?smartCapture\.[A-Za-z0-9.]+$/.test(trimmed)) {
        return resolveKey(trimmed)
      }
      return line.replace(/\b((?:partes\.)?smartCapture\.[A-Za-z0-9.]+)\b/g, (key) =>
        resolveKey(key)
      )
    })
    .join('\n')
}

export async function applyPartesAction(
  action: SmartCaptureTurnAction,
  handlers: PartesSmartCaptureTurnHandlers
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
    if (mapped === 'clienteId') {
      await handlers.onClienteIdChange(value == null ? null : Number(value))
      return
    }
    if (mapped === 'duracionMinutos') {
      const parsed = parseDuracionToMinutos(value)
      const tramo = handlers.tramoMinutos ?? 15
      if (parsed == null || !isValidDuracionMinutos(parsed, tramo)) {
        return
      }
      handlers.setForm((prev) => ({ ...prev, duracionMinutos: parsed }))
      return
    }
    handlers.setForm((prev) => ({
      ...prev,
      [mapped]: coerceFieldValue(mapped, value, handlers.tramoMinutos ?? 15),
    }))
  }
}

function coerceFieldValue(
  field: keyof FormState,
  value: unknown,
  tramoMinutos: number
): FormState[keyof FormState] {
  if (field === 'sinCargo' || field === 'presencial') {
    return Boolean(value)
  }
  if (field === 'duracionMinutos') {
    const parsed = parseDuracionToMinutos(value)
    if (parsed == null || !isValidDuracionMinutos(parsed, tramoMinutos)) {
      return tramoMinutos
    }
    return parsed
  }
  if (field === 'usuarioId' || field === 'clienteId' || field === 'tipoTareaId') {
    return value == null || value === '' ? null : Number(value)
  }
  return value == null ? '' : String(value)
}
