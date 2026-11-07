import { expect, test } from '@playwright/test'
import {
  fillUsuarioForm,
  gotoUsuariosAdmin,
  installStatefulUsuariosMock,
  seedUsuariosAdminSession,
} from './helpers/adminUsuariosNotifyAccessE2e'

test('fallo de notify tras alta muestra error dentro del modal (CC #16)', async ({ page }) => {
  test.setTimeout(90_000)
  const state = await installStatefulUsuariosMock(page, [], { notifyAccessFails: true })

  await seedUsuariosAdminSession(page)
  await gotoUsuariosAdmin(page)
  await page.getByTestId('admin.usuarios.create').click()
  await fillUsuarioForm(page, {
    usuario: 'cc5smoke',
    nombre: 'CC5 Smoke',
    email: 'cc5smoke@example.com',
  })
  await page.getByTestId('admin.usuarios.save').click()

  await expect.poll(() => state.notifyIds.length).toBe(1)
  await expect(page.getByTestId('adminUsuariosNotifyFormError')).toBeVisible({ timeout: 15_000 })
  await expect(page.getByTestId('admin.usuarios.form')).toBeVisible()
})
