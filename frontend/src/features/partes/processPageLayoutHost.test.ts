import { readFileSync } from 'node:fs'
import { dirname, join } from 'node:path'
import { fileURLToPath } from 'node:url'
import { describe, expect, it } from 'vitest'

const dir = dirname(fileURLToPath(import.meta.url))

const processPages = [
  'carga/CargaDiariaPage.tsx',
  'masivo/ProcesoMasivoPage.tsx',
  'informes/PartesDashboardPage.tsx',
  'informes/PaqueteHorasPage.tsx',
  'informes/PartesConsultasPages.tsx',
  'informes/ReportDesignerHostPage.tsx',
  'maestros/ClienteTiposTareaPage.tsx',
  'maestros/MaestroCrudPage.tsx',
  'mobile/ConsultaKardexMobilePage.tsx',
]

describe('PA-13 ProcessPageLayout host', () => {
  it.each(processPages)('%s usa ProcessPageLayout y no título h2 de proceso', (relative) => {
    const source = readFileSync(join(dir, relative), 'utf8')
    expect(source).toContain('ProcessPageLayout')
    expect(source).not.toMatch(/<h2 className="pqProcessTitle"/)
  })
})
