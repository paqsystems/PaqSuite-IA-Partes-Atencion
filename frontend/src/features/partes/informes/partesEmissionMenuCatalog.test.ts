import { describe, expect, it } from 'vitest'
import { partesEmissionMenuEntries } from './partesEmissionMenuCatalog'

describe('partesEmissionMenuCatalog', () => {
  it('lists each host process with emit test ids', () => {
    expect(partesEmissionMenuEntries.length).toBeGreaterThanOrEqual(2)
    const codes = partesEmissionMenuEntries.map((e) => e.processCode)
    expect(codes).toContain('partes.informes.consultaDetallada')
    expect(codes).toContain('partes.informes.paqueteHoras')
    partesEmissionMenuEntries.forEach((entry) => {
      expect(entry.routePath.startsWith('/partes/')).toBe(true)
      expect(entry.pageTestId.length).toBeGreaterThan(0)
      expect(entry.emitTestId.length).toBeGreaterThan(0)
    })
  })
})
