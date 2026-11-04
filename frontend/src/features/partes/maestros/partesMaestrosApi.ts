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

export type PartesResourceListQuery = {
  page?: number
  pageSize?: number
  code?: string
  nombre?: string
  descripcion?: string
  usuario?: string
  email?: string
}

export async function listPartesResource(path: string, query: PartesResourceListQuery = {}) {
  const params = new URLSearchParams()
  if (query.page != null) {
    params.set('page', String(query.page))
  }
  if (query.pageSize != null) {
    params.set('pageSize', String(query.pageSize))
  }
  if (query.code) {
    params.set('code', query.code)
  }
  if (query.nombre) {
    params.set('nombre', query.nombre)
  }
  if (query.descripcion) {
    params.set('descripcion', query.descripcion)
  }
  if (query.usuario) {
    params.set('usuario', query.usuario)
  }
  if (query.email) {
    params.set('email', query.email)
  }
  const qs = params.toString()
  return apiRequest<{ items: Record<string, unknown>[]; total?: number }>(
    `/api/v1/partes/${path}${qs ? `?${qs}` : ''}`,
    {
      method: 'GET',
      headers: authHeaders(),
      platform: platform(),
    },
  )
}

export async function savePartesResource(
  path: string,
  body: Record<string, unknown>,
  id?: number
) {
  return apiRequest<{ item: Record<string, unknown> }>(
    id ? `/api/v1/partes/${path}/${id}` : `/api/v1/partes/${path}`,
    {
      method: id ? 'PUT' : 'POST',
      headers: authHeaders(),
      body: JSON.stringify(body),
      platform: platform(),
    }
  )
}

export async function deletePartesResource(path: string, id: number) {
  return apiRequest<Record<string, never>>(`/api/v1/partes/${path}/${id}`, {
    method: 'DELETE',
    headers: authHeaders(),
    platform: platform(),
  })
}

export async function listUsuariosVinculables(exceptoUserId?: number | null) {
  const params = new URLSearchParams()
  if (exceptoUserId != null && exceptoUserId > 0) {
    params.set('exceptoUserId', String(exceptoUserId))
  }
  const query = params.toString()
  return apiRequest<{ items: Array<{ id: number; codigo: string; nombre: string }> }>(
    `/api/v1/partes/catalogos/usuarios-vinculables${query ? `?${query}` : ''}`,
    {
      method: 'GET',
      headers: authHeaders(),
      platform: platform(),
    }
  )
}

export async function listAdminUsuarios(soloActivos = '1') {
  return apiRequest<{ items: Array<{ id: number; codigo: string; usuario?: string; nombre: string }> }>(
    `/api/v1/admin/usuarios?soloActivos=${soloActivos}`,
    {
      method: 'GET',
      headers: authHeaders(),
      platform: platform(),
    }
  )
}

export async function listCatalogo(path: string, query = '') {
  return apiRequest<{ items: Record<string, unknown>[] }>(
    `/api/v1/partes/catalogos/${path}${query}`,
    {
      method: 'GET',
      headers: authHeaders(),
      platform: platform(),
    }
  )
}
