import { describe, expect, it, vi } from 'vitest'
import { buildPartesPaqueteDraftContext } from './buildPartesPaqueteDraftContext'
import { handlePartesPaqueteSmartCaptureSend } from './partesPaqueteSmartCaptureTurn'
import type { PaqueteFormState } from './cargaPaquetesFormTypes'

vi.mock('@paqsuite/react-core', async () => {
  const actual = await vi.importActual<typeof import('@paqsuite/react-core')>('@paqsuite/react-core')
  return {
    ...actual,
    postSmartCaptureTurn: vi.fn(),
  }
})

import { postSmartCaptureTurn } from '@paqsuite/react-core'

describe('buildPartesPaqueteDraftContext', () => {
  it('marca processKind paquete y campos reducidos', () => {
    const form: PaqueteFormState = {
      clienteId: 3,
      fecha: '2026-10-21',
      duracionMinutos: 8 * 60,
      observacion: 'Compra',
      rowVersion: 'AA',
    }
    const draft = buildPartesPaqueteDraftContext({
      form,
      editingId: 9,
      esSupervisor: true,
      clientes: [{ id: 3, code: 'CL1', nombre: 'Cliente' }],
    })
    expect(draft.processKind).toBe('paquete')
    expect(draft.mode).toBe('edit')
    expect(draft.clienteCode).toBe('CL1')
    expect(draft.duracionMinutos).toBe(8 * 60)
    expect(draft).not.toHaveProperty('asistenteId')
  })
})

describe('handlePartesPaqueteSmartCaptureSend', () => {
  it('aplica setField y save llama onSave (API paquetes)', async () => {
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
            { action: 'setField', payload: { field: 'duracionMinutos', value: 10 * 60 }, resultado: 'ok' },
            { action: 'save', payload: {}, resultado: 'ok' },
          ],
          pendingChoice: null,
          configurationRequired: false,
        },
      },
    } as never)

    const form: PaqueteFormState = {
      clienteId: null,
      fecha: '2026-10-21',
      duracionMinutos: 8 * 60,
      observacion: '',
    }
    let nextForm = form
    const onSave = vi.fn(async () => undefined)

    await handlePartesPaqueteSmartCaptureSend(
      {
        message: 'guardar',
        modality: 'texto',
        images: [],
        credentialId: 10,
      },
      {
        form,
        editingId: null,
        esSupervisor: true,
        clientes: [],
        pendingChoice: null,
        activeCredentialId: 10,
        supportsVision: false,
        setForm: (updater) => {
          nextForm = updater(nextForm)
        },
        setPendingChoice: vi.fn(),
        onSave,
        onAssistantReply: vi.fn(),
        onError: vi.fn(),
        resolveMessage: (key) => key,
      }
    )

    expect(nextForm.clienteId).toBe(5)
    expect(nextForm.duracionMinutos).toBe(10 * 60)
    expect(onSave).toHaveBeenCalledTimes(1)
    const body = post.mock.calls[0]?.[1] as { draftContext?: { processKind?: string } }
    expect(body?.draftContext?.processKind).toBe('paquete')
  })
})
