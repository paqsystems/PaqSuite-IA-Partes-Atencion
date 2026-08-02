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

export async function fetchDashboardParametros() {
  return apiRequest<{ topN: number; refreshSeg: number }>('/api/v1/partes/parametros/dashboard', {
    method: 'GET',
    headers: authHeaders(),
    platform: platform(),
  })
}

export async function fetchDashboard(query: { mes?: string; fechaDesde?: string; fechaHasta?: string }) {
  const params = new URLSearchParams()
  if (query.mes) {
    params.set('mes', query.mes)
  }
  if (query.fechaDesde) {
    params.set('fechaDesde', query.fechaDesde)
  }
  if (query.fechaHasta) {
    params.set('fechaHasta', query.fechaHasta)
  }
  return apiRequest<{
    totalMinutos: number
    cantidadTareas: number
    top: Array<{
      clave: number
      codigo: string
      descripcion: string
      totalMinutos: number
      cantidadTareas: number
    }>
    fechaDesde: string
    fechaHasta: string
  }>(`/api/v1/partes/dashboard?${params.toString()}`, {
    method: 'GET',
    headers: authHeaders(),
    platform: platform(),
  })
}

export async function fetchInformeTareas(query: Record<string, string>) {
  const params = new URLSearchParams(query)
  return apiRequest<{ items: Record<string, unknown>[]; total: number }>(
    `/api/v1/partes/informes/tareas?${params.toString()}`,
    { method: 'GET', headers: authHeaders(), platform: platform() }
  )
}

export async function fetchInformeAgrupado(query: Record<string, string>) {
  const params = new URLSearchParams(query)
  return apiRequest<{ items: Record<string, unknown>[]; total: number }>(
    `/api/v1/partes/informes/agrupado?${params.toString()}`,
    { method: 'GET', headers: authHeaders(), platform: platform() }
  )
}

export async function fetchPaqueteHoras(query: {
  fechaDesde: string
  fechaHasta: string
  clienteId?: number | null
}) {
  const params = new URLSearchParams({
    fechaDesde: query.fechaDesde,
    fechaHasta: query.fechaHasta,
  })
  if (query.clienteId != null) {
    params.set('clienteId', String(query.clienteId))
  }
  return apiRequest<{
    items: Record<string, unknown>[]
    total: number
    saldoInicial: number
    fechaDesde: string
    fechaHasta: string
  }>(`/api/v1/partes/informes/paquete-horas?${params.toString()}`, {
    method: 'GET',
    headers: authHeaders(),
    platform: platform(),
  })
}
