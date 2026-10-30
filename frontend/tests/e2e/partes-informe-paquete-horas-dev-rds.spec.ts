import { expect, test } from '@playwright/test'
import { selectDevExtremeSelectByText } from './helpers/partesPaqueteHorasE2e'

/**
 * Smoke contra backend real (sqlsrv / RDS dev): `VITE_API_PROXY_TARGET=http://127.0.0.1:8088`
 * y backend con `.env` DEV (sin APP_ENV=e2e).
 * No seedea datos; usa cliente 4D si existe.
 */
test.describe.configure({ mode: 'serial' })

const clientCode = process.env.PW_DEV_CLIENT_CODE ?? '4D'
const fechaDesde = process.env.PW_DEV_FECHA_DESDE ?? '2026-10-01'
const fechaHasta = process.env.PW_DEV_FECHA_HASTA ?? '2026-10-31'

test('RDS dev: supervisor 4D oct-2026 muestra saldo y filas en grilla', async ({ page, request }) => {
  test.setTimeout(120_000)

  const tenant = process.env.PW_TENANT ?? 'PAQ'
  const usuario = process.env.PW_LOGIN_USER ?? 'PQ'
  const password = process.env.PW_LOGIN_PASSWORD ?? 'Paqsystems26*'

  const login = await request.post('/api/v1/auth/login', {
    headers: { 'X-Paq-Cliente': tenant, 'Content-Type': 'application/json' },
    data: { usuario, password },
  })
  expect(login.ok(), await login.text()).toBeTruthy()
  const token = (await login.json()).resultado.token as string

  const clientes = await request.get('/api/v1/partes/catalogos/clientes', {
    headers: { 'X-Paq-Cliente': tenant, Authorization: `Bearer ${token}` },
  })
  const items = (await clientes.json()).resultado.items as Array<{ id: number; code: string; nombre: string }>
  const client = items.find((c) => c.code === clientCode)
  expect(client, `cliente ${clientCode} en RDS dev`).toBeTruthy()

  const informe = await request.get(
    `/api/v1/partes/informes/paquete-horas?fechaDesde=${fechaDesde}&fechaHasta=${fechaHasta}&clienteId=${client!.id}`,
    { headers: { 'X-Paq-Cliente': tenant, Authorization: `Bearer ${token}` } },
  )
  expect(informe.ok()).toBeTruthy()
  const body = await informe.json()
  const saldo = body.resultado.saldoInicial as number
  const rowCount = (body.resultado.items as unknown[]).length
  expect(rowCount).toBeGreaterThan(0)
  expect(saldo).not.toBe(0)

  await page.goto('/login')
  await page.getByTestId('loginUsuario').locator('input').fill(usuario)
  await page.getByTestId('loginPassword').locator('input').fill(password)
  await page.getByTestId('loginSubmit').click()
  await expect(page).toHaveURL(/\/partes\/?(?:\?|$)/, { timeout: 45_000 })
  await expect(page.getByTestId('partesDashboardRoot')).toBeVisible({ timeout: 45_000 })

  const paqueteLink = page.getByTestId('partesDashboardLinkPaquete')
  if (await paqueteLink.isVisible().catch(() => false)) {
    await paqueteLink.click()
  } else {
    await page.goto('/partes/informes/paquete-horas')
  }
  await expect(page.getByTestId('partesPaqueteHorasPage')).toBeVisible({ timeout: 30_000 })

  await page.getByTestId('partesPaqueteFechaDesde').getByRole('combobox').fill(fechaDesde)
  await page.getByTestId('partesPaqueteFechaHasta').getByRole('combobox').fill(fechaHasta)

  await selectDevExtremeSelectByText(page, 'partesPaqueteCliente', `${client!.code} — ${client!.nombre}`)

  await page.getByTestId('partesPaqueteBuscar').click()

  const saldoHoras = formatMinutos(saldo)
  await expect(page.getByTestId('partesPaqueteHorasPage')).toContainText(saldoHoras, { timeout: 20_000 })
  await expect(page.getByTestId('partesPaqueteHorasGrid')).not.toContainText(/^Sin datos$/i)
})

function formatMinutos(min: number): string {
  const sign = min < 0 ? '-' : ''
  const abs = Math.abs(min)
  const h = Math.floor(abs / 60)
  const m = abs % 60
  return `${sign}${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}`
}
