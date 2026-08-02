import { expect, test } from '@playwright/test'

/**
 * TR-009: Carga diaria muestra toolbar Excel GEN (plantilla + importar).
 * Requiere backend seed (admin / Paqsystems).
 */
test('carga diaria muestra toolbar importacion excel', async ({ page }) => {
  test.setTimeout(60_000)
  await page.goto('/login')
  await page.getByTestId('loginUsuario').locator('input').fill('admin')
  await page.getByTestId('loginPassword').locator('input').fill('Paqsystems')
  await page.getByTestId('loginSubmit').click()

  await expect(page).toHaveURL(/\/partes\/?$/, { timeout: 45_000 })

  await page.goto('/partes/carga-diaria')
  await expect(page.getByTestId('partesCargaPage')).toBeVisible({ timeout: 20_000 })
  await expect(page.getByTestId('excelImport.toolbar')).toBeVisible({ timeout: 15_000 })
  await expect(page.getByTestId('excelImport.template')).toBeVisible()
})
