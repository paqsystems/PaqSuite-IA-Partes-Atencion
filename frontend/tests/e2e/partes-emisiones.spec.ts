import { expect, test, type Locator, type Page } from '@playwright/test'

const e2eUser = process.env.PW_LOGIN_USER ?? 'admin'
const e2ePassword = process.env.PW_LOGIN_PASSWORD ?? 'Paqsystems'

/** Un retry si php -S / sqlite rechazan el login bajo carga; los casos siguen independientes. */
test.describe.configure({ retries: 1 })

async function loginPartes(page: Page) {
  let lastError: unknown
  for (let attempt = 1; attempt <= 3; attempt++) {
    await page.goto('/login?cliente=DEMO')
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
  const option = page.getByRole('option', { name: /consulta detallada/i })

  await expect(confirm).toBeVisible({ timeout: 20_000 })
  await expect(process).toBeVisible({ timeout: 20_000 })

  await expect
    .poll(
      async () => {
        const transport = await recoverTransientTransport(page)
        if (transport) {
          return `retry:${transport}`
        }

        if (!(await isDxDisabled(confirm))) {
          return 'ready'
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

test('consulta detallada muestra emitir y abre el dialogo', async ({ page }) => {
  test.setTimeout(90_000)
  await loginPartes(page)

  await page.goto('/partes/informes/consulta-detallada')
  await expect(page.getByTestId('partesConsultaDetalladaPage')).toBeVisible({ timeout: 20_000 })
  const emit = page.getByTestId('partesConsultaDetalladaEmit')
  await expect(emit).toBeVisible({ timeout: 15_000 })
  if (await emit.isEnabled()) {
    await emit.click()
    await expect(page.getByTestId('emission.dialog')).toBeVisible({ timeout: 10_000 })
  }
})

test('disenador llega al diseño del reporte sin errores', async ({ page }) => {
  test.setTimeout(120_000)
  const pageErrors: string[] = []
  page.on('pageerror', (error) => {
    pageErrors.push(error.message)
  })

  await loginPartes(page)

  await page.goto('/emisiones/disenador?cliente=DEMO')
  await expect(page).toHaveURL(/\/emisiones\/disenador/, { timeout: 20_000 })
  await expect(page.getByTestId('partesEmisionesDisenadorPage')).toBeVisible({ timeout: 20_000 })
  await expect(page.getByTestId('emission.design.process')).toBeVisible({ timeout: 20_000 })
  await expect(page.getByTestId('emission.design.confirmProcess')).toBeVisible()
  await expect(page.getByTestId('emission.design.host')).toHaveCount(0)

  await waitForDesignerProcessReady(page)

  const designerModel = page.waitForResponse(
    (response) => /GetDesignerModel/i.test(response.url()) && response.ok(),
    { timeout: 60_000 },
  )
  await confirmDesignerProcess(page)

  await expect(page.getByTestId('emission.design.host')).toBeVisible({ timeout: 30_000 })
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

  const blocking = pageErrors.filter(
    (message) =>
      !/License|trial|evaluation|W0019|W0021/i.test(message) &&
      !/ResizeObserver/i.test(message),
  )
  expect(blocking, `errores de página: ${blocking.join(' | ')}`).toEqual([])
})
