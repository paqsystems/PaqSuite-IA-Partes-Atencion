import { readFileSync } from 'node:fs'
import { dirname, join } from 'node:path'
import { fileURLToPath } from 'node:url'
import { describe, expect, it } from 'vitest'

const dir = dirname(fileURLToPath(import.meta.url))
const srcRoot = join(dir, '../..')

describe('GEN-19 host wiring PA-09 / PA-10', () => {
  it('platformContext termina en bootstrapClienteFromWindow y usa buildPlatformHeaders', () => {
    const source = readFileSync(join(dir, 'platformContext.ts'), 'utf8')
    expect(source).toContain('bootstrapClienteFromWindow')
    expect(source).toContain('buildPlatformHeaders')
  })

  it('main.tsx persiste cliente antes de createRoot', () => {
    const source = readFileSync(join(srcRoot, 'main.tsx'), 'utf8')
    const bootstrapIdx = source.indexOf('bootstrapPlatformCliente()')
    const rootIdx = source.indexOf('createRoot(document')
    expect(bootstrapIdx).toBeGreaterThan(-1)
    expect(rootIdx).toBeGreaterThan(bootstrapIdx)
  })

  it('ShellPage reaplica tema solo si cambia la clave de apariencia', () => {
    const source = readFileSync(join(dir, 'ShellPage.tsx'), 'utf8')
    expect(source).toContain('committedThemeKey')
    expect(source).not.toContain('[session?.activeCompanyId, session?.empresas]')
  })

  it('ShellPage abre procesos con openShellProcess y no delega openInNewTab al SDK', () => {
    const source = readFileSync(join(dir, 'ShellPage.tsx'), 'utf8')
    expect(source).toContain('openShellProcess')
    expect(source).toContain('openInNewTab={false}')
    expect(source).toContain('readOpenInNewTabPreference')
  })
})
