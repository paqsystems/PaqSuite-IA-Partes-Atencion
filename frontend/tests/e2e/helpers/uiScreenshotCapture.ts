import { expect, type Locator, type Page } from '@playwright/test'
import fs from 'node:fs'
import path from 'node:path'
import { fileURLToPath } from 'node:url'

const repoRoot = path.resolve(path.dirname(fileURLToPath(import.meta.url)), '../../../..')

export const e2eUser = process.env.PW_LOGIN_USER ?? 'admin'
export const e2ePassword = process.env.PW_LOGIN_PASSWORD ?? 'Paqsystems'
export const e2eTenant = process.env.PW_TENANT ?? process.env.VITE_TENANT_DEFAULT_CLIENT ?? 'DEMO'
export const compactThemeKey = process.env.PW_UI_COMPACT_THEME ?? 'paqsuite.dark.compact'

/** Tiempo máximo esperando que desaparezcan spinners / “Cargando…” (ms). */
export const uiCaptureReadyTimeoutMs = Number(process.env.PW_UI_CAPTURE_READY_TIMEOUT_MS ?? 90_000)

/**
 * Pausa extra tras estabilizar la UI (ms). Para capturas muy lentas usar `60000`.
 * Default 3s tras quitar indicadores de carga.
 */
export const uiCaptureSettleMs = Number(process.env.PW_UI_CAPTURE_SETTLE_MS ?? 3_000)

export const uiScreenshotDir = path.resolve(
  process.env.PW_UI_SCREENSHOT_DIR ?? path.join(repoRoot, 'docs', 'ui-redesign', 'screenshot-catalog'),
)

export const uiScreenshotAuthStatePath = path.join(uiScreenshotDir, '.auth-state.json')
export const uiScreenshotManifestPath = path.join(uiScreenshotDir, 'manifest.json')

export type ScreenshotManifestEntry = {
  id: string
  file: string
  title: string
  capturedAt: string
  url: string
  status: 'ok' | 'skipped'
  note?: string
}

export type ScreenshotManifest = {
  generatedAt: string
  tenant: string
  user: string
  baseUrl: string
  viewport: { width: number; height: number }
  entries: ScreenshotManifestEntry[]
}

let manifest: ScreenshotManifest | null = null

export function initScreenshotManifest(viewport: { width: number; height: number }, baseUrl: string): void {
  fs.mkdirSync(uiScreenshotDir, { recursive: true })
  if (fs.existsSync(uiScreenshotManifestPath)) {
    try {
      manifest = JSON.parse(fs.readFileSync(uiScreenshotManifestPath, 'utf8')) as ScreenshotManifest
      manifest.generatedAt = new Date().toISOString()
      manifest.baseUrl = baseUrl
      manifest.viewport = viewport
      return
    } catch {
      // recrear si el JSON está corrupto
    }
  }
  manifest = {
    generatedAt: new Date().toISOString(),
    tenant: e2eTenant,
    user: e2eUser,
    baseUrl,
    viewport,
    entries: [],
  }
}

export function withTenantQuery(pathname: string): string {
  const url = new URL(pathname, 'http://localhost')
  url.searchParams.set('cliente', e2eTenant)
  const vercelShare = (process.env.PW_VERCEL_SHARE ?? '').trim()
  if (vercelShare) {
    url.searchParams.set('_vercel_share', vercelShare)
  }
  return `${url.pathname}${url.search}`
}

async function countVisibleLoadPanels(page: Page): Promise<number> {
  return page.locator('.dx-loadpanel-wrapper, .dx-overlay-wrapper .dx-loadpanel').evaluateAll((nodes) => {
    const isVisible = (el: Element) => {
      const rect = el.getBoundingClientRect()
      if (rect.width < 2 && rect.height < 2) return false
      const style = window.getComputedStyle(el)
      if (style.visibility === 'hidden' || style.display === 'none') return false
      if (Number(style.opacity) < 0.05) return false
      return true
    }
    return nodes.filter((el) => isVisible(el)).length
  })
}

async function countVisibleDxLoadIndicators(page: Page): Promise<number> {
  return page.locator('.dx-loadindicator, .dx-loadindicator-icon').evaluateAll((nodes) => {
    const isVisible = (el: Element) => {
      const rect = el.getBoundingClientRect()
      if (rect.width < 2 && rect.height < 2) return false
      const style = window.getComputedStyle(el)
      if (style.visibility === 'hidden' || style.display === 'none') return false
      if (Number(style.opacity) < 0.05) return false
      return true
    }
    return nodes.filter((el) => isVisible(el)).length
  })
}

