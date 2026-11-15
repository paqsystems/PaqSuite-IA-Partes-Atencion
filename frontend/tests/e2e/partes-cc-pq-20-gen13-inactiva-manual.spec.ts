import { expect, test } from '@playwright/test'
import {
  PROCESS_INACTIVE_ERROR,
  buildTaskAuthHeaders,
  createInactiveDefinition,
  deleteDefinition,
  findBackupProcessId,
  isDxDisabled,
  patchProcessActive,
  postDefinitionRun,
  uniqueTaskTitle,
} from './helpers/tareasProgramadasGen13E2e'

/**
 * CC Framework PQ #20 — GEN-13: definición `isActive=false` no debe deshabilitar Ejecutar/Simular en UI
 * ni bloquear run/simulate manual vía API; solo el proceso de catálogo inactivo → 4405.
 *
 * Requiere backend en VITE_API_PROXY_TARGET (p. ej. :8010) y `PW_INTEGRATION=1`.
 * Credenciales: `PW_LOGIN_USER` / `PW_LOGIN_PASSWORD` (DEMO/ERP suele ser `PQ` / `Paqsystems26*`; seed lite `admin` / `Paqsystems`).
 */
test.describe('CC #20 — GEN-13 definición inactiva, manual habilitado', () => {
  test.skip(() => process.env.PW_INTEGRATION !== '1', 'Solo con PW_INTEGRATION=1')

  test.beforeEach(async () => {
    test.setTimeout(120_000)
  })

  test('CA-FW-20-01: UI — run y simulate habilitados con definición inactiva', async ({ page, request }) => {
    const headers = await buildTaskAuthHeaders(request)
    const processId = await findBackupProcessId(request, headers)
    const taskTitle = uniqueTaskTitle('e2e-cc20-ui')
    const created = await createInactiveDefinition(request, headers, processId, taskTitle)

    try {
      await page.goto('/login?cliente=DEMO')
      await page.getByTestId('loginUsuario').locator('input').fill(process.env.PW_LOGIN_USER ?? 'admin')
      await page
        .getByTestId('loginPassword')
        .locator('input')
        .fill(process.env.PW_LOGIN_PASSWORD ?? 'Paqsystems')
      await page.getByTestId('loginSubmit').click()
      await expect(page).toHaveURL(/\/partes\/?/, { timeout: 45_000 })

      await page.goto('/tareas-programadas/definiciones')
      await expect(page.getByTestId('tareasProgramadas.definiciones')).toBeVisible({ timeout: 30_000 })
      await expect(page.getByTestId('tareasProgramadas.definiciones.grid')).toBeVisible({ timeout: 30_000 })

      const row = page.locator('.dx-datagrid-rowsview .dx-data-row').filter({ hasText: taskTitle })
      await expect(row).toBeVisible({ timeout: 45_000 })

      const runBtn = row.getByTestId('tareasProgramadas.definiciones.run')
      const simulateBtn = row.getByTestId('tareasProgramadas.definiciones.simulate')

      await expect(runBtn).toBeVisible()
      expect(await isDxDisabled(runBtn)).toBe(false)

      if (await simulateBtn.count()) {
        await expect(simulateBtn.first()).toBeVisible()
        expect(await isDxDisabled(simulateBtn.first())).toBe(false)
      }
    } finally {
      await deleteDefinition(request, headers, created.id)
    }
  })

  test('CA-FW-20-02: API — run manual con definición inactiva no devuelve 4405', async ({ request }) => {
    const headers = await buildTaskAuthHeaders(request)
    const processId = await findBackupProcessId(request, headers)
    const created = await createInactiveDefinition(
      request,
      headers,
      processId,
      uniqueTaskTitle('e2e-cc20-api-run'),
    )

    try {
      const result = await postDefinitionRun(request, headers, created.id)
      expect(
        result.error,
        'run manual no debe rechazarse por definición inactiva (4405 = proceso catálogo inactivo)',
      ).not.toBe(PROCESS_INACTIVE_ERROR)
    } finally {
      await deleteDefinition(request, headers, created.id)
    }
  })

  test('API — proceso catálogo inactivo → run rechaza con 4405', async ({ request }) => {
    const headers = await buildTaskAuthHeaders(request)
    const processId = await findBackupProcessId(request, headers)
    const created = await createInactiveDefinition(
      request,
      headers,
      processId,
      uniqueTaskTitle('e2e-cc20-proc-off'),
    )

    let processWasActive = true
    try {
      const listRes = await request.get('/api/v1/task-processes', { headers })
      const items = ((await listRes.json())?.resultado?.items ?? []) as { id: number; isActive: boolean }[]
      const proc = items.find((p) => p.id === processId)
      processWasActive = proc?.isActive !== false

      await patchProcessActive(request, headers, processId, false)
      const result = await postDefinitionRun(request, headers, created.id)
      expect(result.error).toBe(PROCESS_INACTIVE_ERROR)
    } finally {
      await patchProcessActive(request, headers, processId, true)
      if (!processWasActive) {
        await patchProcessActive(request, headers, processId, false)
      }
      await deleteDefinition(request, headers, created.id)
    }
  })

  test('CA-FW-20-06 scheduler: cubierto por PHPUnit (no E2E)', () => {
    test.skip(
      true,
      'Scheduler automático + OMITIDA con definición inactiva: TaskExecutionServiceTest (Framework).',
    )
  })
})