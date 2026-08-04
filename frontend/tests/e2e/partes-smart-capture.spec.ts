import { expect, test, type Page, type Route } from '@playwright/test'

type ScTurnResult = {
  replyText: string
  actions: Array<{ action: string; payload: Record<string, unknown>; resultado: string }>
  pendingChoice: {
    kind: string
    options: Array<{ id: string | number; label: string }>
    deferred: Array<{ cause: string; payload: Record<string, unknown> }>
  } | null
  configurationRequired: boolean
}

async function loginAsAdmin(page: Page): Promise<void> {
  await page.goto('/login')
  await page.getByTestId('loginUsuario').locator('input').fill('admin')
  await page.getByTestId('loginPassword').locator('input').fill('Paqsystems')
  await page.getByTestId('loginSubmit').click()
  await expect(page).toHaveURL(/\/partes\/?$/, { timeout: 45_000 })
}

const mockCredentialId = 9001

/**
 * Mock BYOK en el browser: listado LLM + preferencia activa.
 * Evita depender de CRUD real / criptografía en E2E.
 */
async function mockLlmCredentials(page: Page): Promise<void> {
  await page.route('**/api/v1/llm-credentials', async (route) => {
    if (route.request().method() !== 'GET') {
      await route.continue()
      return
    }
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          items: [
            {
              id: mockCredentialId,
              nombre: 'E2E Mock LLM',
              proveedor: 'openai',
              modelo: 'gpt-4o-mini',
              baseUrl: null,
              enabled: true,
              supportsVision: false,
              hasSecret: true,
            },
          ],
          activeLlmCredentialId: mockCredentialId,
          providers: [],
        },
      }),
    })
  })

  await page.route('**/api/v1/user/preferences', async (route) => {
    const method = route.request().method()
    if (method === 'GET' || method === 'PATCH') {
      await route.fulfill({
        status: 200,
        contentType: 'application/json',
        body: JSON.stringify({
          error: 0,
          respuesta: 'ok',
          resultado: {
            locale: 'es',
            theme: 'light',
            openInNewTab: false,
            activeLlmCredentialId: mockCredentialId,
          },
        }),
      })
      return
    }
    await route.continue()
  })
}

async function mockPartesCatalogAndSave(page: Page): Promise<{ a: number; b: number; tipoId: number }> {
  const cat = { a: 501, b: 502, tipoId: 601 }
  await page.route('**/api/v1/partes/catalogos/clientes', async (route) => {
    if (route.request().method() !== 'GET') {
      await route.continue()
      return
    }
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          items: [
            { id: cat.a, code: 'CL_A', nombre: 'Cliente A' },
            { id: cat.b, code: 'CL_B', nombre: 'Cliente B' },
          ],
        },
      }),
    })
  })
  await page.route('**/api/v1/partes/catalogos/tipos-tarea**', async (route) => {
    if (route.request().method() !== 'GET') {
      await route.continue()
      return
    }
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          items: [
            {
              id: cat.tipoId,
              code: 'GEN',
              descripcion: 'General',
              isDefault: true,
              isGenerico: true,
            },
          ],
        },
      }),
    })
  })
  await page.route('**/api/v1/partes/tareas', async (route) => {
    if (route.request().method() !== 'POST') {
      await route.continue()
      return
    }
    const body = (route.request().postDataJSON() ?? {}) as Record<string, unknown>
    await route.fulfill({
      status: 201,
      contentType: 'application/json',
      body: JSON.stringify({
        error: 0,
        respuesta: 'ok',
        resultado: {
          item: {
            id: 99901,
            usuarioId: body.usuarioId ?? 1,
            clienteId: body.clienteId,
            tipoTareaId: body.tipoTareaId,
            fecha: body.fecha,
            duracionMinutos: body.duracionMinutos,
            observacion: body.observacion,
            sinCargo: false,
            presencial: false,
            cerrado: false,
            esTarea: true,
            rowVersion: '1',
            clienteNombre: 'Cliente A',
            tipoTareaDescripcion: 'General',
            usuarioCode: 'admin',
          },
        },
      }),
    })
  })
  return cat
}

async function openCargaCreateExpanded(page: Page): Promise<void> {
  await page.goto('/partes/carga-diaria')
  await expect(page.getByTestId('partesCargaPage')).toBeVisible({ timeout: 20_000 })
  await expect(page.getByTestId('partesCargaAdd')).toBeVisible({ timeout: 30_000 })
  await page.getByTestId('partesCargaAdd').click()
  await expect(page.getByTestId('partesCargaForm')).toBeVisible({ timeout: 10_000 })
  await expect(page.getByTestId('partesCargaSmartCapture')).toBeVisible()
  await page.getByTestId('smartCapture.expand').click()
  await expect(page.getByTestId('smartCapture.prompt')).toBeVisible({ timeout: 10_000 })
}