async function countVisibleLoadingLabels(page: Page): Promise<number> {
  return page.evaluate(() => {
    const isVisible = (el: Element) => {
      const rect = el.getBoundingClientRect()
      if (rect.width < 2 && rect.height < 2) return false
      const style = window.getComputedStyle(el)
      if (style.visibility === 'hidden' || style.display === 'none') return false
      if (Number(style.opacity) < 0.05) return false
      return true
    }
    const pattern = /^(Cargando|Loading)(\.\.\.|…)?$/i
    let count = 0
    document.querySelectorAll('span, div, p, label, td').forEach((el) => {
      if (el.children.length > 0) return
      const text = (el.textContent ?? '').trim()
      if (!pattern.test(text)) return
      if (isVisible(el)) count += 1
    })
    return count
  })
}

async function countBusyDataGrids(page: Page): Promise<number> {
  return page.evaluate(() => {
    const isVisible = (el: Element) => {
      const rect = el.getBoundingClientRect()
      if (rect.width < 2 && rect.height < 2) return false
      const style = window.getComputedStyle(el)
      if (style.visibility === 'hidden' || style.display === 'none') return false
      if (Number(style.opacity) < 0.05) return false
      return true
    }
    let busy = 0
    document.querySelectorAll('.dx-datagrid').forEach((grid) => {
      if (!isVisible(grid)) return
      const rowsView = grid.querySelector('.dx-datagrid-rowsview')
      if (!rowsView) return
      const hasRowSpinner = Array.from(rowsView.querySelectorAll('.dx-loadindicator')).some((el) => isVisible(el))
      const hasLoadPanel = Array.from(grid.querySelectorAll('.dx-loadpanel-wrapper')).some((el) => isVisible(el))
      if (hasRowSpinner || hasLoadPanel) busy += 1
    })
    return busy
  })
}

async function menuTreeNodeCount(page: Page): Promise<number> {
  return page.locator('.dx-treeview-node').count()
}

/** Espera hasta que menú, grillas y overlays DX dejen de cargar; luego pausa `PW_UI_CAPTURE_SETTLE_MS`. */
export async function waitForDxReady(page: Page): Promise<void> {
  const timeout = uiCaptureReadyTimeoutMs

  await page.waitForLoadState('domcontentloaded').catch(() => undefined)
  await page.waitForLoadState('networkidle', { timeout: Math.min(timeout, 45_000) }).catch(() => undefined)

  const pollOptions = {
    timeout,
    intervals: [400, 800, 1_500, 2_500] as number[],
  }

  await expect
    .poll(async () => countVisibleLoadPanels(page), { ...pollOptions, message: 'LoadPanel DevExtreme visible' })
    .toBe(0)

  await expect
    .poll(async () => countVisibleLoadingLabels(page), { ...pollOptions, message: 'texto Cargando/Loading visible' })
    .toBe(0)

  await expect
    .poll(async () => countBusyDataGrids(page), { ...pollOptions, message: 'grilla con spinner en rowsview' })
    .toBe(0)

  const pathname = new URL(page.url()).pathname
  // Solo shell principal /partes y /admin (no /archivos/partes/… donde el menú a veces tarda o no usa treeview igual).
  if (/^\/(partes|admin)(\/|$)/.test(pathname)) {
    await expect
      .poll(async () => menuTreeNodeCount(page), {
        ...pollOptions,
        message: 'árbol de menú lateral con nodos',
      })
      .toBeGreaterThan(2)
  }

  const straySpinners = await countVisibleDxLoadIndicators(page)
  if (straySpinners > 0) {
    await expect
      .poll(async () => countVisibleDxLoadIndicators(page), {
        ...pollOptions,
        message: 'dx-loadindicator sueltos',
      })
      .toBe(0)
  }

  if (uiCaptureSettleMs > 0) {
    await page.waitForTimeout(uiCaptureSettleMs)
  }
}

export async function loginPartesUi(page: Page): Promise<void> {
  let lastError: unknown
  for (let attempt = 1; attempt <= 3; attempt++) {
    await page.goto(withTenantQuery('/login'))
    await page.getByTestId('loginUsuario').locator('input').fill(e2eUser)
    await page.getByTestId('loginPassword').locator('input').fill(e2ePassword)
    await page.getByTestId('loginSubmit').click()
    try {
      await expect(page).toHaveURL(/\/partes\/?(?:\?|$)/, { timeout: 45_000 })
      await expect(page.getByTestId('partesDashboardRoot')).toBeVisible({ timeout: 45_000 })
      return
    } catch (error) {
      lastError = error
    }
  }
  throw lastError
}

export async function openUserAvatarMenu(page: Page): Promise<void> {
  await page.getByTestId('userMenu.trigger').click()
  await expect(page.locator('.dx-popup-content, .dx-context-menu, .dx-menu').first()).toBeVisible({
    timeout: 10_000,
  })
}

