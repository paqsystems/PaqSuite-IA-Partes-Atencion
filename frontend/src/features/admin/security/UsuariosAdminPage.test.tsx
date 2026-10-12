import { readFileSync } from 'node:fs'
import { dirname, join } from 'node:path'
import { fileURLToPath } from 'node:url'
import { describe, expect, it } from 'vitest'

const dir = dirname(fileURLToPath(import.meta.url))

describe('UsuariosAdminPage wrapper GEN-06', () => {
  it('monta el export SDK con namespace common', () => {
    const source = readFileSync(join(dir, 'UsuariosAdminPage.tsx'), 'utf8')
    expect(source).toContain('UsuariosAdminPage as UsuariosAdminPageSdk')
    expect(source).toContain('i18nNamespace="common"')
    expect(source).not.toContain('ProcessDataGrid')
    expect(source).not.toContain('createAdminUsuario')
  })
})
