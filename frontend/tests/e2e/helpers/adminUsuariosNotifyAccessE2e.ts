import { expect, type Page } from '@playwright/test'

export const adminUsuariosLoginSession = {
  token: 'e2e-cc16-usuarios-token',
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

export type MockUsuarioRow = {
  id: number
  usuario: string
  name: string
  email: string
  activo: boolean
  inhabilitado: boolean
}

export function envelope(resultado: unknown, extra?: { error?: boolean | number; respuesta?: string }) {
  return {
    error: extra?.error ?? 0,
    respuesta: extra?.respuesta ?? 'ok',
    resultado,
  }
}

export function usuariosMenuEnvelope() {
  return envelope({
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
  })
}

type MockState = {
  users: MockUsuarioRow[]
  nextId: number
  createBodies: Record<string, unknown>[]
  notifyIds: number[]
}

export async function seedUsuariosAdminSession(page: Page): Promise<void> {
  await page.addInitScript((session) => {
    localStorage.setItem(
      'paqAuthSession',
      JSON.stringify({
        ...session,
        activeCompanyId: 1,
      }),
    )
  }, adminUsuariosLoginSession)
}

export async function gotoUsuariosAdmin(page: Page): Promise<void> {
  await page.goto('/admin/usuarios?cliente=DEMO')
  await expect(page.getByTestId('adminUsuariosPage')).toBeVisible({ timeout: 30_000 })
}

export type UsuariosMockOptions = {
  /** Simula rechazo HTTP del POST notify-access tras un alta con email. */
  notifyAccessFails?: boolean
}

export async function installStatefulUsuariosMock(
  page: Page,
  initialUsers: MockUsuarioRow[] = [],
  options: UsuariosMockOptions = {},
): Promise<MockState> {
  const state: MockState = {
    users: [...initialUsers],
    nextId: initialUsers.reduce((max, row) => Math.max(max, row.id), 100) + 1,
    createBodies: [],
    notifyIds: [],
  }

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
      await fulfill(envelope(adminUsuariosLoginSession))
      return
    }
    if (url.includes('/user/preferences')) {
      await fulfill(envelope({ openInNewTab: false }))
      return
    }
    if (url.includes('/user/menu')) {
      await fulfill(usuariosMenuEnvelope())
      return
    }
    if (url.includes('/health')) {
      await fulfill(
        envelope({ serviceName: 'partes', status: 'up', tenancy: 'single', db: 'unified' }),
      )
      return
    }
    if (url.includes('/auth/password-policy')) {
      await fulfill(envelope({ passwordComplejidad: 'segura', passwordLongitudMin: 10 }))
      return
    }
    if (url.includes('/admin/usuarios') && method === 'GET' && !url.includes('/notify-access')) {
      await fulfill(envelope({ items: state.users, usuarioCodigoEditable: true }))
      return
    }
    if (url.includes('/admin/usuarios') && method === 'POST' && !url.includes('/notify-access')) {
      const body = route.request().postDataJSON() as Record<string, unknown>
      state.createBodies.push(body)
      const usuario = String(body.usuario ?? body.codigo ?? '')
      const name = String(body.name ?? body.nombre ?? '')
      const email = String(body.email ?? '')
      const item: MockUsuarioRow = {
        id: state.nextId++,
        usuario,
        name,
        email,
        activo: body.activo !== false,
        inhabilitado: false,
      }
      state.users.push(item)
      await fulfill(envelope({ item }), 201)
      return
    }
    if (url.includes('/notify-access') && method === 'POST') {
      const match = url.match(/\/usuarios\/(\d+)\/notify-access/)
      const id = match ? Number(match[1]) : 0
      state.notifyIds.push(id)
      const row = state.users.find((u) => u.id === id)
      if (!row || row.email.trim() === '') {
        await fulfill(envelope({}, { error: 1, respuesta: 'admin.usuarios.notify.emailRequired' }), 422)
        return
      }
      if (options.notifyAccessFails) {
        await fulfill(
          envelope({}, { error: 1, respuesta: 'admin.usuarios.notify.emailRequired' }),
          422,
        )
        return
      }
      await fulfill(envelope({ item: { id, notified: true } }))
      return
    }
    if (url.includes('/admin/usuarios') && (method === 'PUT' || method === 'PATCH')) {
      await fulfill(envelope({ item: state.users[0] ?? {} }))
      return
    }
    await fulfill(envelope({}))
  })

  return state
}

export async function fillUsuarioForm(
  page: Page,
  data: { usuario: string; nombre: string; email: string },
): Promise<void> {
  const form = page.getByTestId('admin.usuarios.form')
  await expect(form).toBeVisible()
  const inputs = form.locator('.dx-texteditor-input')
  await inputs.nth(0).fill(data.usuario)
  await inputs.nth(1).fill(data.nombre)
  await inputs.nth(2).fill(data.email)
}

export async function openEditFirstUsuarioRow(page: Page): Promise<void> {
  const edit = page.locator('.dx-datagrid-rowsview .dx-link.dx-icon-edit').first()
  await expect(edit).toBeVisible({ timeout: 15_000 })
  await edit.click()
  await expect(page.getByTestId('admin.usuarios.form')).toBeVisible()
}
