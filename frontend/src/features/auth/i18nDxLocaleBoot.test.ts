import { readFileSync } from 'node:fs'
import { dirname, join } from 'node:path'
import { fileURLToPath } from 'node:url'
import { describe, expect, it } from 'vitest'

const dir = dirname(fileURLToPath(import.meta.url))
const srcRoot = join(dir, '../..')

describe('PA-11 DX locale boot', () => {
  it('no fuerza syncDevExtremeLocale(\'es\') en main.tsx', () => {
    const source = readFileSync(join(srcRoot, 'main.tsx'), 'utf8')
    expect(source).not.toMatch(/syncDevExtremeLocale\(\s*['"]es['"]\s*\)/)
  })

  it('i18n.ts sincroniza DX con getGuestLocale, no con literal es', () => {
    const source = readFileSync(join(srcRoot, 'i18n/i18n.ts'), 'utf8')
    expect(source).toContain('getGuestLocale')
    expect(source).toContain('syncDevExtremeLocale(initialLocale)')
    expect(source).not.toMatch(/syncDevExtremeLocale\(\s*['"]es['"]\s*\)/)
  })
})
