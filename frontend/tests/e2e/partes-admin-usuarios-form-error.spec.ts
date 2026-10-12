import { expect, test, type Page } from '@playwright/test'

const loginResultado = {
  token: 'e2e-cc5-token',
  user: { id: 1, usuario: 'admin', locale: 'es' },
  firstLogin: false,
  minutosWeb: 60,
  tenancy: 'single',
  db: 'unified',
  empresas: [{ id: 1, nombreEmpresa: 'Demo', theme: null }],
  cliente: 'DEMO',
  partes: {
    tipoFuncional: 'asistente',
    asistenteId: 1,
    clienteId: null,
    esSupervisor: true,
    code: 'admin',
    nombre: 'Admin',
    email: null,
  },
}

function envelope(resultado: unknown, extra?: { error?: boolean | number; respuesta?: string }) {
  return {
    error: extra?.error ?? 0,
    respuesta: extra?.respuesta ?? 'ok',
    resultado,
  }
}

async function mockApis(page: Page): Promise<void> {
  await page.route('**/api/v1/**', async (route) => {
    const url = route.request().url()
    const method = route.request().method()
    const fulfill = (body: unknown, status = 200) =>
      route.fulfill({
        status,
        contentType: 'application/json',
        body: JSON.stringify(body),
      })

    if (url.includes('/auth/login') || url.includes('/auth/me')) {
      await fulfill(envelope(loginResultado))
      return
    }
    if (url.includes('/user/preferences')) {
      await fulfill(envelope({ openInNewTab: false }))
      return
    }
    if (url.includes('/user/menu')) {
      await fulfill(
        envelope({
          items: [
            {
              id: 60000,
              menuKey: 'seguridad',
              labelKey: null,
              text: 'Seguridad',
              routeName: null,
              procedimiento: null,
              order: 60000,
              nodeType: 'group',
              iconName: null,
              processType: null,
              permissions: null,
              children: [
                {
                  id: 60100,
                  menuKey: 'admin_usuarios',
                  labelKey: null,
                  text: 'Usuarios',
                  routeName: '/admin/usuarios',
                  procedimiento: 'partes.admin.usuarios',
                  order: 60100,
                  nodeType: 'process',
                  iconName: null,
                  processType: null,
                  permissions: {
                    create: true,
                    delete: true,
                    update: true,
                    report: true,
                  },
                  children: [],
                },
              ],
            },
          ],
        }),
      )
      return
    }
    if (url.includes('/health')) {
      await fulfill(
        envelope({ serviceName: 'partes', status: 'up', tenancy: 'single', db: 'unified' }),
      )
      return
    }
    if (url.includes('/admin/usuarios') && method === 'GET') {
      await fulfill(envelope({ items: [], usuarioCodigoEditable: true }))
      return
    }
    if (url.includes('/admin/usuarios') && method === 'POST') {
      await fulfill(
        envelope({}, { error: 1, respuesta: 'auth.password.policyUnsafe' }),
      )
      return
    }
    await fulfill(envelope({}))
  })
}

test('alta de usuario con clave insegura muestra el error dentro del modal', async ({ page }) => {
  test.setTimeout(90_000)
  await mockApis(page)
  await page.addInitScript((session) => {
    localStorage.setItem(
      'paqAuthSession',
      JSON.stringify({
        ...session,
        activeCompanyId: 1,
      }),
    )
  }, loginResultado)

  await page.goto('/admin/usuarios?cliente=DEMO')
  await expect(page.getByTestId('adminUsuariosPage')).toBeVisible({ timeout: 30_000 })
  await expect(page.getByTestId('admin.usuarios.create')).toBeVisible({ timeout: 20_000 })
  await page.getByTestId('admin.usuarios.create').click()
  await expect(page.getByTestId('admin.usuarios.form')).toBeVisible()

  const form = page.getByTestId('admin.usuarios.form')
  const inputs = form.locator('.dx-texteditor-input')
  await inputs.nth(0).fill('cc5smoke')
  await inputs.nth(1).fill('CC5 Smoke')
  await inputs.nth(2).fill('cc5smoke@example.com')
  await inputs.nth(3).fill('123')
  await page.getByTestId('admin.usuarios.save').click()

  const formError = page.getByTestId('adminUsuariosFormError')
  await expect(formError).toBeVisible()
  await expect(page.getByTestId('admin.usuarios.form')).toBeVisible()
  await expect(page.getByTestId('adminUsuariosListError')).toHaveCount(0)
})
