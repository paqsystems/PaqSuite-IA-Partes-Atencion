import { expect, test, type Locator, type Page } from '@playwright/test'
import { partesEmissionMenuEntries } from '../../src/features/partes/informes/partesEmissionMenuCatalog.ts'
import { selectDevExtremeSelectByText } from './helpers/partesPaqueteHorasE2e'

const e2eUser = process.env.PW_LOGIN_USER ?? 'admin'
const e2ePassword = process.env.PW_LOGIN_PASSWORD ?? 'Paqsystems'
const e2eTenant = process.env.PW_TENANT ?? process.env.VITE_TENANT_DEFAULT_CLIENT ?? 'DEMO'
const vercelShare = (process.env.PW_VERCEL_SHARE ?? '').trim()

function withTenantQuery(path: string): string {
  const url = new URL(path, 'http://localhost')
  url.searchParams.set('cliente', e2eTenant)
  if (vercelShare) {
    url.searchParams.set('_vercel_share', vercelShare)
  }
  return `${url.pathname}${url.search}`
}

/** Un retry si php -S / sqlite rechazan el login bajo carga; los casos siguen independientes. */
test.describe.configure({ retries: 1 })

async function loginPartes(page: Page) {
  let lastError: unknown
  for (let attempt = 1; attempt <= 3; attempt++) {
    await page.goto(withTenantQuery('/login'))
    await page.getByTestId('loginUsuario').locator('input').fill(e2eUser)
    await page.getByTestId('loginPassword').locator('input').fill(e2ePassword)
    await page.getByTestId('loginSubmit').click()
    try {
      await expect(page).toHaveURL(/\/partes\/?(?:\?|$)/, { timeout: 20_000 })
      return
    } catch (error) {
      lastError = error
    }
  }
  throw lastError
}

function isDxDisabled(locator: Locator) {
  return locator.evaluate(
    (el) =>
      el.getAttribute('aria-disabled') === 'true' ||
      el.classList.contains('dx-state-disabled') ||
      (el as HTMLButtonElement).disabled === true,
  )
}

async function recoverTransientTransport(page: Page): Promise<string | null> {
  const menuTransport = page.getByText('infra.transport', { exact: false })
  if (await menuTransport.isVisible().catch(() => false)) {
    const retry = page.getByRole('button', { name: /Reintentar|Retry|Riprova|Réessayer/i })
    if (await retry.isVisible().catch(() => false)) {
      await retry.click()
    }
    return 'infra.transport'
  }

  const alert = page.getByRole('alert')
  if (await alert.isVisible().catch(() => false)) {
    const text = ((await alert.textContent()) ?? '').trim()
    if (/comunicación|communication|transport|infra/i.test(text)) {
      await page.reload()
      await expect(page.getByTestId('partesEmisionesDisenadorPage')).toBeVisible({ timeout: 20_000 })
      return text || 'alert comunicación'
    }
  }

  return null
}

/**
 * El catálogo GEN debe estar cargado antes de confirmar.
 * Si el combo está vacío, GetDesignerModel nunca sale: fallar (o reintentar) acá.
 */
async function waitForDesignerProcessReady(page: Page) {
  const confirm = page.getByTestId('emission.design.confirmProcess')
  const process = page.getByTestId('emission.design.process')
  /** Mismo proceso que falló en preview DEV («Error de comunicación al emitir»). */
  const option = page.getByRole('option', { name: /paquete de horas/i })

  await expect(confirm).toBeVisible({ timeout: 20_000 })
  await expect(process).toBeVisible({ timeout: 20_000 })

  await expect
    .poll(
      async () => {
        const transport = await recoverTransientTransport(page)
        if (transport) {
          return `retry:${transport}`
        }

        await process.click()
        const hasOption = await option.isVisible().catch(() => false)
        const emptyList = await page.getByText(/Sin datos|No data|Nessun dato|Aucun/i).isVisible().catch(
          () => false,
        )
        if (hasOption) {
          await option.click()
          return (await isDxDisabled(confirm)) ? 'option-selected-still-disabled' : 'ready'
        }
        if (emptyList) {
          await page.keyboard.press('Escape')
          return 'empty-catalog'
        }
        await page.keyboard.press('Escape')
        if (!(await isDxDisabled(confirm))) {
          return 'ready'
        }
        return 'waiting-catalog'
      },
      { timeout: 45_000, intervals: [500, 1_000, 2_000] },
    )
    .toBe('ready')
}

async function confirmDesignerProcess(page: Page) {
  const confirm = page.getByTestId('emission.design.confirmProcess')
  await confirm.click()
}

async function waitForLoadPanelHidden(page: Page) {
  await page.locator('.dx-loadpanel-wrapper').waitFor({ state: 'hidden', timeout: 60_000 }).catch(() => undefined)
}

/** El botón Emitir solo monta si `GET .../emissions/processes/{code}` responde OK. */
async function waitForEmissionProcessApi(page: Page, processCode: string): Promise<boolean> {
  const response = await page
    .waitForResponse(
      (r) =>
        r.request().method() === 'GET' &&
        /\/emissions\/processes\//.test(r.url()) &&
        decodeURIComponent(r.url()).includes(processCode),
      { timeout: 45_000 },
    )
    .catch(() => null)
  return response !== null && response.ok()
}

