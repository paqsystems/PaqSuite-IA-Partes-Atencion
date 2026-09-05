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

export async function listPartesResource(path: string) {
  return apiRequest<{ items: Record<string, unknown>[]; total?: number }>(`/api/v1/partes/${path}`, {
    method: 'GET',
    headers: authHeaders(),
    platform: platform(),
  })
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
