import { describe, expect, it, vi } from 'vitest'
import { buildPartesDraftContext } from './buildPartesDraftContext'
import {
  handlePartesSmartCaptureSend,
  resolveSmartCaptureReplyText,
} from './partesSmartCaptureTurn'
import type { FormState } from './cargaDiariaFormTypes'

vi.mock('@paqsuite/react-core', async () => {
  const actual = await vi.importActual<typeof import('@paqsuite/react-core')>('@paqsuite/react-core')
  return {
    ...actual,
    postSmartCaptureTurn: vi.fn(),
  }
})

import { postSmartCaptureTurn } from '@paqsuite/react-core'

describe('buildPartesDraftContext', () => {
  it('arma snapshot camelCase del form', () => {
    const form: FormState = {
      usuarioId: 7,
      clienteId: 3,
      tipoTareaId: 9,
      fecha: '2026-08-03',
      duracionMinutos: 30,
      sinCargo: false,
      presencial: true,
      observacion: 'demo',
      rowVersion: 'abc',
    }
    const draft = buildPartesDraftContext({
      form,
      editingId: 12,
      cerrado: false,
      esSupervisor: true,
      clientes: [{ id: 3, code: 'CL1', nombre: 'Cliente' }],
      asistentes: [{ id: 7, code: 'A1', nombre: 'Asist' }],
      tipos: [{ id: 9, code: 'T1', descripcion: 'Tipo' }],
    })
    expect(draft.mode).toBe('edit')
    expect(draft.id).toBe(12)
    expect(draft.clienteCode).toBe('CL1')
    expect(draft.asistenteId).toBe(7)
    expect(draft.tipoTareaCode).toBe('T1')
    expect(draft.presencial).toBe(true)
  })
})

describe('handlePartesSmartCaptureSend', () => {
  it('aplica setField clienteId y dispara save', async () => {
    const post = vi.mocked(postSmartCaptureTurn)
    post.mockResolvedValue({
      kind: 'ok',
      envelope: {
        error: 0,
        respuesta: 'ok',
        resultado: {
          replyText: 'partes.smartCapture.ok',
          actions: [
            { action: 'setField', payload: { field: 'clienteId', value: 5 }, resultado: 'ok' },
            { action: 'setField', payload: { field: 'duracionMinutos', value: 45 }, resultado: 'ok' },
            { action: 'save', payload: {}, resultado: 'ok' },
          ],
          pendingChoice: null,
          configurationRequired: false,
        },
      },
    } as never)

    const form: FormState = {
      usuarioId: 1,
      clienteId: null,
      tipoTareaId: null,
      fecha: '2026-08-03',
      duracionMinutos: 15,
      sinCargo: false,
      presencial: false,
      observacion: '',
    }
    let nextForm = form
    const onClienteIdChange = vi.fn(async (id: number | null) => {
      nextForm = { ...nextForm, clienteId: id }
    })
    const onSave = vi.fn(async () => undefined)
    const onAssistantReply = vi.fn()
    const setPendingChoice = vi.fn()

    await handlePartesSmartCaptureSend(
      {
        message: 'guardar',
        modality: 'texto',
        images: [],
        credentialId: 10,
      },
      {
        form,
        editingId: null,
        cerrado: false,
        esSupervisor: false,
        clientes: [],
        asistentes: [],
        tipos: [],
        pendingChoice: null,
        activeCredentialId: 10,
        supportsVision: false,
        setForm: (updater) => {
          nextForm = updater(nextForm)
        },
        setPendingChoice,
        onClienteIdChange,
        onSave,
        onAssistantReply,
        onError: vi.fn(),
        resolveMessage: (key) => key,
      }
    )

    expect(onClienteIdChange).toHaveBeenCalledWith(5)
    expect(nextForm.duracionMinutos).toBe(45)
    expect(onSave).toHaveBeenCalledTimes(1)
    expect(onAssistantReply).toHaveBeenCalled()
    expect(setPendingChoice).toHaveBeenCalledWith(null)
  })
})

describe('resolveSmartCaptureReplyText', () => {
  it('traduce claves sueltas y numeración de opciones', () => {
    const resolve = (key: string) =>
      key === 'partes.smartCapture.clienteAmbiguo'
        ? 'Hay varios clientes posibles; elegí una opción numerada.'
        : key

    const text = resolveSmartCaptureReplyText(
      'partes.smartCapture.clienteAmbiguo\n1 — FLEXO — Flexo\n2 — FLEXOPLAST — Flexoplast',
      resolve
    )

    expect(text).toContain('Hay varios clientes posibles')
    expect(text).toContain('1 — FLEXO — Flexo')
    expect(text).not.toContain('partes.smartCapture.clienteAmbiguo')
  })
})
