import { apiRequest } from '@paqsuite/react-core'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'
import { getAuthToken } from '../../auth/authSessionStore'

function authHeaders(): HeadersInit {
  const token = getAuthToken()
  return token ? { Authorization: `Bearer ${token}` } : {}
}

function platform() {
  return buildAuthPlatformHeaders()
}

export type PartesTareaItem = {
  id: number
  usuarioId: number
  clienteId: number
  tipoTareaId: number
  fecha: string
  duracionMinutos: number
  sinCargo: boolean
  presencial: boolean
  observacion: string
  cerrado: boolean
  rowVersion: string
  usuarioCode?: string
  usuarioNombre?: string
  clienteCode?: string
  clienteNombre?: string
  tipoTareaCode?: string
  tipoTareaDescripcion?: string
}

export type TareaListQuery = {
  fechaDesde: string
  fechaHasta: string
  clienteId?: number | null
  usuarioId?: number | null
  estadoCerrado?: 'todas' | 'abiertas' | 'cerradas'
  page?: number
  pageSize?: number
}

export async function fetchDuracionTramo() {
  return apiRequest<{ tramoMinutos: number }>('/api/v1/partes/parametros/duracion-tramo', {
    method: 'GET',
    headers: authHeaders(),
    platform: platform(),
  })
}

export async function listTareas(query: TareaListQuery) {
  const params = new URLSearchParams({
    fechaDesde: query.fechaDesde,
    fechaHasta: query.fechaHasta,
    estadoCerrado: query.estadoCerrado ?? 'todas',
    page: String(query.page ?? 1),
    pageSize: String(query.pageSize ?? 50),
  })
  if (query.clienteId) {
    params.set('clienteId', String(query.clienteId))
  }
  if (query.usuarioId) {
    params.set('usuarioId', String(query.usuarioId))
  }
  return apiRequest<{ items: PartesTareaItem[]; total: number }>(
    `/api/v1/partes/tareas?${params.toString()}`,
    {
      method: 'GET',
      headers: authHeaders(),
      platform: platform(),
    }
  )
}

export async function saveTarea(
  body: Record<string, unknown>,
  id?: number
) {
  return apiRequest<{ item: PartesTareaItem }>(
    id ? `/api/v1/partes/tareas/${id}` : '/api/v1/partes/tareas',
    {
      method: id ? 'PUT' : 'POST',
      headers: authHeaders(),
      body: JSON.stringify(body),
      platform: platform(),
    }
  )
}

export async function deleteTarea(id: number, rowVersion: string) {
  return apiRequest<Record<string, never>>(`/api/v1/partes/tareas/${id}`, {
    method: 'DELETE',
    headers: authHeaders(),
    body: JSON.stringify({ rowVersion }),
    platform: platform(),
  })
}

export async function setTareaCerrado(id: number, rowVersion: string, cerrar: boolean) {
  const action = cerrar ? 'cerrar' : 'reabrir'
  return apiRequest<{ item: PartesTareaItem }>(`/api/v1/partes/tareas/${id}/${action}`, {
    method: 'POST',
    headers: authHeaders(),
    body: JSON.stringify({ rowVersion }),
    platform: platform(),
  })
}