async function installTurnMock(
  page: Page,
  resolver: ScTurnResult | ScTurnResult[] | ((body: Record<string, unknown>) => ScTurnResult)
): Promise<void> {
  let index = 0
  await page.route('**/api/v1/partes/tareas/asistente/turn', async (route: Route) => {
    if (route.request().method() !== 'POST') {
      await route.continue()
      return
    }
    const body = (route.request().postDataJSON() ?? {}) as Record<string, unknown>
    let result: ScTurnResult
    if (typeof resolver === 'function') {
      result = resolver(body)
    } else if (Array.isArray(resolver)) {
      result = resolver[Math.min(index, resolver.length - 1)]!
      index += 1
    } else {
      result = resolver
    }
    await route.fulfill({
      status: 200,
      contentType: 'application/json',
      body: JSON.stringify({ error: 0, respuesta: 'ok', resultado: result }),
    })
  })
}

async function sendSmartCapture(page: Page, message: string): Promise<void> {
  const prompt = page.getByTestId('smartCapture.prompt').locator('textarea, .dx-texteditor-input').first()
  await prompt.fill(message)
  await page.getByTestId('smartCapture.send').click()
}

test.describe('TR-010 Smart Capture E2E (mock turno)', () => {
  test.describe.configure({ mode: 'serial' })

  test('smoke: modal muestra panel smart capture', async ({ page }) => {
    test.setTimeout(60_000)
    await loginAsAdmin(page)
    await page.goto('/partes/carga-diaria')
    await expect(page.getByTestId('partesCargaPage')).toBeVisible({ timeout: 20_000 })
    await page.getByTestId('partesCargaAdd').click()
    await expect(page.getByTestId('partesCargaForm')).toBeVisible({ timeout: 10_000 })
    await expect(page.getByTestId('partesCargaSmartCapture')).toBeVisible()
    await expect(page.getByTestId('smartCapture.panel')).toBeVisible()
  })

  test('6 setField aplica observacion y duracion al form', async ({ page }) => {
    test.setTimeout(90_000)
    await loginAsAdmin(page)
    await mockLlmCredentials(page)
    await installTurnMock(page, {
      replyText: 'partes.smartCapture.ok',
      actions: [
        {
          action: 'setField',
          payload: { field: 'observacion', value: 'obs-sc-e2e' },
          resultado: 'ok',
        },
        {
          action: 'setField',
          payload: { field: 'duracionMinutos', value: 45 },
          resultado: 'ok',
        },
      ],
      pendingChoice: null,
      configurationRequired: false,
    })
    await openCargaCreateExpanded(page)
    await sendSmartCapture(page, 'cargar observacion y 45 minutos')
    await expect(page.getByTestId('partesCargaObservacion').locator('input')).toHaveValue(
      'obs-sc-e2e',
      { timeout: 10_000 }
    )
    await expect(page.getByTestId('partesCargaDuracion').locator('input[type="hidden"]')).toHaveValue(
      '45',
      { timeout: 10_000 }
    )
  })

  test('7 needsChoice: elegir 1 aplica campo y conserva hilo', async ({ page }) => {
    test.setTimeout(90_000)
    await loginAsAdmin(page)
    await mockLlmCredentials(page)
    await installTurnMock(page, (body) => {
      if ((body.pendingChoice as { kind?: string } | null)?.kind === 'chooseCliente') {
        return {
          replyText: 'partes.smartCapture.opcionAplicada',
          actions: [
            {
              action: 'setField',
              payload: { field: 'observacion', value: 'elegido-1' },
              resultado: 'ok',
            },
          ],
          pendingChoice: null,
          configurationRequired: false,
        }
      }
      return {
        replyText: 'Hay varios clientes posibles; elegí una opción numerada.',
        actions: [
          {
            action: 'needsChoice',
            payload: { field: 'clienteId', kind: 'chooseCliente' },
            resultado: 'ok',
          },
        ],
        pendingChoice: {
          kind: 'chooseCliente',
          options: [
            { id: 11, label: '1 — Cliente A' },
            { id: 22, label: '2 — Cliente B' },
          ],
          deferred: [{ cause: 'ambiguity', payload: { field: 'clienteId' } }],
        },
        configurationRequired: false,
      }
    })
    await openCargaCreateExpanded(page)
    await sendSmartCapture(page, 'cliente ambiguo')
    await expect(page.getByTestId('smartCapture.thread')).toContainText('varios clientes', {
      timeout: 10_000,
    })
    await sendSmartCapture(page, '1')
    await expect(page.getByTestId('partesCargaObservacion').locator('input')).toHaveValue(
      'elegido-1',
      { timeout: 10_000 }
    )
    await expect(page.getByTestId('smartCapture.thread')).toContainText('cliente ambiguo')
  })

  test('8 fecha futura: confirma con si y aplica DateBox', async ({ page }) => {
    test.setTimeout(90_000)
    const future = new Date()
    future.setDate(future.getDate() + 5)
    const futureIso = future.toISOString().slice(0, 10)

    await loginAsAdmin(page)
    await mockLlmCredentials(page)
    await installTurnMock(page, (body) => {
      if ((body.pendingChoice as { kind?: string } | null)?.kind === 'confirmFutureDate') {
        return {
          replyText: 'partes.smartCapture.fechaConfirmada',
          actions: [
            {
              action: 'setField',
              payload: { field: 'fecha', value: futureIso },
              resultado: 'ok',
            },
          ],
          pendingChoice: null,
          configurationRequired: false,
        }
      }
      return {
        replyText: 'La fecha es futura. Respondé «sí» o «confirmo» para aplicarla al formulario.',
        actions: [],
        pendingChoice: {
          kind: 'confirmFutureDate',
          options: [
            { id: 1, label: 'Sí, confirmar fecha futura' },
            { id: 2, label: 'No' },
          ],
          deferred: [{ cause: 'confirmationRequired', payload: { fecha: futureIso } }],
        },
        configurationRequired: false,
      }
    })
    await openCargaCreateExpanded(page)
    const fechaInput = page.getByTestId('partesCargaFecha').locator('input[type="hidden"]')
    const before = await fechaInput.inputValue()
    await sendSmartCapture(page, `fecha ${futureIso}`)
    await expect(page.getByTestId('smartCapture.thread')).toContainText('fecha es futura', {
      timeout: 10_000,
    })
    await expect(fechaInput).toHaveValue(before)
    await sendSmartCapture(page, 'sí')
    await expect(fechaInput).toHaveValue(futureIso, { timeout: 10_000 })
  })

  test('9 save con draft completo cierra modal', async ({ page }) => {
    test.setTimeout(120_000)
    await loginAsAdmin(page)
    await mockLlmCredentials(page)
    const cat = await mockPartesCatalogAndSave(page)
    await installTurnMock(page, {
      replyText: 'partes.smartCapture.ok',
      actions: [
        {
          action: 'setField',
          payload: { field: 'clienteId', value: cat.a },
          resultado: 'ok',
        },
        {
          action: 'setField',
          payload: { field: 'tipoTareaId', value: cat.tipoId },
          resultado: 'ok',
        },
        {
          action: 'setField',
          payload: { field: 'observacion', value: 'tarea-sc-save-e2e' },
          resultado: 'ok',
        },
        {
          action: 'setField',
          payload: { field: 'duracionMinutos', value: 30 },
          resultado: 'ok',
        },
        { action: 'save', payload: {}, resultado: 'ok' },
      ],
      pendingChoice: null,
      configurationRequired: false,
    })
    await openCargaCreateExpanded(page)
    await sendSmartCapture(page, 'guardar tarea completa')
    await expect(page.getByTestId('partesCargaForm')).toBeHidden({ timeout: 20_000 })
  })

  test('10 save incompleto no persiste y muestra error', async ({ page }) => {
    test.setTimeout(90_000)
    await loginAsAdmin(page)
    await mockLlmCredentials(page)
    await page.route('**/api/v1/partes/tareas', async (route) => {
      if (route.request().method() !== 'POST') {
        await route.continue()
        return
      }
      await route.fulfill({
        status: 422,
        contentType: 'application/json',
        body: JSON.stringify({
          error: 422,
          respuesta: 'partes.tarea.camposObligatorios',
          resultado: {},
        }),
      })
    })
    await installTurnMock(page, {
      replyText: 'partes.smartCapture.ok',
      actions: [{ action: 'save', payload: {}, resultado: 'ok' }],
      pendingChoice: null,
      configurationRequired: false,
    })
    await openCargaCreateExpanded(page)
    await sendSmartCapture(page, 'guardar ya')
    await expect(page.getByTestId('partesCargaForm')).toBeVisible({ timeout: 10_000 })
    await expect(page.getByTestId('partesCargaError')).toBeVisible({ timeout: 10_000 })
  })

  test('11 overwrite cliente via SC sin dialogo confirm', async ({ page }) => {
    test.setTimeout(90_000)
    await loginAsAdmin(page)
    await mockLlmCredentials(page)
    const cat = await mockPartesCatalogAndSave(page)
    await installTurnMock(page, [
      {
        replyText: 'partes.smartCapture.ok',
        actions: [
          {
            action: 'setField',
            payload: { field: 'clienteId', value: cat.a },
            resultado: 'ok',
          },
          {
            action: 'setField',
            payload: { field: 'observacion', value: 'antes-overwrite' },
            resultado: 'ok',
          },
        ],
        pendingChoice: null,
        configurationRequired: false,
      },
      {
        replyText: 'partes.smartCapture.ok',
        actions: [
          {
            action: 'setField',
            payload: { field: 'clienteId', value: cat.b },
            resultado: 'ok',
          },
        ],
        pendingChoice: null,
        configurationRequired: false,
      },
    ])
    await openCargaCreateExpanded(page)
    await sendSmartCapture(page, 'cliente uno')
    await expect(page.getByTestId('partesCargaObservacion').locator('input')).toHaveValue(
      'antes-overwrite',
      { timeout: 10_000 }
    )
    await sendSmartCapture(page, 'cambiar cliente')
    await expect(page.getByTestId('partesCargaObservacion').locator('input')).toHaveValue(
      'antes-overwrite'
    )
    await expect(page.locator('.dx-dialog')).toHaveCount(0)
    await expect(page.getByTestId('partesCargaForm')).toBeVisible()
  })
})
