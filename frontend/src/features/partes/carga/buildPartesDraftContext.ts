import type { FormState } from './cargaDiariaFormTypes'

export type PartesSmartCaptureDraftContext = {
  mode: 'create' | 'edit'
  id: number | null
  cerrado: boolean
  clienteId: number | null
  clienteCode: string | null
  clienteNombre: string | null
  asistenteId: number | null
  asistenteCode: string | null
  tipoTareaId: number | null
  tipoTareaCode: string | null
  fecha: string | null
  duracionMinutos: number | null
  observacion: string
  sinCargo: boolean
  presencial: boolean
  esSupervisor: boolean
  rowVersion: string | null
}

type CatalogItem = { id: number; code?: string; nombre?: string; descripcion?: string }

export function buildPartesDraftContext(input: {
  form: FormState
  editingId: number | null
  cerrado: boolean
  esSupervisor: boolean
  clientes: CatalogItem[]
  asistentes: CatalogItem[]
  tipos: CatalogItem[]
}): PartesSmartCaptureDraftContext {
  const { form, editingId, cerrado, esSupervisor, clientes, asistentes, tipos } = input
  const cliente = clientes.find((item) => Number(item.id) === form.clienteId)
  const asistente = asistentes.find((item) => Number(item.id) === form.usuarioId)
  const tipo = tipos.find((item) => Number(item.id) === form.tipoTareaId)

  return {
    mode: editingId === null ? 'create' : 'edit',
    id: editingId,
    cerrado,
    clienteId: form.clienteId,
    clienteCode: cliente?.code != null ? String(cliente.code) : null,
    clienteNombre: cliente?.nombre != null ? String(cliente.nombre) : null,
    asistenteId: form.usuarioId,
    asistenteCode: asistente?.code != null ? String(asistente.code) : null,
    tipoTareaId: form.tipoTareaId,
    tipoTareaCode: tipo?.code != null ? String(tipo.code) : null,
    fecha: form.fecha || null,
    duracionMinutos: form.duracionMinutos,
    observacion: form.observacion,
    sinCargo: form.sinCargo,
    presencial: form.presencial,
    esSupervisor,
    rowVersion: form.rowVersion ?? null,
  }
}
