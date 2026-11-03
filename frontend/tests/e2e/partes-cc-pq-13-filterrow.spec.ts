import { expect, test } from '@playwright/test'
import { loginAsSupervisor, mockMasivoSupervisorApis } from './helpers/partesMasivoE2e'
import { fillFilterRowInput, mockMaestrosAdminApis } from './helpers/partesMaestrosFilterRowE2e'

test.describe('CC PQ #13 — FilterRow remoto', () => {
  test('asistentes — filtro nombre acota filas y query API', async ({ page }) => {
    test.setTimeout(90_000)
    await mockMaestrosAdminApis(page, {
      asistentes: [
        { id: 1, code: 'A1', nombre: 'ANKAS Uno', supervisor: false, activo: true },
        { id: 2, code: 'A2', nombre: 'Beta Dos', supervisor: false, activo: true },
      ],
    })
    await loginAsSupervisor(page)
    await page.goto('/archivos/partes/asistentes')
    await expect(page.getByTestId('partesMaestrosAsistentesPage')).toBeVisible({ timeout: 20_000 })
    await expect(page.getByTestId('partesMaestrosAsistentesGrid')).toBeVisible()

    await fillFilterRowInput(page, 'partesMaestrosAsistentesGrid', 1, 'ANKAS')
    const grid = page.getByTestId('partesMaestrosAsistentesGrid')
    await expect(grid.getByText('ANKAS Uno')).toBeVisible({ timeout: 15_000 })
    await expect(grid.getByText('Beta Dos')).not.toBeVisible()
  })

  test('clientes — filtro nombre acota filas y query API', async ({ page }) => {
    test.setTimeout(90_000)
    await mockMaestrosAdminApis(page, {
      clientes: [
        { id: 1, code: 'C1', nombre: 'ANKAS Cliente', activo: true },
        { id: 2, code: 'C2', nombre: 'Otro Cliente', activo: true },
      ],
    })
    await loginAsSupervisor(page)
    await page.goto('/archivos/partes/clientes')
    await expect(page.getByTestId('partesMaestrosClientesGrid')).toBeVisible({ timeout: 20_000 })

    await fillFilterRowInput(page, 'partesMaestrosClientesGrid', 1, 'ANKAS')
    const grid = page.getByTestId('partesMaestrosClientesGrid')
    await expect(grid.getByText('ANKAS Cliente')).toBeVisible({ timeout: 15_000 })
    await expect(grid.getByText('Otro Cliente')).not.toBeVisible()
  })

  test('tipos cliente — filtro descripción acota filas', async ({ page }) => {
    test.setTimeout(90_000)
    await mockMaestrosAdminApis(page, {
      'tipos-cliente': [
        { id: 1, code: 'TC1', descripcion: 'Premium ANKAS', activo: true },
        { id: 2, code: 'TC2', descripcion: 'Standard', activo: true },
      ],
    })
    await loginAsSupervisor(page)
    await page.goto('/archivos/partes/tipos-cliente')
    await expect(page.getByTestId('partesMaestrosTiposClienteGrid')).toBeVisible({ timeout: 20_000 })

    await fillFilterRowInput(page, 'partesMaestrosTiposClienteGrid', 1, 'ANKAS')
    const grid = page.getByTestId('partesMaestrosTiposClienteGrid')
    await expect(grid.getByText('Premium ANKAS')).toBeVisible({ timeout: 15_000 })
    await expect(grid.getByText('Standard')).not.toBeVisible()
  })

  test('tipos tarea — filtro descripción acota filas', async ({ page }) => {
    test.setTimeout(90_000)
    await mockMaestrosAdminApis(page, {
      'tipos-tarea': [
        { id: 1, code: 'TT1', descripcion: 'Soporte ANKAS', activo: true, isGenerico: false, isDefault: false },
        { id: 2, code: 'TT2', descripcion: 'Desarrollo', activo: true, isGenerico: false, isDefault: false },
      ],
    })
    await loginAsSupervisor(page)
    await page.goto('/archivos/partes/tipos-tarea')
    await expect(page.getByTestId('partesMaestrosTiposTareaGrid')).toBeVisible({ timeout: 20_000 })

    await fillFilterRowInput(page, 'partesMaestrosTiposTareaGrid', 1, 'ANKAS')
    const grid = page.getByTestId('partesMaestrosTiposTareaGrid')
    await expect(grid.getByText('Soporte ANKAS')).toBeVisible({ timeout: 15_000 })
    await expect(grid.getByText('Desarrollo')).not.toBeVisible()
  })

  test('proceso masivo — filtro cliente envía clienteNombre al API', async ({ page }) => {
    test.setTimeout(90_000)
    const all = [
      {
        id: 1,
        usuarioId: 1,
        clienteId: 1,
        tipoTareaId: 1,
        fecha: '2026-10-31',
        duracionMinutos: 15,
        sinCargo: false,
        presencial: false,
        observacion: 'obs-ank',
        cerrado: false,
        rowVersion: '1',
        usuarioCode: 'admin',
        clienteNombre: 'ANKAS SA',
        tipoTareaDescripcion: 'General',
      },
      {
        id: 2,
        usuarioId: 1,
        clienteId: 2,
        tipoTareaId: 1,
        fecha: '2026-10-31',
        duracionMinutos: 15,
        sinCargo: false,
        presencial: false,
        observacion: 'obs-otro',
        cerrado: false,
        rowVersion: '1',
        usuarioCode: 'admin',
        clienteNombre: 'Otro SA',
        tipoTareaDescripcion: 'General',
      },
    ]

    await mockMasivoSupervisorApis(page, (url) => {
      let rows = [...all]
      const clienteNombre = url.searchParams.get('clienteNombre')
      if (clienteNombre) {
        rows = rows.filter((row) => String(row.clienteNombre).includes(clienteNombre))
      }
      return { items: rows, total: rows.length }
    })

    await loginAsSupervisor(page)
    await page.goto('/partes/proceso-masivo')
    await expect(page.getByTestId('partesMasivoGrid')).toBeVisible({ timeout: 20_000 })

    const grid = page.getByTestId('partesMasivoGrid')
    await expect(grid.getByText('obs-ank')).toBeVisible({ timeout: 15_000 })
    await expect(grid.getByText('obs-otro')).toBeVisible()

    const filterReq = page.waitForRequest(
      (req) =>
        req.url().includes('/partes/tareas') &&
        new URL(req.url()).searchParams.get('clienteNombre') === 'ANKAS',
    )
    const filterInput = grid.locator('.dx-datagrid-filter-row input.dx-texteditor-input').nth(2)
    await filterInput.fill('ANKAS')
    await filterInput.press('Enter')
    await filterReq

    await expect(grid.getByText('obs-ank')).toBeVisible({ timeout: 15_000 })
    await expect(grid.getByText('obs-otro')).not.toBeVisible()
  })
})