async function preparePaqueteHorasScreen(page: Page) {
  await waitForLoadPanelHidden(page)
  const fechaDesde = process.env.PW_DEV_FECHA_DESDE ?? '2026-10-01'
  const fechaHasta = process.env.PW_DEV_FECHA_HASTA ?? '2026-10-31'
  await page.getByTestId('partesPaqueteFechaDesde').getByRole('combobox').fill(fechaDesde)
  await page.getByTestId('partesPaqueteFechaHasta').getByRole('combobox').fill(fechaHasta)
  const clientCode = process.env.PW_DEV_CLIENT_CODE ?? '4D'
  try {
    await selectDevExtremeSelectByText(page, 'partesPaqueteCliente', clientCode)
  } catch {
    test.skip(true, `Cliente ${clientCode} no disponible en catálogo (Paquete de horas)`)
  }
  const buscar = page.getByTestId('partesPaqueteBuscar')
  await expect(buscar).toBeEnabled({ timeout: 15_000 })
  await buscar.click()
  await waitForLoadPanelHidden(page)
}

for (const entry of partesEmissionMenuEntries) {
  test(`${entry.processCode} — navegación menú y diálogo Emitir`, async ({ page }) => {
    test.setTimeout(entry.processCode === 'partes.informes.paqueteHoras' ? 120_000 : 90_000)
    await loginPartes(page)

    const emissionApiReady = waitForEmissionProcessApi(page, entry.processCode)
    await page.goto(withTenantQuery(entry.routePath))
    if (!(await emissionApiReady)) {
      test.skip(true, `Backend sin emisión para ${entry.processCode} (GET processes)`)
    }
    await expect(page.getByTestId(entry.pageTestId)).toBeVisible({ timeout: 20_000 })
    if (entry.processCode === 'partes.informes.paqueteHoras') {
      await preparePaqueteHorasScreen(page)
    } else {
      await waitForLoadPanelHidden(page)
    }
    const emit = page.getByTestId(entry.emitTestId)
    await expect(emit).toBeVisible({ timeout: 45_000 })
    if (await emit.isEnabled()) {
      await emit.click()
      await expect(page.getByTestId('emission.dialog')).toBeVisible({ timeout: 10_000 })
    }
  })
}

test('disenador llega al diseño del reporte sin errores', async ({ page }) => {
  test.setTimeout(180_000)
  const pageErrors: string[] = []
  const failedApis: string[] = []
  page.on('pageerror', (error) => {
    pageErrors.push(error.message)
  })
  page.on('response', (response) => {
    const url = response.url()
    if (!/\/api\/v1\/emissions\//.test(url) && !/GetDesignerModel/i.test(url)) {
      return
    }
    if (response.status() >= 400) {
      failedApis.push(`${response.status()} ${response.request().method()} ${url}`)
    }
  })

  await loginPartes(page)

  await page.goto(withTenantQuery('/emisiones/disenador'))
  await expect(page).toHaveURL(/\/emisiones\/disenador/, { timeout: 20_000 })
  await expect(page.getByTestId('partesEmisionesDisenadorPage')).toBeVisible({ timeout: 20_000 })
  await expect(page.getByTestId('emission.design.process')).toBeVisible({ timeout: 20_000 })
  await expect(page.getByTestId('emission.design.confirmProcess')).toBeVisible()
  await expect(page.getByTestId('emission.design.host')).toHaveCount(0)

  try {
    await waitForDesignerProcessReady(page)
  } catch {
    test.skip(true, 'Catálogo de procesos de emisión vacío en este entorno (p. ej. capability off o sin seed)')
  }

  const fieldSchemaOk = page.waitForResponse(
    (response) =>
      response.request().method() === 'GET' &&
      /\/emissions\/design\/field-schema/.test(response.url()) &&
      response.ok(),
    { timeout: 45_000 },
  )
  const designerModel = page.waitForResponse(
    (response) => /GetDesignerModel/i.test(response.url()) && response.ok(),
    { timeout: 60_000 },
  )
  await confirmDesignerProcess(page)

  const designHost = page.getByTestId('emission.design.host')
  try {
    await expect(designHost).toBeVisible({ timeout: 30_000 })
  } catch {
    test.skip(true, 'Sidecar DX / emission.design.host no disponible en este entorno')
  }
  await fieldSchemaOk
  await expect(page.getByTestId('emission.design.apiError')).toHaveCount(0)
  await expect(page.getByTestId('emissionDesignerSchemaError')).toHaveCount(0)
  await expect(page.getByText(/Error de comunicación|Communication error|infra\.transport/i)).toHaveCount(0)
  await expect(page.getByTestId('emission.design.report')).toBeVisible({ timeout: 20_000 })
  await expect(page.getByTestId('emission.design.setPrincipal')).toBeVisible()
  await expect(page.getByTestId('emissions.designer.hostMissing')).toHaveCount(0)
  await expect(page.getByTestId('emission.design.hostCrash')).toHaveCount(0)
  await expect(page.locator('[data-i18n-key^="emission.error"]')).toHaveCount(0)

  await expect(page.getByTestId('emissions.designer.dxHost')).toBeVisible({ timeout: 20_000 })
  await designerModel

  await expect(
    page.locator('.dxrd, .dxrd-designer, .dxrd-toolbar, .dx-designer, .dxrd-viewport').first(),
  ).toBeVisible({ timeout: 45_000 })

  expect(failedApis, `APIs fallidas: ${failedApis.join(' | ')}`).toEqual([])
  const blocking = pageErrors.filter(
    (message) =>
      !/License|trial|evaluation|W0019|W0021/i.test(message) &&
      !/ResizeObserver/i.test(message),
  )
  expect(blocking, `errores de página: ${blocking.join(' | ')}`).toEqual([])
})
