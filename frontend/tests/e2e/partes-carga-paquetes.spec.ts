import { expect, test } from '@playwright/test'

async function loginAsAdmin(page: import('@playwright/test').Page): Promise<void> {
  await page.goto('/login')
  await page.getByTestId('loginUsuario').locator('input').fill('admin')
  await page.getByTestId('loginPassword').locator('input').fill('Paqsystems')
  await page.getByTestId('loginSubmit').click()
  await expect(page).toHaveURL(/\/partes\/?$/, { timeout: 45_000 })
}

test('supervisor abre carga de paquetes y el error de alta queda en el formulario', async ({
  page,
}) => {
  test.setTimeout(60_000)
  await loginAsAdmin(page)
  await page.goto('/partes/carga-paquetes')
  await expect(page.getByTestId('partesCargaPaquetesPage')).toBeVisible({ timeout: 30_000 })
  await expect(page.getByTestId('partesCargaPaquetesAdd')).toBeVisible()
  await page.getByTestId('partesCargaPaquetesAdd').click()
  await expect(page.getByTestId('partesCargaPaquetesForm')).toBeVisible({ timeout: 10_000 })
  await page.getByTestId('partesCargaPaquetesSave').click()
  await expect(page.getByTestId('partesCargaPaquetesFormError')).toBeVisible({ timeout: 5_000 })
  await expect(page.getByTestId('partesCargaPaquetesForm')).toBeVisible()
  await expect(page.getByTestId('partesCargaPaquetesSmartCapture')).toBeVisible()
})
