import { expect, type APIRequestContext, type Locator } from '@playwright/test'
import { ensurePartesBackendHealth, loginPartesApi } from './partesPaqueteHorasE2e'

export const PROCESS_INACTIVE_ERROR = 4405
export const SIMULATION_NOT_SUPPORTED_ERROR = 4406

const tenantHeader = { 'X-Paq-Cliente': 'DEMO', 'X-Company-Id': '1' }

export async function buildTaskAuthHeaders(request: APIRequestContext): Promise<Record<string, string>> {
  await ensurePartesBackendHealth(request)
  const token = await loginPartesApi(request)
  return {
    ...tenantHeader,
    Authorization: `Bearer ${token}`,
    'Content-Type': 'application/json',
  }
}

type TaskProcessItem = { id: number; processCode: string; isActive: boolean }

export async function findBackupProcessId(
  request: APIRequestContext,
  headers: Record<string, string>,
): Promise<number> {
  const res = await request.get('/api/v1/task-processes', { headers })
  expect(res.ok(), `task-processes ${res.status()}`).toBeTruthy()
  const body = await res.json()
  const items = (body?.resultado?.items ?? []) as TaskProcessItem[]
  const backup = items.find((p) => p.processCode === 'BACKUP_DATABASE')
  expect(backup?.id, 'proceso BACKUP_DATABASE en seed').toBeTruthy()
  return backup!.id
}

export type CreatedDefinition = { id: number; taskTitle: string; processId: number }

export async function createInactiveDefinition(
  request: APIRequestContext,
  headers: Record<string, string>,
  processId: number,
  taskTitle: string,
): Promise<CreatedDefinition> {
  const today = new Date()
  const baseDate = today.toISOString().slice(0, 10)
  const res = await request.post('/api/v1/task-definitions', {
    headers,
    data: {
      processId,
      taskTitle,
      taskDescription: 'E2E CC-FW-20 GEN-13',
      isActive: false,
      frequencyType: 'DAILY',
      frequencyInterval: 1,
      baseDate,
      baseTime: '08:00',
      companyIds: [1],
    },
  })
  expect(res.status(), `create definition ${await res.text()}`).toBe(201)
  const body = await res.json()
  const id = Number(body?.resultado?.id)
  expect(id).toBeGreaterThan(0)
  return { id, taskTitle, processId }
}

export async function deleteDefinition(
  request: APIRequestContext,
  headers: Record<string, string>,
  definitionId: number,
): Promise<void> {
  const res = await request.delete(`/api/v1/task-definitions/${definitionId}`, { headers })
  expect(res.ok(), `delete definition ${definitionId} → ${res.status()}`).toBeTruthy()
}

export async function patchProcessActive(
  request: APIRequestContext,
  headers: Record<string, string>,
  processId: number,
  isActive: boolean,
): Promise<void> {
  const res = await request.patch(`/api/v1/task-processes/${processId}`, {
    headers,
    data: { isActive },
  })
  expect(res.ok(), `patch process ${processId} isActive=${isActive} → ${res.status()}`).toBeTruthy()
}

export async function postDefinitionRun(
  request: APIRequestContext,
  headers: Record<string, string>,
  definitionId: number,
): Promise<{ status: number; error: number | null; respuesta: string | null }> {
  const res = await request.post(`/api/v1/task-definitions/${definitionId}/run`, { headers })
  const body = await res.json().catch(() => ({}))
  const errorRaw = body?.error
  const error = typeof errorRaw === 'number' ? errorRaw : errorRaw == null ? null : Number(errorRaw)
  return {
    status: res.status(),
    error: Number.isFinite(error) ? error : null,
    respuesta: typeof body?.respuesta === 'string' ? body.respuesta : null,
  }
}

export function uniqueTaskTitle(prefix: string): string {
  return `${prefix}-${Date.now().toString(36)}`
}

export async function isDxDisabled(locator: Locator): Promise<boolean> {
  return locator.evaluate(
    (el) =>
      el.getAttribute('aria-disabled') === 'true' ||
      el.classList.contains('dx-state-disabled') ||
      (el as HTMLButtonElement).disabled === true,
  )
}