import { expect, test, type Page } from '@playwright/test'
import {
  captureUiScreenshot,
  clickGridFirstEdit,
  e2eTenant,
  initScreenshotManifest,
  loginPartesUi,
  openEmissionDesignerReportStep,
  openUserAvatarMenu,
  switchConsultaDetalladaPivot,
  uiScreenshotDir,
  withTenantQuery,
  waitForDxReady,
} from './helpers/uiScreenshotCapture'
import {
  ensurePartesBackendHealth,
  seedPaqueteHorasFixture,
  selectDevExtremeSelectByText,
  waitForGridWithDataRows,
  type PaqueteHorasE2eFixture,
} from './helpers/partesPaqueteHorasE2e'

const viewport = { width: 1440, height: 900 }
const clientCode = process.env.PW_DEV_CLIENT_CODE ?? '4D'
const fechaDesde = process.env.PW_DEV_FECHA_DESDE ?? '2026-10-01'
const fechaHasta = process.env.PW_DEV_FECHA_HASTA ?? '2026-10-31'

const uiCaptureTestTimeoutMs = Number(process.env.PW_UI_CAPTURE_TEST_TIMEOUT_MS ?? 360_000)

test.describe.configure({ mode: 'serial', retries: 0, timeout: uiCaptureTestTimeoutMs })
test.use({ viewport })

test.beforeAll(() => {
  test.skip(process.env.PW_UI_CAPTURE !== '1', 'Activar con PW_UI_CAPTURE=1 o npm run test:e2e:ui-screenshots')
  initScreenshotManifest(viewport, process.env.PW_BASE_URL ?? 'http://localhost:3101')
})

test.describe('Catálogo UI — invitado', () => {
  test('01 — olvidé la contraseña', async ({ page }) => {
    await page.goto(withTenantQuery('/forgot-password'))
    await expect(page.getByTestId('authForgotPage')).toBeVisible({ timeout: 20_000 })
    await captureUiScreenshot(page, {
      id: 'auth-forgot-password',
      fileName: '01-auth-forgot-password.png',
      title: 'Olvidé la contraseña',
    })
  })
})

