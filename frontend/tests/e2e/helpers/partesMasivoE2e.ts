import type { Page, Route } from '@playwright/test'

export const supervisorLoginResultado = {
  token: 'e2e-masivo-token',
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

export function envelope(resultado: unknown, extra?: { error?: number; respuesta?: string }) {
  return {
    error: extra?.error ?? 0,
    respuesta: extra?.respuesta ?? 'ok',
    resultado,
  }
}

export type MasivoTareasListHandler = (url: URL) => { items: Record<string, unknown>[]; total: number }

export async function mockMasivoSupervisorApis(
  page: Page,
  listTareas: MasivoTareasListHandler,
): Promise<void> {
  await page.route('**/api/v1/**', async (route: Route) => {
    const url = route.request().url()
    const method = route.request().method()
    const fulfill = (body: unknown, status = 200) =>
      route.fulfill({
        status,
        contentType: 'application/json',
        body: JSON.stringify(body),
      })

    if (url.includes('/auth/login') || url.includes('/auth/me')) {
      await fulfill(envelope(supervisorLoginResultado))
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
              id: 10000,
              menuKey: 'partes',
              text: 'Partes',
              routeName: '/partes',
              nodeType: 'group',
              children: [
                {
                  id: 10300,
                  menuKey: 'partes_masivo',
                  text: 'Proceso masivo',
                  routeName: '/partes/proceso-masivo',
                  procedimiento: 'partes.masivo',
                  nodeType: 'process',
                  permissions: { create: false, delete: false, update: true, read: true },
                },
              ],
            },
          ],
        }),
      )
      return
    }
    if (url.includes('/grid-layouts')) {
      if (url.includes('/active')) {
        await fulfill(
          envelope({
            layoutId: null,
            layoutName: null,
            stateJson: null,
          }),
        )
        return
      }
      await fulfill(envelope({ items: [] }))
      return
    }
    if (url.includes('/partes/catalogos/') || url.includes('/partes/tipos-tarea')) {
      await fulfill(envelope({ items: [] }))
      return
    }
    if (url.includes('/partes/tareas/ids')) {
      await fulfill(envelope({ items: [], total: 0 }))
      return
    }
    if (url.includes('/partes/masivo/informar-clientes/preview')) {
      await fulfill(
        envelope({
          destinatarioCount: 2,
          fechaDesde: '2026-01-01',
          fechaHasta: '2026-01-31',
          destinatarios: [
            {
              clienteId: 1,
              clienteCode: 'CRI',
              clienteNombre: 'Crilen',
              email: 'crilen@test.com',
              nombre: 'Crilen',
              codigo: 'CRILEN',
            },
            {
              clienteId: 2,
              clienteCode: 'GOLO',
              clienteNombre: 'Golo Tap',
              email: 'golotap@test.com',
              nombre: 'Golo Tap',
              codigo: 'GOLOTAP',
            },
          ],
        }),
      )
      return
    }
    if (url.includes('/partes/tareas') && method === 'GET') {
      const parsed = new URL(url)
      const { items, total } = listTareas(parsed)
      await fulfill(envelope({ items, total }))
      return
    }
    await fulfill(envelope({}))
  })
}

export async function loginAsSupervisor(page: Page): Promise<void> {
  await page.goto('/login')
  await page.getByTestId('loginUsuario').locator('input').fill('admin')
  await page.getByTestId('loginPassword').locator('input').fill('Paqsystems')
  await page.getByTestId('loginSubmit').click()
  await page.waitForURL(/\/partes\/?$/, { timeout: 45_000 })
}
