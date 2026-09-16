import { expect, test } from '@playwright/test'

async function loginAsAdmin(page: import('@playwright/test').Page): Promise<void> {
  await page.goto('/login')
  await page.getByTestId('loginUsuario').locator('input').fill('admin')
  await page.getByTestId('loginPassword').locator('input').fill('Paqsystems')
  await page.getByTestId('loginSubmit').click()
  await expect(page).toHaveURL(/\/partes\/?$/, { timeout: 45_000 })
}

test('alta de carga diaria precarga tipo de tarea default', async ({ page }) => {
  test.setTimeout(60_000)
  const defaultId = 601
  await page.route('**/api/v1/partes/catalogos/tipos-tarea**', async (route) => {
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
        resultado: {
          items: [
            { id: 500, code: 'AAA', descripcion: 'Primero', isDefault: false, isGenerico: true },
            {
              id: defaultId,
              code: 'GEN',
              descripcion: 'General',
              isDefault: true,
              isGenerico: true,
            },
          ],
        },
      }),
    })
  })
  await loginAsAdmin(page)
  await page.goto('/partes/carga-diaria')
  await expect(page.getByTestId('partesCargaAdd')).toBeVisible({ timeout: 30_000 })
  await page.getByTestId('partesCargaAdd').click()
  await expect(page.getByTestId('partesCargaForm')).toBeVisible({ timeout: 10_000 })
  await expect(page.getByTestId('partesCargaTipoTarea').locator('input[type="hidden"]')).toHaveValue(
    String(defaultId),
    { timeout: 10_000 }
  )
})

test('Guardar alta incompleta muestra el error dentro del formulario', async ({ page }) => {
  test.setTimeout(60_000)
  await loginAsAdmin(page)
  await page.goto('/partes/carga-diaria')
  await expect(page.getByTestId('partesCargaAdd')).toBeVisible({ timeout: 30_000 })
  await page.getByTestId('partesCargaAdd').click()
  await expect(page.getByTestId('partesCargaForm')).toBeVisible({ timeout: 10_000 })
  await page.getByTestId('partesCargaSave').click()
  await expect(page.getByTestId('partesCargaError')).toBeVisible({ timeout: 5_000 })
  await expect(page.getByTestId('partesCargaForm')).toBeVisible()
})
