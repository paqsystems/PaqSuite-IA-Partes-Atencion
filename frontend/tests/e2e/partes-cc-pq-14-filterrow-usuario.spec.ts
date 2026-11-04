import { expect, test } from '@playwright/test'
import { loginAsSupervisor } from './helpers/partesMasivoE2e'
import { fillFilterRowInput, mockMaestrosAdminApis } from './helpers/partesMaestrosFilterRowE2e'

test.describe('CC PQ #14 — FilterRow usuario en maestros', () => {
  test('asistentes — filtro columna usuario acota filas y envía query usuario', async ({ page }) => {
    test.setTimeout(90_000)
    await mockMaestrosAdminApis(page, {
      asistentes: [
        {
          id: 1,
          code: 'A1',
          usuarioCodigo: 'login-asist-ank',
          nombre: 'Asist Uno',
          supervisor: false,
          activo: true,
        },
        {
          id: 2,
          code: 'A2',
          usuarioCodigo: 'otro-login',
          nombre: 'Asist Dos',
          supervisor: false,
          activo: true,
        },
      ],
    })
    await loginAsSupervisor(page)
    await page.goto('/archivos/partes/asistentes')
    await expect(page.getByTestId('partesMaestrosAsistentesGrid')).toBeVisible({ timeout: 20_000 })

    const filterReq = page.waitForRequest(
      (req) =>
        req.method() === 'GET' &&
        req.url().includes('/partes/asistentes') &&
        new URL(req.url()).searchParams.get('usuario') === 'login-asist',
    )
    await fillFilterRowInput(page, 'partesMaestrosAsistentesGrid', 1, 'login-asist')
    await filterReq

    const grid = page.getByTestId('partesMaestrosAsistentesGrid')
    await expect(grid.getByText('login-asist-ank')).toBeVisible({ timeout: 15_000 })
    await expect(grid.getByText('otro-login')).not.toBeVisible()
  })

  test('clientes — filtro columna usuario acota filas y envía query usuario', async ({ page }) => {
    test.setTimeout(90_000)
    await mockMaestrosAdminApis(page, {
      clientes: [
        {
          id: 1,
          code: 'C1',
          nombre: 'Cliente Uno',
          tipoClienteCode: 'TC',
          usuarioCodigo: 'login-cli-ank',
          email: 'a@acme.test',
          activo: true,
        },
        {
          id: 2,
          code: 'C2',
          nombre: 'Cliente Dos',
          tipoClienteCode: 'TC',
          usuarioCodigo: 'login-cli-otro',
          email: 'b@example.test',
          activo: true,
        },
      ],
    })
    await loginAsSupervisor(page)
    await page.goto('/archivos/partes/clientes')
    await expect(page.getByTestId('partesMaestrosClientesGrid')).toBeVisible({ timeout: 20_000 })

    const filterReq = page.waitForRequest(
      (req) =>
        req.method() === 'GET' &&
        req.url().includes('/partes/clientes') &&
        new URL(req.url()).searchParams.get('usuario') === 'login-cli-ank',
    )
    await fillFilterRowInput(page, 'partesMaestrosClientesGrid', 3, 'login-cli-ank')
    await filterReq

    const grid = page.getByTestId('partesMaestrosClientesGrid')
    await expect(grid.getByText('login-cli-ank')).toBeVisible({ timeout: 15_000 })
    await expect(grid.getByText('login-cli-otro')).not.toBeVisible()
  })
})