test.describe('Catálogo UI — autenticado', () => {
  let paqueteFixture: PaqueteHorasE2eFixture | null = null
  test.beforeAll(async ({ request }) => {
    const fs = await import('node:fs')
    fs.mkdirSync(uiScreenshotDir, { recursive: true })
    await ensurePartesBackendHealth(request)
    try {
      paqueteFixture = await seedPaqueteHorasFixture(request)
    } catch {
      paqueteFixture = null
    }
  })

  test.beforeEach(async ({ page }) => {
    await loginPartesUi(page)
  })

  async function gotoPath(page: Page, pathname: string): Promise<void> {
    await page.goto(withTenantQuery(pathname))
    await waitForDxReady(page)
  }

  test('02 — pantalla principal (menú + dashboard)', async ({ page }) => {
    await gotoPath(page, '/partes')
    await expect(page.getByTestId('partesDashboardRoot')).toBeVisible({ timeout: 45_000 })
    await captureUiScreenshot(page, {
      id: 'shell-dashboard',
      fileName: '02-shell-dashboard-menu.png',
      title: 'Pantalla principal (Menú + dashboard)',
    })
  })

  test('03 — menú avatar (apariencia compact)', async ({ page }) => {
    await gotoPath(page, '/partes')
    await page.evaluate(() => {
      document.documentElement.setAttribute('data-theme', 'paqsuite.dark.compact')
    })
    await openUserAvatarMenu(page)
    await captureUiScreenshot(page, {
      id: 'shell-avatar-menu-compact',
      fileName: '03-shell-avatar-menu-compact.png',
      title: 'Menú Avatar (apariencia compact)',
    })
    await page.keyboard.press('Escape')
  })

  test('04–05 — preferencias LLM', async ({ page }) => {
    await gotoPath(page, '/partes')
    await openUserAvatarMenu(page)
    const preferencesItem = page
      .getByTestId('userMenu.preferences')
      .or(page.getByRole('menuitem', { name: /preferencias/i }))
    await preferencesItem.first().click()
    await waitForDxReady(page)
    await captureUiScreenshot(page, {
      id: 'llm-preferences-list',
      fileName: '04-llm-preferences.png',
      title: 'Preferencias',
    })

    const addBtn = page
      .getByTestId('llmPreferences.add')
      .or(page.getByRole('button', { name: /^Agregar$|^Add$/i }))
    if ((await addBtn.count()) > 0) {
      await addBtn.first().click()
    } else {
      const editBtn = page.locator('[data-testid*="llm" i] .dx-icon-edit, .dx-datagrid .dx-link-edit').first()
      if ((await editBtn.count()) > 0) {
        await editBtn.click()
      }
    }
    await waitForDxReady(page)
    await captureUiScreenshot(page, {
      id: 'llm-preferences-edit',
      fileName: '05-llm-preferences-edit.png',
      title: 'Preferencias — edición',
    })
    await page.keyboard.press('Escape')
  })

  test('06 — asistente IA', async ({ page }) => {
    await gotoPath(page, '/chat-assistant')
    await expect(page.getByTestId('chatAssistant.page')).toBeVisible({ timeout: 30_000 })
    await captureUiScreenshot(page, {
      id: 'chat-assistant',
      fileName: '06-chat-assistant.png',
      title: 'Asistente IA',
    })
  })

  test('07 — cambio contraseña', async ({ page }) => {
    await gotoPath(page, '/change-password')
    await expect(page.getByTestId('authChangePage')).toBeVisible({ timeout: 20_000 })
    await captureUiScreenshot(page, {
      id: 'auth-change-password',
      fileName: '07-auth-change-password.png',
      title: 'Cambio contraseña',
    })
  })

  const maestroScreens: Array<{
    path: string
    gridId: string
    formId: string
    listFile: string
    editFile: string
    title: string
    slug: string
  }> = [
    {
      path: '/archivos/partes/asistentes',
      gridId: 'partesMaestrosAsistentesGrid',
      formId: 'partesMaestrosAsistentesForm',
      listFile: '08-maestros-asistentes-grilla.png',
      editFile: '09-maestros-asistentes-editar.png',
      title: 'ABM Asistentes',
      slug: 'asistentes',
    },
    {
      path: '/archivos/partes/clientes',
      gridId: 'partesMaestrosClientesGrid',
      formId: 'partesMaestrosClientesForm',
      listFile: '10-maestros-clientes-grilla.png',
      editFile: '11-maestros-clientes-editar.png',
      title: 'ABM Clientes',
      slug: 'clientes',
    },
    {
      path: '/archivos/partes/tipos-cliente',
      gridId: 'partesMaestrosTiposClienteGrid',
      formId: 'partesMaestrosTiposClienteForm',
      listFile: '12-maestros-tipos-cliente-grilla.png',
      editFile: '13-maestros-tipos-cliente-editar.png',
      title: 'ABM Tipos de cliente',
      slug: 'tipos-cliente',
    },
    {
      path: '/archivos/partes/tipos-tarea',
      gridId: 'partesMaestrosTiposTareaGrid',
      formId: 'partesMaestrosTiposTareaForm',
      listFile: '14-maestros-tipos-tarea-grilla.png',
      editFile: '15-maestros-tipos-tarea-editar.png',
      title: 'ABM Tipo de tarea',
      slug: 'tipos-tarea',
    },
    {
      path: '/archivos/partes/cliente-tipos-tarea',
      gridId: 'partesMaestrosAsignacionesGrid',
      formId: 'partesMaestrosAsignacionesForm',
      listFile: '16-maestros-asignaciones-grilla.png',
      editFile: '17-maestros-asignaciones-editar.png',
      title: 'ABM Asignación tipos por cliente',
      slug: 'asignaciones',
    },
  ]

  for (const item of maestroScreens) {
    test(`Maestros — ${item.title}`, async ({ page }) => {
      await gotoPath(page, item.path)
      const pageTestId = item.gridId.replace(/Grid$/, 'Page')
      await expect(page.getByTestId(pageTestId)).toBeVisible({ timeout: 45_000 })
      await captureUiScreenshot(page, {
        id: `maestros-${item.slug}-list`,
        fileName: item.listFile,
        title: `${item.title} — grilla`,
      })
      const opened = await clickGridFirstEdit(page, item.gridId)
      if (opened) {
        await expect(page.getByTestId(item.formId)).toBeVisible({ timeout: 15_000 })
        await captureUiScreenshot(page, {
          id: `maestros-${item.slug}-edit`,
          fileName: item.editFile,
          title: `${item.title} — edición`,
        })
        await page.keyboard.press('Escape')
      } else {
        await captureUiScreenshot(page, {
          id: `maestros-${item.slug}-edit`,
          fileName: item.editFile,
          title: `${item.title} — edición (sin filas)`,
        })
      }
    })
  }

  test('18–20 — carga partes', async ({ page }) => {
    await gotoPath(page, '/partes/carga-diaria')
    await expect(page.getByTestId('partesCargaPage')).toBeVisible({ timeout: 45_000 })
    await captureUiScreenshot(page, {
      id: 'carga-partes-inicial',
      fileName: '18-carga-partes-inicial.png',
      title: 'Carga Partes — pantalla inicial',
    })

    const importBtn = page.getByTestId('excelImport.import')
    if ((await importBtn.count()) > 0) {
      await importBtn.click()
      await expect(page.getByTestId('excelImport.modal')).toBeVisible({ timeout: 15_000 })
      await captureUiScreenshot(page, {
        id: 'carga-partes-excel',
        fileName: '19-carga-partes-importacion-excel.png',
        title: 'Carga Partes — importación Excel',
      })
      await page.keyboard.press('Escape')
    }

    const edited = await clickGridFirstEdit(page, 'partesCargaGrid')
    if (edited) {
      await expect(page.getByTestId('partesCargaForm')).toBeVisible({ timeout: 15_000 })
      await captureUiScreenshot(page, {
        id: 'carga-partes-edit',
        fileName: '20-carga-partes-edicion.png',
        title: 'Carga Partes — edición',
      })
      await page.keyboard.press('Escape')
    }
  })

  test('21 — proceso masivo', async ({ page }) => {
    await gotoPath(page, '/partes/proceso-masivo')
    await expect(page.getByTestId('partesMasivoPage')).toBeVisible({ timeout: 45_000 })
    await captureUiScreenshot(page, {
      id: 'proceso-masivo',
      fileName: '21-proceso-masivo-inicial.png',
      title: 'Proceso masivo — pantalla inicial',
    })
  })

  test('22 — carga paquetes de horas', async ({ page }) => {
    await gotoPath(page, '/partes/carga-paquetes')
    await expect(page.getByTestId('partesCargaPaquetesPage')).toBeVisible({ timeout: 45_000 })
    await captureUiScreenshot(page, {
      id: 'carga-paquetes',
      fileName: '22-carga-paquetes-horas-inicial.png',
      title: 'Carga de paquetes de horas — pantalla inicial',
    })
  })

  test('23–25 — consulta detallada', async ({ page }) => {
    await gotoPath(page, '/partes/informes/consulta-detallada')
    await expect(page.getByTestId('partesConsultaDetalladaPage')).toBeVisible({ timeout: 45_000 })
    await waitForDxReady(page)
    await captureUiScreenshot(page, {
      id: 'consulta-detallada-inicial',
      fileName: '23-consulta-detallada-inicial.png',
      title: 'Consulta detallada — pantalla inicial',
    })

    const switched = await switchConsultaDetalladaPivot(page)
    if (switched) {
      await captureUiScreenshot(page, {
        id: 'consulta-detallada-pivot',
        fileName: '24-consulta-detallada-pivot.png',
        title: 'Consulta detallada — modo pivot',
      })
    }

    const emit = page.getByTestId('partesConsultaDetalladaEmit')
    if ((await emit.count()) > 0 && (await emit.isEnabled())) {
      await emit.click()
      await expect(page.getByTestId('emission.dialog')).toBeVisible({ timeout: 15_000 })
      await captureUiScreenshot(page, {
        id: 'consulta-detallada-emision',
        fileName: '25-consulta-detallada-emision.png',
        title: 'Consulta detallada — ventana emisión',
      })
      await page.keyboard.press('Escape')
    }
  })

  test('26–27 — paquete de horas informe', async ({ page }) => {
    await gotoPath(page, '/partes/informes/paquete-horas')
    await expect(page.getByTestId('partesPaqueteHorasPage')).toBeVisible({ timeout: 45_000 })
    await captureUiScreenshot(page, {
      id: 'paquete-horas-inicial',
      fileName: '26-paquete-horas-inicial.png',
      title: 'Paquete de horas — ventana inicial',
    })

    if (paqueteFixture) {
      try {
        await selectDevExtremeSelectByText(page, 'partesPaqueteCliente', paqueteFixture.clientDisplay)
        await page.getByTestId('partesPaqueteBuscar').click()
        await waitForGridWithDataRows(page, 1)
      } catch {
        await waitForDxReady(page)
      }
      await captureUiScreenshot(page, {
        id: 'paquete-horas-con-datos',
        fileName: '27-paquete-horas-con-datos.png',
        title: 'Paquete de horas — con datos en grilla',
      })
    } else {
      await page.getByTestId('partesPaqueteFechaDesde').getByRole('combobox').fill(fechaDesde)
      await page.getByTestId('partesPaqueteFechaHasta').getByRole('combobox').fill(fechaHasta)
      try {
        await selectDevExtremeSelectByText(page, 'partesPaqueteCliente', clientCode)
        await page.getByTestId('partesPaqueteBuscar').click()
        await waitForDxReady(page)
      } catch {
        // entorno sin catálogo cliente
      }
      await captureUiScreenshot(page, {
        id: 'paquete-horas-con-datos',
        fileName: '27-paquete-horas-con-datos.png',
        title: 'Paquete de horas — con datos (mejor esfuerzo)',
      })
    }
  })

  test('28–29 — ABM usuarios', async ({ page }) => {
    await gotoPath(page, '/admin/usuarios')
    await expect(page.getByTestId('adminUsuariosPage')).toBeVisible({ timeout: 45_000 })
    await expect(page.getByTestId('admin.usuarios.grid')).toBeVisible({ timeout: 30_000 })
    await captureUiScreenshot(page, {
      id: 'admin-usuarios-list',
      fileName: '28-admin-usuarios-inicial.png',
      title: 'ABM Usuarios — pantalla inicial',
    })
    const opened = await clickGridFirstEdit(page, 'admin.usuarios.grid')
    if (opened) {
      await expect(page.getByTestId('admin.usuarios.form')).toBeVisible({ timeout: 15_000 })
      await captureUiScreenshot(page, {
        id: 'admin-usuarios-edit',
        fileName: '29-admin-usuarios-editar.png',
        title: 'ABM Usuarios — edición',
      })
      await page.keyboard.press('Escape')
    }
  })

  test('30–31 — ABM empresa', async ({ page }) => {
    await gotoPath(page, '/admin/empresas')
    await expect(page.getByTestId('adminEmpresasPage')).toBeVisible({ timeout: 45_000 })
    await captureUiScreenshot(page, {
      id: 'admin-empresas-list',
      fileName: '30-admin-empresas-inicial.png',
      title: 'ABM Empresa — pantalla inicial',
    })
    const opened = await clickGridFirstEdit(page, 'admin.empresas.grid')
    if (opened) {
      await expect(page.getByTestId('adminEmpresasForm').or(page.getByTestId('admin.empresas.form'))).toBeVisible({
        timeout: 15_000,
      })
      await captureUiScreenshot(page, {
        id: 'admin-empresas-edit',
        fileName: '31-admin-empresas-editar.png',
        title: 'ABM Empresa — edición',
      })
      await page.keyboard.press('Escape')
    }
  })

  test('32–34 — ABM roles', async ({ page }) => {
    await gotoPath(page, '/admin/roles')
    await expect(page.getByTestId('adminRolesPage')).toBeVisible({ timeout: 45_000 })
    await captureUiScreenshot(page, {
      id: 'admin-roles-list',
      fileName: '32-admin-roles-inicial.png',
      title: 'ABM Roles — pantalla inicial',
    })

    const grid = page.locator('[data-testid="security.roles.grid"], [data-testid*="roles" i] .dx-datagrid').first()
    const edit = grid.locator('.dx-link-edit, .dx-icon-edit').first()
    if ((await edit.count()) > 0) {
      await edit.click()
      await waitForDxReady(page)
      await captureUiScreenshot(page, {
        id: 'admin-roles-edit',
        fileName: '33-admin-roles-editar.png',
        title: 'ABM Roles — edición de un rol',
      })
      await page.keyboard.press('Escape')
    }

    const atributos = page.getByRole('link', { name: /atributos|permisos/i }).first()
    if ((await atributos.count()) > 0) {
      await atributos.click()
    } else if ((await grid.locator('.dx-data-row').count()) > 0) {
      const roleId = await page.locator('.dx-datagrid-rowsview .dx-data-row').first().getAttribute('aria-rowindex')
      if (roleId) {
        await gotoPath(page, '/admin/roles/1/atributos')
      }
    } else {
      await gotoPath(page, '/admin/roles/1/atributos')
    }
    await expect(page.getByTestId('adminRolesAtributosPage')).toBeVisible({ timeout: 30_000 })
    await captureUiScreenshot(page, {
      id: 'admin-roles-permisos',
      fileName: '34-admin-roles-seleccion-permisos.png',
      title: 'ABM Roles — selección de permisos',
    })
  })

  test('35–38 — ABM permisos', async ({ page }) => {
    await gotoPath(page, '/admin/permisos')
    await expect(page.getByTestId('adminPermisosPage')).toBeVisible({ timeout: 45_000 })
    await captureUiScreenshot(page, {
      id: 'admin-permisos-list',
      fileName: '35-admin-permisos-inicial.png',
      title: 'ABM Permisos — pantalla inicial',
    })

    const grid = page.locator('[data-testid*="permisos" i] .dx-datagrid').first()
    const edit = grid.locator('.dx-link-edit, .dx-icon-edit').first()
    if ((await edit.count()) > 0) {
      await edit.click()
      await captureUiScreenshot(page, {
        id: 'admin-permisos-edit',
        fileName: '36-admin-permisos-editar.png',
        title: 'ABM Permisos — edición',
      })
      await page.keyboard.press('Escape')
    }

    const tabUsuario = page.getByRole('tab', { name: /usuario/i })
    if ((await tabUsuario.count()) > 0) {
      await tabUsuario.click()
      await waitForDxReady(page)
      await captureUiScreenshot(page, {
        id: 'admin-permisos-por-usuario',
        fileName: '37-admin-permisos-por-usuario.png',
        title: 'ABM Permisos — permisos por usuario',
      })
    }

    const tabRol = page.getByRole('tab', { name: /rol/i })
    if ((await tabRol.count()) > 0) {
      await tabRol.click()
      await waitForDxReady(page)
      await captureUiScreenshot(page, {
        id: 'admin-permisos-por-rol',
        fileName: '38-admin-permisos-por-rol.png',
        title: 'ABM Permisos — permisos por rol',
      })
    }
  })

  test('39–40 — parámetros AUTH', async ({ page }) => {
    await gotoPath(page, '/parametros/Auth')
    await waitForDxReady(page)
    await captureUiScreenshot(page, {
      id: 'parametros-auth-list',
      fileName: '39-parametros-auth-inicial.png',
      title: 'ABM Parámetros AUTH — pantalla inicial',
    })

    const grid = page.locator('.dx-datagrid').first()
    const edit = grid.locator('.dx-link-edit, .dx-icon-edit').first()
    if ((await edit.count()) > 0) {
      await edit.click()
      await waitForDxReady(page)
      await captureUiScreenshot(page, {
        id: 'parametros-auth-edit',
        fileName: '40-parametros-auth-editar.png',
        title: 'ABM Parámetros AUTH — edición',
      })
      await page.keyboard.press('Escape')
    }
  })

  test('41–43 — diseñador de informes', async ({ page }) => {
    await gotoPath(page, '/emisiones/disenador')
    await expect(page.getByTestId('partesEmisionesDisenadorPage')).toBeVisible({ timeout: 45_000 })
    await captureUiScreenshot(page, {
      id: 'emisiones-disenador-proceso',
      fileName: '41-disenador-seleccion-proceso.png',
      title: 'Diseñador informes — selección proceso',
    })

    const ready = await openEmissionDesignerReportStep(page)
    if (ready) {
      await captureUiScreenshot(page, {
        id: 'emisiones-disenador-informe',
        fileName: '42-disenador-seleccion-informe.png',
        title: 'Diseñador informes — selección informe',
      })
      const reportCombo = page.getByTestId('emission.design.report')
      await reportCombo.click()
      const reportOption = page.getByRole('option').first()
      if ((await reportOption.count()) > 0) {
        await reportOption.click()
        const setPrincipal = page.getByTestId('emission.design.setPrincipal')
        if ((await setPrincipal.count()) > 0) {
          await setPrincipal.click()
        }
      }
      const designerHost = page
        .getByTestId('emissions.designer.dxHost')
        .or(page.getByTestId('emission.design.host'))
      const hostVisible = await designerHost
        .waitFor({ state: 'visible', timeout: 120_000 })
        .then(() => true)
        .catch(() => false)
      if (hostVisible) {
        await captureUiScreenshot(page, {
          id: 'emisiones-disenador-canvas',
          fileName: '43-disenador-canvas.png',
          title: 'Diseñador informes — diseñador',
          fullPage: true,
        })
      }
    }
  })

  const tareasTabs: Array<{ path: string; testId: string; file: string; title: string; id: string }> = [
    {
      path: '/tareas-programadas/procesos',
      testId: 'tareasProgramadas.procesos',
      file: '44-tareas-programadas-procesos.png',
      title: 'Tareas programadas — solapa Procesos',
      id: 'tareas-procesos',
    },
    {
      path: '/tareas-programadas/definiciones',
      testId: 'tareasProgramadas.definiciones',
      file: '45-tareas-programadas-definiciones.png',
      title: 'Tareas programadas — solapa Definición',
      id: 'tareas-definiciones',
    },
    {
      path: '/tareas-programadas/ejecuciones',
      testId: 'tareasProgramadas.ejecuciones',
      file: '46-tareas-programadas-ejecuciones.png',
      title: 'Tareas programadas — solapa Ejecuciones',
      id: 'tareas-ejecuciones',
    },
    {
      path: '/tareas-programadas/historial',
      testId: 'tareasProgramadas.historial',
      file: '47-tareas-programadas-historial.png',
      title: 'Tareas programadas — solapa Historial',
      id: 'tareas-historial',
    },
  ]

  for (const tab of tareasTabs) {
    test(tab.title, async ({ page }) => {
      await gotoPath(page, tab.path)
      await expect(page.getByTestId(tab.testId)).toBeVisible({ timeout: 60_000 })
      await captureUiScreenshot(page, {
        id: tab.id,
        fileName: tab.file,
        title: tab.title,
      })
    })
  }

  test.afterAll(() => {
    // La carpeta queda documentada en README; manifest ya escrito por captureUiScreenshot.
    void e2eTenant
    void uiScreenshotDir
  })
})