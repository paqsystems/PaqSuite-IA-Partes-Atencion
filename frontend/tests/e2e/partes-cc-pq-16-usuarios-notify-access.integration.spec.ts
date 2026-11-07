import { expect, test } from '@playwright/test'
import { ensurePartesBackendHealth, loginPartesApi } from './helpers/partesPaqueteHorasE2e'
import { fillUsuarioForm, gotoUsuariosAdmin } from './helpers/adminUsuariosNotifyAccessE2e'

/**
 * Requiere backend en VITE_API_PROXY_TARGET (p. ej. :8010) y `PW_INTEGRATION=1`.
 * La conformidad de longitud/modo (simple/segura) se valida en PHPUnit ApiV1AdminUsuariosPolicyTest.
 */
test.describe('CC #16 — integración UI + API real', () => {
  test.skip(() => process.env.PW_INTEGRATION !== '1', 'Solo con PW_INTEGRATION=1')

  test.beforeEach(async ({ request }) => {
    test.setTimeout(120_000)
    await ensurePartesBackendHealth(request)
  })

  test('alta con email encadena POST create (sin password) y notify-access; edición reenvía', async ({
    page,
    request,
  }) => {
    await loginPartesApi(request)

    await page.goto('/login?cliente=DEMO')
    await page.getByTestId('loginUsuario').locator('input').fill(process.env.PW_LOGIN_USER ?? 'admin')
    await page
      .getByTestId('loginPassword')
      .locator('input')
      .fill(process.env.PW_LOGIN_PASSWORD ?? 'Paqsystems')
    await page.getByTestId('loginSubmit').click()
    await expect(page).toHaveURL(/\/partes\/?/, { timeout: 45_000 })

    await gotoUsuariosAdmin(page)
    const suffix = Date.now().toString(36)

    const createPromise = page.waitForResponse(
      (res) =>
        res.url().includes('/admin/usuarios') &&
        res.request().method() === 'POST' &&
        !res.url().includes('notify-access'),
    )
    const notifyPromise = page.waitForResponse(
      (res) => res.url().includes('/notify-access') && res.request().method() === 'POST',
    )

    await page.getByTestId('admin.usuarios.create').click()
    await fillUsuarioForm(page, {
      usuario: `uie2e${suffix}`,
      nombre: 'UI E2E CC16',
      email: `uie2e${suffix}@partes.local`,
    })
    await page.getByTestId('admin.usuarios.save').click()

    const createRes = await createPromise
    expect(createRes.status()).toBe(201)
    const createBody = createRes.request().postDataJSON() as Record<string, unknown>
    expect(createBody.email).toMatch(/@partes\.local$/)
    expect(createBody).not.toHaveProperty('password')

    const notifyRes = await notifyPromise
    expect(notifyRes.ok()).toBeTruthy()

    await expect(page.getByTestId('admin.usuarios.form')).toHaveCount(0, { timeout: 15_000 })

    await page.locator('.dx-datagrid-rowsview .dx-link.dx-icon-edit').first().click()
    await expect(page.getByTestId('adminUsuariosNotifyEmail')).toBeEnabled()

    const notifyAgain = page.waitForResponse(
      (res) => res.url().includes('/notify-access') && res.request().method() === 'POST',
    )
    await page.getByTestId('adminUsuariosNotifyEmail').click()
    expect((await notifyAgain).ok()).toBeTruthy()
  })
})
