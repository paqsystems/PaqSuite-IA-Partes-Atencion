import type { Page, Route } from '@playwright/test'
import { envelope, supervisorLoginResultado } from './partesMasivoE2e'

export type MaestroListItem = Record<string, unknown>

function filterMaestroItems(items: MaestroListItem[], url: URL): { items: MaestroListItem[]; total: number } {
  let rows = [...items]
  const code = url.searchParams.get('code')
  const nombre = url.searchParams.get('nombre')
  const descripcion = url.searchParams.get('descripcion')
  if (code) {
    rows = rows.filter((row) => String(row.code ?? '').includes(code))
  }
  if (nombre) {
    rows = rows.filter((row) => String(row.nombre ?? '').includes(nombre))
  }
  if (descripcion) {
    rows = rows.filter((row) => String(row.descripcion ?? '').includes(descripcion))
  }
  const page = Math.max(1, Number(url.searchParams.get('page') ?? 1))
  const pageSize = Math.max(1, Number(url.searchParams.get('pageSize') ?? 20))
  const total = rows.length
  const start = (page - 1) * pageSize
  return { items: rows.slice(start, start + pageSize), total }
}

const archivosMenu = {
  id: 20000,
  menuKey: 'archivos',
  text: 'Archivos',
  routeName: '/archivos',
  nodeType: 'group',
  children: [
    {
      id: 20200,
      menuKey: 'partes_asistentes',
      text: 'Asistentes',
      routeName: '/archivos/partes/asistentes',
      nodeType: 'process',
      permissions: { create: true, delete: true, update: true, read: true },
    },
    {
      id: 20201,
      menuKey: 'partes_clientes',
      text: 'Clientes',
      routeName: '/archivos/partes/clientes',
      nodeType: 'process',
      permissions: { create: true, delete: true, update: true, read: true },
    },
    {
      id: 20202,
      menuKey: 'partes_tipos_cliente',
      text: 'Tipos cliente',
      routeName: '/archivos/partes/tipos-cliente',
      nodeType: 'process',
      permissions: { create: true, delete: true, update: true, read: true },
    },
    {
      id: 20203,
      menuKey: 'partes_tipos_tarea',
      text: 'Tipos tarea',
      routeName: '/archivos/partes/tipos-tarea',
      nodeType: 'process',
      permissions: { create: true, delete: true, update: true, read: true },
    },
  ],
}

export async function mockMaestrosAdminApis(
  page: Page,
  lists: Record<string, MaestroListItem[]>,
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
              children: [archivosMenu],
            },
          ],
        }),
      )
      return
    }
    if (url.includes('/grid-layouts')) {
      if (url.includes('/active')) {
        await fulfill(envelope({ layoutId: null, layoutName: null, stateJson: null }))
        return
      }
      await fulfill(envelope({ items: [] }))
      return
    }

    for (const [resource, items] of Object.entries(lists)) {
      if (url.includes(`/partes/${resource}`) && method === 'GET' && !url.match(/\/partes\/[^/]+\/\d+/)) {
        const parsed = new URL(url)
        await fulfill(envelope(filterMaestroItems(items, parsed)))
        return
      }
    }

    await fulfill(envelope({ items: [] }))
  })
}

/** Escribe en la fila de filtros (índice de input visible, 0-based). */
export async function fillFilterRowInput(page: Page, gridTestId: string, inputIndex: number, value: string) {
  const grid = page.getByTestId(gridTestId)
  const input = grid.locator('.dx-datagrid-filter-row input.dx-texteditor-input').nth(inputIndex)
  await input.waitFor({ state: 'visible', timeout: 15_000 })
  const requestPromise = page.waitForRequest(
    (req) => req.method() === 'GET' && req.url().includes('/partes/') && req.url().includes(encodeURIComponent(value)),
    { timeout: 20_000 },
  )
  await input.fill(value)
  await input.press('Enter')
  await requestPromise
}
