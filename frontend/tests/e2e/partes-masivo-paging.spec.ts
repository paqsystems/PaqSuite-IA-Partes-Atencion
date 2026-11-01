import { expect, test } from '@playwright/test'
import { loginAsSupervisor, mockMasivoSupervisorApis } from './helpers/partesMasivoE2e'

function tareaItem(id: number): Record<string, unknown> {
  return {
    id,
    usuarioId: 1,
    clienteId: 1,
    tipoTareaId: 1,
    fecha: '2026-10-31',
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

test('proceso masivo pagina el listado cuando total supera pageSize', async ({ page }) => {
  test.setTimeout(90_000)
  const total = 25

  await mockMasivoSupervisorApis(page, (url) => {
    const pageNum = Number(url.searchParams.get('page') ?? '1')
    const take = Number(url.searchParams.get('pageSize') ?? '20')
    const startId = (pageNum - 1) * take + 1
    const count = Math.min(take, Math.max(0, total - (pageNum - 1) * take))
    const items = Array.from({ length: count }, (_, index) => tareaItem(startId + index))
    return { items, total }
  })

  await loginAsSupervisor(page)
  await page.goto('/partes/proceso-masivo')
  await expect(page.getByTestId('partesMasivoPage')).toBeVisible({ timeout: 20_000 })
  await expect(page.getByTestId('partesMasivoGrid')).toBeVisible()

  const grid = page.getByTestId('partesMasivoGrid')
  const dataRows = grid.locator('.dx-data-row')
  await expect(dataRows.first()).toBeVisible({ timeout: 15_000 })
  await expect(dataRows).toHaveCount(20)
  await expect(grid.getByText('obs-1', { exact: true })).toBeVisible()
  await expect(grid.getByText('obs-20', { exact: true })).toBeVisible()
  await expect(grid.getByText('obs-21', { exact: true })).not.toBeVisible()

  const nextPage = grid.getByRole('button', { name: 'Página siguiente' })
  await expect(nextPage).toBeEnabled({ timeout: 10_000 })
  const page2Request = page.waitForRequest(
    (req) =>
      req.url().includes('/partes/tareas') &&
      new URL(req.url()).searchParams.get('page') === '2',
  )
  await nextPage.click()
  await page2Request

  await expect(grid.getByText('obs-21', { exact: true })).toBeVisible({ timeout: 15_000 })
  await expect(grid.getByText('obs-25', { exact: true })).toBeVisible()
  await expect(grid.getByText('obs-1', { exact: true })).not.toBeVisible()
})
