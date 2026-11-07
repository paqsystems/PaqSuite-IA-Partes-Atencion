import { expect, test } from '@playwright/test'
import {
  fillUsuarioForm,
  gotoUsuariosAdmin,
  installStatefulUsuariosMock,
  openEditFirstUsuarioRow,
  seedUsuariosAdminSession,
} from './helpers/adminUsuariosNotifyAccessE2e'

test.describe('CC #16 — ABM usuarios notify por usuario (UI)', () => {
  test.beforeEach(async ({ page }) => {
    test.setTimeout(90_000)
  })

  test('alta: sin campo contraseña; POST create sin password y encadena notify-access', async ({ page }) => {
    const state = await installStatefulUsuariosMock(page)
    await seedUsuariosAdminSession(page)
    await gotoUsuariosAdmin(page)

    await expect(page.getByTestId('admin.usuarios.create')).toBeVisible({ timeout: 20_000 })
    await page.getByTestId('admin.usuarios.create').click()
    await expect(page.getByTestId('admin.usuarios.form')).toBeVisible()

    const inputs = page.getByTestId('admin.usuarios.form').locator('.dx-texteditor-input')
    await expect(inputs).toHaveCount(3)

    await fillUsuarioForm(page, {
      usuario: 'cc16alta',
      nombre: 'CC16 Alta',
      email: 'cc16alta@example.com',
    })
    await page.getByTestId('admin.usuarios.save').click()

    await expect(page.getByTestId('admin.usuarios.form')).toHaveCount(0, { timeout: 15_000 })

    expect(state.createBodies).toHaveLength(1)
    const body = state.createBodies[0]!
    expect(body.email).toBe('cc16alta@example.com')
    expect(body).not.toHaveProperty('password')
    expect(state.notifyIds).toEqual([state.users[0]!.id])
  })

  test('alta sin email: error de validación en el modal', async ({ page }) => {
    await installStatefulUsuariosMock(page)
    await seedUsuariosAdminSession(page)
    await gotoUsuariosAdmin(page)

    await page.getByTestId('admin.usuarios.create').click()
    await fillUsuarioForm(page, {
      usuario: 'sincorreo',
      nombre: 'Sin Correo',
      email: '',
    })
    await page.getByTestId('admin.usuarios.save').click()

    await expect(page.getByTestId('adminUsuariosFormError')).toBeVisible()
    await expect(page.getByTestId('admin.usuarios.form')).toBeVisible()
  })

  test('edición: ícono notify deshabilitado si el usuario no tiene mail', async ({ page }) => {
    await installStatefulUsuariosMock(page, [
      {
        id: 201,
        usuario: 'sinmail',
        name: 'Sin Mail',
        email: '',
        activo: true,
        inhabilitado: false,
      },
    ])
    await seedUsuariosAdminSession(page)
    await gotoUsuariosAdmin(page)
    await openEditFirstUsuarioRow(page)
    await expect(page.getByTestId('adminUsuariosNotifyEmail')).toHaveAttribute('aria-disabled', 'true')
  })

  test('edición: ícono notify habilitado si el usuario tiene mail', async ({ page }) => {
    await installStatefulUsuariosMock(page, [
      {
        id: 202,
        usuario: 'conmail',
        name: 'Con Mail',
        email: 'conmail@example.com',
        activo: true,
        inhabilitado: false,
      },
    ])
    await seedUsuariosAdminSession(page)
    await gotoUsuariosAdmin(page)
    await openEditFirstUsuarioRow(page)
    await expect(page.getByTestId('adminUsuariosNotifyEmail')).not.toHaveAttribute('aria-disabled', 'true')
  })

  test('edición: click en ícono llama notify-access cuando hay email', async ({ page }) => {
    const state = await installStatefulUsuariosMock(page, [
      {
        id: 301,
        usuario: 'cc16send',
        name: 'CC16 Send',
        email: 'cc16send@example.com',
        activo: true,
        inhabilitado: false,
      },
    ])
    await seedUsuariosAdminSession(page)
    await gotoUsuariosAdmin(page)

    await openEditFirstUsuarioRow(page)
    await page.getByTestId('adminUsuariosNotifyEmail').click()

    await expect.poll(() => state.notifyIds).toEqual([301])
    await expect(page.getByTestId('adminUsuariosNotifyFormError')).toHaveCount(0)
  })

})
