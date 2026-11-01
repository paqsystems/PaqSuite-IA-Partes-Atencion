import { expect, test } from '@playwright/test'
import { loginAsSupervisor, mockMasivoSupervisorApis } from './helpers/partesMasivoE2e'

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
  await mockMasivoSupervisorApis(page, () => ({
    items: [tareaItem(11), tareaItem(12)],
    total: 2,
  }))
  await loginAsSupervisor(page)
  await page.goto('/partes/proceso-masivo')
  await expect(page.getByTestId('partesMasivoPage')).toBeVisible({ timeout: 20_000 })
  await expect(page.getByTestId('partesMasivoGrid')).toBeVisible()
  const grid = page.getByTestId('partesMasivoGrid')
  const dataRows = grid.locator('.dx-data-row')
  await expect(dataRows.first()).toBeVisible({ timeout: 15_000 })
  await grid.getByRole('row', { name: /obs-11/ }).getByLabel('Seleccionar fila').click()
  await expect(page.getByTestId('partesMasivoSelectionCount')).toContainText('1', { timeout: 5_000 })
  await page.waitForTimeout(400)
  await expect(page.getByTestId('partesMasivoSelectionCount')).toContainText('1')
})
