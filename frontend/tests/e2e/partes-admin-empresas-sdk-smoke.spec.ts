import { expect, test, type Page } from '@playwright/test'

const loginResultado = {
  token: 'e2e-tr002-empresas-token',
  user: { id: 1, usuario: 'admin', locale: 'es' },
  firstLogin: false,
  minutosWeb: 60,
  tenancy: 'single',
  db: 'unified',
  empresas: [{ id: 1, nombreEmpresa: 'Demo', theme: 'paqsuite.light.generic' }],
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

function envelope(resultado: unknown) {
  return { error: 0, respuesta: 'ok', resultado }
}

async function mockApis(page: Page, locale: 'es' | 'en' | 'fr'): Promise<void> {
  await page.route('**/api/v1/**', async (route) => {
    const url = route.request().url()
    const fulfill = (body: unknown, status = 200) =>
      route.fulfill({
        status,
        contentType: 'application/json',
        body: JSON.stringify(body),
      })

    if (url.includes('/auth/login') || url.includes('/auth/me')) {
      await fulfill(
        envelope({
          ...loginResultado,
          user: { ...loginResultado.user, locale },
        }),
      )
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
                  id: 60200,
                  menuKey: 'admin_empresas',
                  labelKey: 'menu.admin.empresas',
                  text: 'Empresas',
                  routeName: '/admin/empresas',
                  procedimiento: 'admin.empresas',
                  order: 60200,
                  nodeType: 'process',
                  iconName: null,
                  processType: null,
                  permissions: {
                    create: true,
                    delete: false,
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
      await fulfill(envelope({ serviceName: 'partes', status: 'up', tenancy: 'single', db: 'unified' }))
      return
    }
    if (url.includes('/admin/empresas')) {
      await fulfill(
        envelope({
          items: [
            {
              id: 1,
              nombreEmpresa: 'Demo',
              habilitada: true,
              theme: 'paqsuite.light.generic',
              erpEmpresaId: null,
              nombreBd: null,
            },
          ],
        }),
      )
      return
    }
    await fulfill(envelope({}))
  })
}

async function openEmpresas(page: Page, locale: 'es' | 'en' | 'fr'): Promise<void> {
  await mockApis(page, locale)
  await page.addInitScript(
    ({ session, locale: guestLocale }) => {
      localStorage.setItem('paqGuestLocale', guestLocale)
      localStorage.setItem(
        'paqAuthSession',
        JSON.stringify({
          ...session,
          user: { ...session.user, locale: guestLocale },
          activeCompanyId: 1,
        }),
      )
    },
    { session: loginResultado, locale },
  )
  await page.goto('/admin/empresas?cliente=DEMO')
  await expect(page.getByTestId('adminEmpresasPage')).toBeVisible({ timeout: 30_000 })
  await expect(page.getByTestId('admin.empresas.grid')).toBeVisible({ timeout: 20_000 })
}

test('smoke empresas SDK en ES muestra título Empresas', async ({ page }) => {
  test.setTimeout(90_000)
  await openEmpresas(page, 'es')
  await expect(page.getByRole('heading', { name: 'Empresas' })).toBeVisible()
  await expect(page.getByTestId('admin.empresas.monoNote')).toContainText('sola empresa')
})

test('smoke empresas SDK en EN muestra título Companies', async ({ page }) => {
  test.setTimeout(90_000)
  await openEmpresas(page, 'en')
  await expect(page.getByRole('heading', { name: 'Companies' })).toBeVisible()
  await expect(page.getByTestId('admin.empresas.monoNote')).toContainText('Single-company')
})

test('smoke empresas SDK en FR muestra título Sociétés', async ({ page }) => {
  test.setTimeout(90_000)
  await openEmpresas(page, 'fr')
  await expect(page.getByRole('heading', { name: 'Sociétés' })).toBeVisible()
})
