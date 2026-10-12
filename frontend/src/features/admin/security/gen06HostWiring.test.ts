import { readFileSync } from 'node:fs'
import { dirname, join } from 'node:path'
import { fileURLToPath } from 'node:url'
import { describe, expect, it } from 'vitest'

const dir = dirname(fileURLToPath(import.meta.url))
const srcRoot = join(dir, '../../..')

describe('GEN-06 host wiring PA-05 / PA-06', () => {
  it('EmpresasAdminPage pasa i18nNamespace common y preview host sin reload (PA-05/08)', () => {
    const source = readFileSync(join(dir, 'EmpresasAdminPage.tsx'), 'utf8')
    expect(source).toContain('i18nNamespace="common"')
    expect(source).toContain('applyDevExtremeTheme')
    expect(source).toContain('reloadOnGroupChange: false')
    expect(source).not.toMatch(/\bt=\{/)
    expect(source).not.toContain('useEmpresaAppearanceBridge')
  })

  it('RolesAdminPage usa t de i18n (claves SDK) sin mapper host', () => {
    const source = readFileSync(join(dir, 'RolesAdminPage.tsx'), 'utf8')
    expect(source).toContain('SecurityRolesPage')
    expect(source).not.toMatch(/securityRolesSdkI18n|admin\.roles\./)
  })
})

describe('GEN-06 PA-03 / PA-04 / PA-14', () => {
  it('el pin exporta UsuariosAdminPage, PermisosAdminPage y sus register i18n', () => {
    const index = readFileSync(
      join(srcRoot, '../node_modules/@paqsuite/react-core/src/index.ts'),
      'utf8',
    )
    expect(index).toContain('UsuariosAdminPage')
    expect(index).toContain('PermisosAdminPage')
    expect(index).toContain('registerUsuariosAdminI18nResources')
    expect(index).toContain('registerPermisosAdminI18nResources')
  })

  it('i18n.ts registra usuarios y permisos SDK en namespace common (PA-14)', () => {
    const source = readFileSync(join(srcRoot, 'i18n/i18n.ts'), 'utf8')
    expect(source).toContain("registerUsuariosAdminI18nResources(i18n, 'common')")
    expect(source).toContain("registerPermisosAdminI18nResources(i18n, 'common')")
  })

  it('PermisosAdminPage es wrapper SDK MONO', () => {
    const source = readFileSync(join(dir, 'PermisosAdminPage.tsx'), 'utf8')
    expect(source).toContain('PermisosAdminPage as PermisosAdminPageSdk')
    expect(source).toContain('i18nNamespace="common"')
    expect(source).toContain('tenancyMode="single"')
    expect(source).not.toContain('ProcessDataGrid')
    expect(source).not.toContain('buildPermisoBatchItems')
  })
})
