import { apiRequest } from '@paqsuite/react-core'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'
import { getAuthToken } from '../../auth/authSessionStore'
import type { TareaListQuery } from '../carga/partesTareaApi'

function authHeaders(): HeadersInit {
  const token = getAuthToken()
  return token ? { Authorization: `Bearer ${token}` } : {}
}

function platform() {
  return buildAuthPlatformHeaders()
}

export type TareaIdItem = {
  id: number
  rowVersion: string
  fecha?: string
  usuarioCode?: string
}

export type MasivoCamposUpdate = {
  tipoTareaId?: number
  sinCargo?: boolean
  presencial?: boolean
  usuarioId?: number
  fecha?: string
}

export async function listTareaIds(query: Omit<TareaListQuery, 'page' | 'pageSize'>) {
  const params = new URLSearchParams({
    fechaDesde: query.fechaDesde,
    fechaHasta: query.fechaHasta,
    estadoCerrado: query.estadoCerrado ?? 'todas',
  })
  if (query.clienteId) {
    params.set('clienteId', String(query.clienteId))
  }
  if (query.usuarioId) {
    params.set('usuarioId', String(query.usuarioId))
  }
  return apiRequest<{ items: TareaIdItem[]; total: number }>(
    `/api/v1/partes/tareas/ids?${params.toString()}`,
    {
      method: 'GET',
      headers: authHeaders(),
      platform: platform(),
    }
  )
}

export async function masivoSetCerrado(
  accion: 'cerrar' | 'reabrir',
  items: Array<{ id: number; rowVersion: string }>
) {
  return apiRequest<{ item: { accion: string; afectados: number; ok: number } }>(
    '/api/v1/partes/tareas/masivo/set-cerrado',
    {
      method: 'POST',
      headers: authHeaders(),
      body: JSON.stringify({ accion, items }),
      platform: platform(),
    }
  )
}

export async function masivoActualizar(
  campos: MasivoCamposUpdate,
  items: Array<{ id: number; rowVersion: string }>
) {
  return apiRequest<{ item: { accion: string; afectados: number; ok: number; campos?: string[] } }>(
    '/api/v1/partes/tareas/masivo/actualizar',
    {
      method: 'POST',
      headers: authHeaders(),
      body: JSON.stringify({ campos, items }),
      platform: platform(),
    }
  )
}
