import { expect, test } from '@playwright/test'
import {
  ensurePartesBackendHealth,
  formatMinutosAsHhMmSigned,
  openPaqueteHorasInforme,
  seedPaqueteHorasFixture,
  selectDevExtremeSelectByText,
  waitForGridWithDataRows,
  type PaqueteHorasE2eFixture,
} from './helpers/partesPaqueteHorasE2e'

test.describe.configure({ mode: 'serial', retries: 1 })

let fixture: PaqueteHorasE2eFixture

test.beforeAll(async ({ request }) => {
  await ensurePartesBackendHealth(request)
  fixture = await seedPaqueteHorasFixture(request)
})

test('API paquete-horas coherente con fixture sembrada', async ({ request }) => {
  test.setTimeout(60_000)
  const tokenRes = await request.post('/api/v1/auth/login', {
    headers: { 'X-Paq-Cliente': 'DEMO', 'Content-Type': 'application/json' },
    data: { usuario: 'admin', password: 'Paqsystems' },
  })
  const token = (await tokenRes.json()).resultado.token as string
  const res = await request.get(
    `/api/v1/partes/informes/paquete-horas?fechaDesde=${fixture.fechaDesde}&fechaHasta=${fixture.fechaHasta}&clienteId=${fixture.clientId}`,
    { headers: { 'X-Paq-Cliente': 'DEMO', Authorization: `Bearer ${token}` } },
  )
  expect(res.ok()).toBeTruthy()
  const body = await res.json()
  expect(body.resultado.saldoInicial).toBe(fixture.expectedSaldoInicialMin)
  const movs = (body.resultado.items as Array<{ esSaldoInicial?: boolean }>).filter((r) => !r.esSaldoInicial)
  expect(movs.length).toBe(fixture.expectedMovementCount)
})

test('sin cliente: aviso y sin filas de datos', async ({ page }) => {
  test.setTimeout(90_000)
  await openPaqueteHorasInforme(page)
  await expect(page.getByTestId('partesPaqueteClienteHint')).toBeVisible()
  await expect(page.getByTestId('partesPaqueteHorasGrid')).toContainText(/Sin datos|No data/i)
})

test('supervisor: selección cliente + Buscar carga grilla y saldo inicial', async ({ page }) => {
  test.setTimeout(120_000)
  await openPaqueteHorasInforme(page)

  const paqueteRequest = page.waitForRequest(
    (req) =>
      req.method() === 'GET' &&
      req.url().includes('/api/v1/partes/informes/paquete-horas') &&
      req.url().includes(`clienteId=${fixture.clientId}`),
    { timeout: 45_000 },
  )

  await selectDevExtremeSelectByText(page, 'partesPaqueteCliente', fixture.clientDisplay)
  await page.getByTestId('partesPaqueteBuscar').click()

  const req = await paqueteRequest
  expect(req.url()).toContain(`clienteId=${fixture.clientId}`)

  const saldoEsperado = formatMinutosAsHhMmSigned(fixture.expectedSaldoInicialMin)
  await expect(page.getByTestId('partesPaqueteHorasPage')).toContainText(saldoEsperado, { timeout: 15_000 })

  await expect(page.getByTestId('partesPaqueteHorasGrid')).toBeVisible()
  await waitForGridWithDataRows(page, 1)
  await expect(page.getByTestId('partesPaqueteHorasGrid')).toContainText('E2E tarea hoy')
  await expect(page.getByTestId('partesPaqueteHorasGrid')).not.toContainText(/^Sin datos$/i)
})

test('recarga al cambiar cliente dispara nueva consulta (regresión SelectBox)', async ({ page }) => {
  test.setTimeout(120_000)
  await openPaqueteHorasInforme(page)
  await selectDevExtremeSelectByText(page, 'partesPaqueteCliente', fixture.clientDisplay)

  await expect
    .poll(
      async () => {
        const res = await page.waitForResponse(
          (r) => r.url().includes('paquete-horas') && r.status() === 200,
          { timeout: 5_000 },
        ).catch(() => null)
        return res !== null
      },
      { timeout: 45_000 },
    )
    .toBe(true)

  await waitForGridWithDataRows(page, 1)

  const refreshReq = page.waitForRequest(
    (r) => r.url().includes('paquete-horas') && r.url().includes(`clienteId=${fixture.clientId}`),
    { timeout: 30_000 },
  )
  const refreshBtn = page.locator('[data-testid="partesPaqueteHorasGrid"] [data-testid="gridRefresh"]')
  if (await refreshBtn.isVisible().catch(() => false)) {
    await refreshBtn.click()
    await refreshReq
  } else {
    await page.getByTestId('partesPaqueteBuscar').click()
    await refreshReq
  }
  await waitForGridWithDataRows(page, 1)
})

test('vista grilla activa: toggle pivot y vuelta mantiene datos visibles', async ({ page }) => {
  test.setTimeout(120_000)
  await openPaqueteHorasInforme(page)
  await selectDevExtremeSelectByText(page, 'partesPaqueteCliente', fixture.clientDisplay)
  await page.getByTestId('partesPaqueteBuscar').click()
  await waitForGridWithDataRows(page, 1)

  await expect(page.getByTestId('pivot.shell.grid')).toBeVisible()

  const pivotToggle = page.locator('[data-testid="pivot.toggle"]').getByText(/Pivot/i)
  if (await pivotToggle.isVisible().catch(() => false)) {
    await pivotToggle.click()
    await expect(page.getByTestId('pivot.shell.pivot')).toBeVisible({ timeout: 15_000 })
    const gridToggle = page.locator('[data-testid="pivot.toggle"]').getByText(/Grilla|Grid/i)
    await gridToggle.click()
    await expect(page.getByTestId('pivot.shell.grid')).toBeVisible({ timeout: 15_000 })
  }

  await waitForGridWithDataRows(page, 1)
  await expect(page.getByTestId('partesPaqueteHorasGrid')).toContainText('E2E tarea hoy')
})
