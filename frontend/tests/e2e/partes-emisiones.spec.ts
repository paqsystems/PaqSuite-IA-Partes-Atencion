import { expect, test } from '@playwright/test'

/**
 * TR-011: Consulta detallada muestra Emitir (GEN-15) y abre el diálogo.
 * Requiere backend seed (admin / Paqsystems) y EmissionEnabled=S.
 */
test('consulta detallada muestra emitir y abre el dialogo', async ({ page }) => {
  test.setTimeout(60_000)
  await page.goto('/login')
  await page.getByTestId('loginUsuario').locator('input').fill('admin')
  await page.getByTestId('loginPassword').locator('input').fill('Paqsystems')
  await page.getByTestId('loginSubmit').click()

  await expect(page).toHaveURL(/\/partes\/?$/, { timeout: 45_000 })

  await page.goto('/partes/informes/consulta-detallada')
  await expect(page.getByTestId('partesConsultaDetalladaPage')).toBeVisible({ timeout: 20_000 })
  const emit = page.getByTestId('partesConsultaDetalladaEmit')
  await expect(emit).toBeVisible({ timeout: 15_000 })
  if (await emit.isEnabled()) {
    await emit.click()
    await expect(page.getByTestId('emissions.dialog')).toBeVisible({ timeout: 10_000 })
  }
})

test('disenador lista proceso N=1 y monta DX solo tras confirmar', async ({ page }) => {
  test.setTimeout(90_000)
  await page.goto('/login')
  await page.getByTestId('loginUsuario').locator('input').fill('admin')
  await page.getByTestId('loginPassword').locator('input').fill('Paqsystems')
  await page.getByTestId('loginSubmit').click()
  await expect(page).toHaveURL(/\/partes\/?$/, { timeout: 45_000 })

  await page.goto('/emisiones/disenador')
  await expect(page.getByTestId('partesEmisionesDisenadorPage')).toBeVisible({ timeout: 20_000 })
  await expect(page.getByTestId('emission.design.process')).toBeVisible({ timeout: 20_000 })
  await expect(page.getByTestId('emission.design.confirmProcess')).toBeVisible()
  await expect(page.getByTestId('emission.design.host')).toHaveCount(0)

  await page.getByTestId('emission.design.confirmProcess').click()
  await expect(page.getByTestId('emission.design.host')).toBeVisible({ timeout: 20_000 })
})
