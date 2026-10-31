import { expect, type APIRequestContext, type Page } from '@playwright/test'

const tenantHeader = { 'X-Paq-Cliente': 'DEMO' }

export type PaqueteHorasE2eFixture = {
  clientCode: string
  clientDisplay: string
  clientId: number
  fechaDesde: string
  fechaHasta: string
  expectedSaldoInicialMin: number
  expectedMovementCount: number
}

function isoDate(d: Date): string {
  const y = d.getFullYear()
  const m = String(d.getMonth() + 1).padStart(2, '0')
  const day = String(d.getDate()).padStart(2, '0')
  return `${y}-${m}-${day}`
}

export async function ensurePartesBackendHealth(request: APIRequestContext): Promise<void> {
  const health = await request.get('/api/v1/health', { headers: tenantHeader })
  expect(health.ok(), `health ${health.status()} — ¿backend en :8010?`).toBeTruthy()
}

export async function loginPartesApi(request: APIRequestContext): Promise<string> {
  const login = await request.post('/api/v1/auth/login', {
    headers: { ...tenantHeader, 'Content-Type': 'application/json' },
    data: { usuario: process.env.PW_LOGIN_USER ?? 'admin', password: process.env.PW_LOGIN_PASSWORD ?? 'Paqsystems' },
  })
  expect(login.ok(), `login ${login.status()}`).toBeTruthy()
  const body = await login.json()
  const token = body?.resultado?.token as string | undefined
  expect(token, 'token Sanctum').toBeTruthy()
  return token!
}

export async function seedPaqueteHorasFixture(request: APIRequestContext): Promise<PaqueteHorasE2eFixture> {
  const token = await loginPartesApi(request)
  const auth = {
    ...tenantHeader,
    Authorization: `Bearer ${token}`,
    'Content-Type': 'application/json',
  }

  const suffix = Date.now().toString(36).toUpperCase()
  const clientCode = `PQ${suffix}`
  const clientDisplay = `${clientCode} — Cliente E2E Paquete`

  const tipoClienteRes = await request.post('/api/v1/partes/tipos-cliente', {
    headers: auth,
    data: { code: `TC${suffix}`, descripcion: 'Tipo E2E Paquete', activo: true },
  })
  expect(tipoClienteRes.ok(), `tipo cliente ${tipoClienteRes.status()}`).toBeTruthy()
  const tipoClienteId = (await tipoClienteRes.json()).resultado.item.id as number

  const clienteRes = await request.post('/api/v1/partes/clientes', {
    headers: auth,
    data: {
      code: clientCode,
      nombre: 'Cliente E2E Paquete',
      tipoClienteId,
      activo: true,
      inhabilitado: false,
    },
  })
  expect(clienteRes.ok(), `cliente ${clienteRes.status()}`).toBeTruthy()
  const clientId = (await clienteRes.json()).resultado.item.id as number

  const hoy = new Date()
  const ayer = new Date(hoy)
  ayer.setDate(hoy.getDate() - 1)
  const fechaHasta = isoDate(hoy)
  /** Rango solo «hoy»: la compra de ayer debe impactar saldo inicial, no el periodo. */
  const fechaDesde = fechaHasta
  const ayerIso = isoDate(ayer)

  const catalogTipos = await request.get('/api/v1/partes/catalogos/tipos-tarea', { headers: auth })
  expect(catalogTipos.ok()).toBeTruthy()
  const tipoTareaId = ((await catalogTipos.json()).resultado.items as Array<{ id: number; code: string }>).find(
    (t) => t.code === 'GEN',
  )?.id
  expect(tipoTareaId).toBeTruthy()

  const asistentes = await request.get('/api/v1/partes/catalogos/asistentes', { headers: auth })
  expect(asistentes.ok()).toBeTruthy()
  const asistenteId = ((await asistentes.json()).resultado.items as Array<{ id: number; code: string }>).find(
    (a) => a.code === 'admin',
  )?.id
  expect(asistenteId).toBeTruthy()

  const paqueteAyer = await request.post('/api/v1/partes/paquetes', {
    headers: auth,
    data: {
      clienteId: clientId,
      fecha: ayerIso,
      duracionMinutos: 60,
      observacion: 'E2E compra ayer',
    },
  })
  expect(paqueteAyer.ok(), `paquete ayer ${paqueteAyer.status()}`).toBeTruthy()

  const tareaHoy = await request.post('/api/v1/partes/tareas', {
    headers: auth,
    data: {
      usuarioId: asistenteId,
      clienteId: clientId,
      tipoTareaId: tipoTareaId,
      fecha: fechaHasta,
      duracionMinutos: 30,
      observacion: 'E2E tarea hoy',
    },
  })
  expect(tareaHoy.ok(), `tarea hoy ${tareaHoy.status()}`).toBeTruthy()

  const informe = await request.get(
    `/api/v1/partes/informes/paquete-horas?fechaDesde=${fechaDesde}&fechaHasta=${fechaHasta}&clienteId=${clientId}`,
    { headers: auth },
  )
  expect(informe.ok()).toBeTruthy()
  const informeBody = await informe.json()
  expect(informeBody.error).toBe(0)
  const items = (informeBody.resultado.items ?? []) as Array<{ esSaldoInicial?: boolean }>
  const movimientos = items.filter((row) => !row.esSaldoInicial)
  expect(movimientos.length).toBeGreaterThanOrEqual(1)
  expect(informeBody.resultado.saldoInicial).toBe(-60)

  return {
    clientCode,
    clientDisplay,
    clientId,
    fechaDesde,
    fechaHasta,
    expectedSaldoInicialMin: -60,
    expectedMovementCount: movimientos.length,
  }
}

