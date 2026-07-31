import { apiRequest } from '@paqsuite/react-core'
import { buildAuthPlatformHeaders } from '../../auth/platformContext'
import { getAuthToken } from '../../auth/authSessionStore'

export type AdminUsuario = {
  id: number
  usuario: string
  nombre: string
  email: string
  activo: boolean
  inhabilitado: boolean
}

export type AdminEmpresa = {
  id: number
  nombre: string
  activo: boolean
  theme: string
}

export type AdminRol = {
  id: number
  codigo: string
  nombre: string
  accesoTotal: boolean
  activo: boolean
}

export type MenuArbolNode = {
  menuId: number
  padreId: number | null
  titulo: string
  esProceso: boolean
}

export type RolAtributoItem = {
  menuId: number
  permisoAlta: boolean
  permisoBaja: boolean
  permisoModi: boolean
  permisoRepo: boolean
}

export type RolAtributosResultado = {
  accesoTotal: boolean
  codigo: string
  nombre: string
  items: RolAtributoItem[]
  arbol: MenuArbolNode[]
}

export type AdminPermiso = {
  id: number
  userId: number
  usuario: string
  usuarioNombre: string
  empresaId: number
  empresaNombre: string
  rolId: number
  rolCodigo: string
  rolNombre: string
}

function authHeaders(): HeadersInit {
  const token = getAuthToken()
  return token ? { Authorization: `Bearer ${token}` } : {}
}

function platform() {
  return buildAuthPlatformHeaders()
}

function request<T>(path: string, init: RequestInit = {}) {
  return apiRequest<T>(path, {
    ...init,
    headers: authHeaders(),
    platform: platform(),
  })
}

// ---- Usuarios ----

export async function listAdminUsuariosFull(soloActivos: '0' | '1' = '0') {
  return request<{ items: AdminUsuario[] }>(`/api/v1/admin/usuarios?soloActivos=${soloActivos}`)
}

export async function createAdminUsuario(body: {
  usuario: string
  nombre: string
  email: string
  password: string
  activo?: boolean
}) {
  return request<{ item: AdminUsuario }>('/api/v1/admin/usuarios', {
    method: 'POST',
    body: JSON.stringify(body),
  })
}

export async function updateAdminUsuario(
  id: number,
  body: Partial<Pick<AdminUsuario, 'usuario' | 'nombre' | 'email' | 'activo' | 'inhabilitado'>>
) {
  return request<{ item: AdminUsuario }>(`/api/v1/admin/usuarios/${id}`, {
    method: 'PATCH',
    body: JSON.stringify(body),
  })
}

export async function deleteAdminUsuario(id: number) {
  return request<Record<string, never>>(`/api/v1/admin/usuarios/${id}`, {
    method: 'DELETE',
  })
}

// ---- Empresas (MONO: sin alta/baja) ----

export async function listAdminEmpresas() {
  return request<{ items: AdminEmpresa[] }>('/api/v1/admin/empresas')
}

export async function updateAdminEmpresa(
  id: number,
  body: { nombre?: string; activo?: boolean; theme?: string }
) {
  return request<{ item: AdminEmpresa }>(`/api/v1/admin/empresas/${id}`, {
    method: 'PUT',
    body: JSON.stringify(body),
  })
}

// ---- Roles ----

export async function listAdminRoles() {
  return request<{ items: AdminRol[] }>('/api/v1/admin/roles')
}

export async function createAdminRol(body: {
  codigo: string
  nombre: string
  accesoTotal?: boolean
  activo?: boolean
}) {
  return request<{ item: AdminRol }>('/api/v1/admin/roles', {
    method: 'POST',
    body: JSON.stringify(body),
  })
}

export async function updateAdminRol(
  id: number,
  body: Partial<Pick<AdminRol, 'codigo' | 'nombre' | 'accesoTotal' | 'activo'>>
) {
  return request<{ item: AdminRol }>(`/api/v1/admin/roles/${id}`, {
    method: 'PATCH',
    body: JSON.stringify(body),
  })
}

export async function deleteAdminRol(id: number) {
  return request<{ deleted: boolean }>(`/api/v1/admin/roles/${id}`, {
    method: 'DELETE',
  })
}

// ---- Roles: atributos por opción de menú ----

export async function getRolAtributos(rolId: number) {
  return request<RolAtributosResultado>(`/api/v1/admin/roles/${rolId}/atributos`)
}

export async function updateRolAtributos(rolId: number, items: RolAtributoItem[]) {
  return request<RolAtributosResultado>(`/api/v1/admin/roles/${rolId}/atributos`, {
    method: 'PUT',
    body: JSON.stringify({ items }),
  })
}

// ---- Permisos ----

export async function listAdminPermisos() {
  return request<{ items: AdminPermiso[] }>('/api/v1/admin/permisos')
}

export async function listAdminPermisosByUser(userId: number) {
  return request<{ items: AdminPermiso[] }>(`/api/v1/admin/permisos?userId=${userId}`)
}

export async function createAdminPermiso(body: { userId: number; empresaId: number; rolId: number }) {
  return request<{ item: AdminPermiso }>('/api/v1/admin/permisos', {
    method: 'POST',
    body: JSON.stringify(body),
  })
}

export async function batchCreateAdminPermisos(
  items: Array<{ userId: number; empresaId: number; rolId: number }>
) {
  return request<{ creados: number; omitidos: number }>('/api/v1/admin/permisos/batch', {
    method: 'POST',
    body: JSON.stringify({ items }),
  })
}

export async function deleteAdminPermiso(id: number) {
  return request<Record<string, never>>(`/api/v1/admin/permisos/${id}`, {
    method: 'DELETE',
  })
}
