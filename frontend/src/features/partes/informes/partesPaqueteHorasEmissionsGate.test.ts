import { readFileSync } from 'node:fs'
import { dirname, join } from 'node:path'
import { fileURLToPath } from 'node:url'
import { describe, expect, it } from 'vitest'

const source = readFileSync(
  join(dirname(fileURLToPath(import.meta.url)), 'PaqueteHorasPage.tsx'),
  'utf8',
)

describe('emisión Paquete de horas', () => {
  it('monta EmissionDialog y botón emitir en web', () => {
    expect(source).toContain('EmissionDialog')
    expect(source).toContain('partesPaqueteHorasEmit')
    expect(source).toContain('Selection')
  })
})
