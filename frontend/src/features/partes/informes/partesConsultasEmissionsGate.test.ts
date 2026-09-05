import { readFileSync } from 'node:fs'
import { dirname, join } from 'node:path'
import { fileURLToPath } from 'node:url'
import { describe, expect, it } from 'vitest'

const source = readFileSync(
  join(dirname(fileURLToPath(import.meta.url)), 'PartesConsultasPages.tsx'),
  'utf8',
)
const agrupadasStart = source.indexOf('export function ConsultasAgrupadasPage')
const detallada = source.slice(0, agrupadasStart)
const agrupadas = source.slice(agrupadasStart)

describe('emisión solo en Consulta detallada', () => {
  it('Consulta detallada monta EmissionDialog y agrupadas no', () => {
    expect(detallada).toContain('EmissionDialog')
    expect(detallada).toContain('partesConsultaDetalladaEmit')
    expect(agrupadas).not.toContain('EmissionDialog')
    expect(agrupadas).not.toContain('partesConsultaDetalladaEmit')
  })

  it('el diálogo Emitir del host no usa fieldRender (DX E1010)', () => {
    const dialogSource = readFileSync(
      join(dirname(fileURLToPath(import.meta.url)), 'PartesEmissionDialog.tsx'),
      'utf8',
    )
    expect(dialogSource).not.toMatch(/fieldRender\s*=/)
    expect(dialogSource).toContain("displayExpr=\"label\"")
    expect(dialogSource).toContain('modeItems.length > 1')
  })
})