export async function clickGridFirstEdit(page: Page, gridTestId: string): Promise<boolean> {
  const grid = page.getByTestId(gridTestId)
  await expect(grid).toBeVisible({ timeout: 45_000 })
  await waitForDxReady(page)
  const editLink = grid.locator('.dx-datagrid-rowsview .dx-link-edit').first()
  const editIcon = grid.locator('.dx-datagrid-rowsview .dx-icon-edit').first()
  if ((await editLink.count()) > 0) {
    await editLink.click()
    return true
  }
  if ((await editIcon.count()) > 0) {
    await editIcon.click()
    return true
  }
  return false
}

export async function clickFirstDataRow(page: Page, gridTestId: string): Promise<boolean> {
  const row = page.getByTestId(gridTestId).locator('.dx-datagrid-rowsview .dx-data-row').first()
  if ((await row.count()) === 0) {
    return false
  }
  await row.click()
  return true
}

async function writeManifest(): Promise<void> {
  if (!manifest) {
    return
  }
  fs.writeFileSync(uiScreenshotManifestPath, `${JSON.stringify(manifest, null, 2)}\n`, 'utf8')
}

export async function captureUiScreenshot(
  page: Page,
  input: {
    id: string
    fileName: string
    title: string
    locator?: Locator
    fullPage?: boolean
  },
): Promise<void> {
  if (!manifest) {
    throw new Error('initScreenshotManifest debe llamarse antes de capturar.')
  }

  const targetPath = path.join(uiScreenshotDir, input.fileName)
  const url = page.url()
  let entry: ScreenshotManifestEntry

  try {
    await waitForDxReady(page)
    if (input.locator) {
      await expect(input.locator).toBeVisible({ timeout: 45_000 })
      await input.locator.screenshot({ path: targetPath })
    } else {
      await page.screenshot({ path: targetPath, fullPage: input.fullPage ?? true })
    }
    entry = {
      id: input.id,
      file: input.fileName,
      title: input.title,
      capturedAt: new Date().toISOString(),
      url,
      status: 'ok',
    }
  } catch (error) {
    const note = error instanceof Error ? error.message : String(error)
    entry = {
      id: input.id,
      file: input.fileName,
      title: input.title,
      capturedAt: new Date().toISOString(),
      url,
      status: 'skipped',
      note,
    }
  }

  const existingIndex = manifest.entries.findIndex((item) => item.id === input.id)
  if (existingIndex >= 0) {
    manifest.entries[existingIndex] = entry
  } else {
    manifest.entries.push(entry)
  }

  await writeManifest()
}

export async function applyCompactAppearanceViaEmpresas(page: Page): Promise<void> {
  await page.goto(withTenantQuery('/admin/empresas'))
  await expect(page.getByTestId('adminEmpresasPage')).toBeVisible({ timeout: 45_000 })
  await expect(page.getByTestId('admin.empresas.grid')).toBeVisible({ timeout: 30_000 })
  const edited = await clickGridFirstEdit(page, 'admin.empresas.grid')
  if (!edited) {
    return
  }
  const theme = page.getByTestId('adminEmpresasFormTheme')
  if ((await theme.count()) > 0) {
    await theme.click()
    const option = page.getByRole('option', { name: /compact/i }).first()
    if ((await option.count()) > 0) {
      await option.click()
    } else {
      await page.locator('.dx-overlay-content .dx-list-item').filter({ hasText: /compact/i }).first().click()
    }
  }
  const save = page.getByTestId('admin.empresas.save').or(page.getByRole('button', { name: /^Guardar$|^Save$/i }))
  if ((await save.count()) > 0) {
    await save.first().click()
    await waitForDxReady(page)
  }
  await page.reload({ waitUntil: 'domcontentloaded' })
  await waitForDxReady(page)
}

export async function switchConsultaDetalladaPivot(page: Page): Promise<boolean> {
  const pivotButton = page
    .getByRole('button', { name: /^Pivot$/i })
    .or(page.getByRole('tab', { name: /^Pivot$/i }))
    .or(page.locator('[data-testid*="pivot" i]'))
    .first()
  if ((await pivotButton.count()) === 0) {
    return false
  }
  await pivotButton.click()
  await waitForDxReady(page)
  return true
}

export async function openEmissionDesignerReportStep(page: Page): Promise<boolean> {
  const confirm = page.getByTestId('emission.design.confirmProcess')
  if (!(await confirm.isVisible().catch(() => false))) {
    return false
  }
  const process = page.getByTestId('emission.design.process')
  await process.click()
  const option = page.getByRole('option', { name: /paquete de horas|consulta detallada/i }).first()
  if (!(await option.isVisible().catch(() => false))) {
    await page.keyboard.press('Escape')
    return false
  }
  await option.click()
  await confirm.click()
  await expect(page.getByTestId('emission.design.report')).toBeVisible({ timeout: 45_000 })
  return true
}