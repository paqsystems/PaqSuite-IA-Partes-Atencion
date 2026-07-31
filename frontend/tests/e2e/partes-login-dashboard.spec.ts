import { expect, test } from '@playwright/test'

/**
 * Requiere backend en VITE_API_PROXY_TARGET (sqlite seed local o DEMO migrado).
 * Credenciales seed canónico: admin / Paqsystems.
 */
test('login admin llega al dashboard Partes', async ({ page }) => {
  test.setTimeout(60_000)
  await page.goto('/login')
  await page.getByTestId('loginUsuario').locator('input').fill('admin')
  await page.getByTestId('loginPassword').locator('input').fill('Paqsystems')
  await page.getByTestId('loginSubmit').click()

  await expect(page).toHaveURL(/\/partes\/?$/, { timeout: 45_000 })
  await expect(page.getByTestId('partesDashboardRoot')).toBeVisible({ timeout: 45_000 })
  await expect(page.getByTestId('partesDashboardTotalMinutos')).toBeVisible()
})
