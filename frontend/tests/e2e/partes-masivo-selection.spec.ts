import { expect, test, type Page } from '@playwright/test'

async function loginAsAdmin(page: Page): Promise<void> {
  await page.goto('/login')
  await page.getByTestId('loginUsuario').locator('input').fill('admin')
  await page.getByTestId('loginPassword').locator('input').fill('Paqsystems')
  await page.getByTestId('loginSubmit').click()
  await expect(page).toHaveURL(/\/partes\/?$/, { timeout: 45_000 })
}

function tareaItem(id: number): Record<string, unknown> {
  return {
    id,
    usuarioId: 1,
    clienteId: 1,
    tipoTareaId: 1,
    fecha: '2026-09-15',
    duracionMinutos: 15,
    sinCargo: false,
    presencial: false,
    observacion: `obs-${id}`,
    cerrado: false,
    rowVersion: '1',
    usuarioCode: 'admin',
    clienteNombre: 'Cliente',
    tipoTareaDescripcion: 'General',
  }
}

test('tilde de fila en proceso masivo permanece', async ({ page }) => {
  test.setTimeout(60_000)
  await page.route('**/api/v1/partes/tareas**', async (route) => {
    if (route.request().method() !== 'GET') {
      await route.continue()
      return
    }
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: { items: [tareaItem(11), tareaItem(12)], total: 2 },
      }),
    })
  })
  await loginAsAdmin(page)
  await page.goto('/partes/proceso-masivo')
  await expect(page.getByTestId('partesMasivoPage')).toBeVisible({ timeout: 20_000 })
  await expect(page.getByTestId('partesMasivoGrid')).toBeVisible()
  const rowChecks = page.getByTestId('partesMasivoGrid').locator('.dx-checkbox-icon')
  await expect(rowChecks.nth(1)).toBeVisible({ timeout: 15_000 })
  await rowChecks.nth(1).click()
  await expect(page.getByTestId('partesMasivoSelectionCount')).toContainText('1', { timeout: 5_000 })
  await page.waitForTimeout(400)
  await expect(page.getByTestId('partesMasivoSelectionCount')).toContainText('1')
})