export async function loginPartesUi(page: Page): Promise<void> {
  let lastError: unknown
  for (let attempt = 1; attempt <= 3; attempt++) {
    await page.goto('/login')
    await page.getByTestId('loginUsuario').locator('input').fill(process.env.PW_LOGIN_USER ?? 'admin')
    await page.getByTestId('loginPassword').locator('input').fill(process.env.PW_LOGIN_PASSWORD ?? 'Paqsystems')
    await page.getByTestId('loginSubmit').click()
    try {
      await expect(page).toHaveURL(/\/partes\/?(?:\?|$)/, { timeout: 45_000 })
      await expect(page.getByTestId('partesDashboardRoot')).toBeVisible({ timeout: 45_000 })
      return
    } catch (error) {
      lastError = error
    }
  }
  throw lastError
}

export async function openPaqueteHorasInforme(page: Page): Promise<void> {
  await loginPartesUi(page)
  const link = page.getByTestId('partesDashboardLinkPaquete')
  if (await link.isVisible().catch(() => false)) {
    await link.click()
  } else {
    await page.goto('/partes/informes/paquete-horas')
  }
  if (page.url().includes('/login')) {
    await loginPartesUi(page)
    await page.goto('/partes/informes/paquete-horas')
  }
  await expect(page.getByTestId('partesPaqueteHorasPage')).toBeVisible({ timeout: 30_000 })
}

/** Selección DevExtreme SelectBox (cliente / tipo cliente). */
export async function selectDevExtremeSelectByText(page: Page, testId: string, optionText: string): Promise<void> {
  const root = page.getByTestId(testId)
  await expect(root).toBeVisible({ timeout: 20_000 })
  await root.click()
  const input = root.locator('input.dx-texteditor-input')
  await input.fill(optionText.split(' — ')[0] ?? optionText)
  const item = page.locator('.dx-overlay-content .dx-list-item').filter({ hasText: optionText })
  await expect(item.first()).toBeVisible({ timeout: 10_000 })
  await item.first().click()
}

export function formatMinutosAsHhMmSigned(minutos: number): string {
  const sign = minutos < 0 ? '-' : ''
  const total = Math.abs(Math.round(minutos))
  const hours = Math.floor(total / 60)
  const mins = total % 60
  return `${sign}${String(hours).padStart(2, '0')}:${String(mins).padStart(2, '0')}`
}

export async function countVisibleGridDataRows(page: Page): Promise<number> {
  return page.locator('[data-testid="partesPaqueteHorasGrid"] .dx-datagrid-rowsview .dx-data-row').count()
}

export async function waitForGridWithDataRows(page: Page, minRows = 1): Promise<void> {
  await expect
    .poll(async () => countVisibleGridDataRows(page), {
      timeout: 30_000,
      message: 'filas visibles en grilla Paquete de horas',
    })
    .toBeGreaterThanOrEqual(minRows)
}
