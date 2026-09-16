import { readFileSync } from 'node:fs'
import { dirname, join } from 'node:path'
import { fileURLToPath } from 'node:url'
import { describe, expect, it } from 'vitest'

describe('CargaDiariaPage toolbar Excel', () => {
  it('no remonta exportEnabled según filas o loading', () => {
    const source = readFileSync(
      join(dirname(fileURLToPath(import.meta.url)), 'CargaDiariaPage.tsx'),
      'utf8'
    )
    expect(source).not.toMatch(/exportEnabled=\{rows\.length/)
    expect(source).not.toMatch(/exportEnabled=\{[^}]*loading/)
  })
})
