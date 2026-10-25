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

export type PartesPaqueteItem = {
  id: number
  usuarioId: number
  clienteId: number
  fecha: string
  duracionMinutos: number
  observacion: string
  esTarea: boolean
  rowVersion: string
  usuarioCode?: string
  usuarioNombre?: string
  clienteCode?: string
  clienteNombre?: string
}

export type PaqueteListQuery = {
  fechaDesde: string
  fechaHasta: string
  clienteId?: number | null
  page?: number
  pageSize?: number
}

export async function listPaquetes(query: PaqueteListQuery) {
  const params = new URLSearchParams({
    fechaDesde: query.fechaDesde,
    fechaHasta: query.fechaHasta,
    page: String(query.page ?? 1),
    pageSize: String(query.pageSize ?? 50),
  })
  if (query.clienteId) {
    params.set('clienteId', String(query.clienteId))
  }
  return apiRequest<{ items: PartesPaqueteItem[]; total: number }>(
    `/api/v1/partes/paquetes?${params.toString()}`,
    {
      method: 'GET',
      headers: authHeaders(),
      platform: platform(),
    }
  )
}

export async function savePaquete(body: Record<string, unknown>, id?: number) {
  return apiRequest<{ item: PartesPaqueteItem }>(
    id ? `/api/v1/partes/paquetes/${id}` : '/api/v1/partes/paquetes',
    {
      method: id ? 'PUT' : 'POST',
      headers: authHeaders(),
      body: JSON.stringify(body),
      platform: platform(),
    }
  )
}

export async function deletePaquete(id: number, rowVersion: string) {
  return apiRequest<Record<string, never>>(`/api/v1/partes/paquetes/${id}`, {
    method: 'DELETE',
    headers: authHeaders(),
    body: JSON.stringify({ rowVersion }),
    platform: platform(),
  })
}
