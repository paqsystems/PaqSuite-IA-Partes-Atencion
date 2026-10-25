import type { PaqueteFormState } from './cargaPaquetesFormTypes'

export type PartesPaqueteSmartCaptureDraftContext = {
  processKind: 'paquete'
  mode: 'create' | 'edit'
  id: number | null
  clienteId: number | null
  clienteCode: string | null
  clienteNombre: string | null
  fecha: string | null
  duracionMinutos: number | null
  observacion: string
  esSupervisor: boolean
  tramoMinutos: number
  rowVersion: string | null
}

type CatalogItem = { id: number; code?: string; nombre?: string }

export function buildPartesPaqueteDraftContext(input: {
  form: PaqueteFormState
  editingId: number | null
  esSupervisor: boolean
  clientes: CatalogItem[]
  tramoMinutos?: number
}): PartesPaqueteSmartCaptureDraftContext {
  const { form, editingId, esSupervisor, clientes } = input
  const tramoMinutos = input.tramoMinutos && input.tramoMinutos > 0 ? input.tramoMinutos : 15
  const cliente = clientes.find((item) => Number(item.id) === form.clienteId)

  return {
    processKind: 'paquete',
    mode: editingId === null ? 'create' : 'edit',
    id: editingId,
    clienteId: form.clienteId,
    clienteCode: cliente?.code != null ? String(cliente.code) : null,
    clienteNombre: cliente?.nombre != null ? String(cliente.nombre) : null,
    fecha: form.fecha || null,
    duracionMinutos: form.duracionMinutos,
    observacion: form.observacion,
    esSupervisor,
    tramoMinutos,
    rowVersion: form.rowVersion ?? null,
  }
}
