import { describe, expect, it } from 'vitest'
import { defaultEmpresaTheme, empresaThemeKeys } from '@paqsuite/react-core'
import { EMPRESA_THEME_DEFAULT } from '../features/admin/security/empresaThemeCatalog'
import { resolveEmpresaThemeKey, themeGroupOf } from './devExtremeThemeSwitcher'

describe('resolveEmpresaThemeKey (SDK A1)', () => {
  it('normaliza stock DX a clave paqsuite', () => {
    expect(resolveEmpresaThemeKey('generic.darkviolet')).toBe('paqsuite.violet.generic')
    expect(resolveEmpresaThemeKey('material.purple.light')).toBe('paqsuite.violet.material')
  })

  it('acepta clave paqsuite del catálogo', () => {
    expect(resolveEmpresaThemeKey('paqsuite.burgundy.generic')).toBe('paqsuite.burgundy.generic')
  })

  it('cae al default del SDK si es desconocida', () => {
    expect(resolveEmpresaThemeKey('no-existe')).toBe(defaultEmpresaTheme)
    expect(EMPRESA_THEME_DEFAULT).toBe(defaultEmpresaTheme)
  })

  it('catálogo host alineado al SDK (24 apariencias)', () => {
    expect(empresaThemeKeys.length).toBe(24)
  })

  it('detecta cambio de grupo DX entre familias material y generic', () => {
    expect(themeGroupOf('paqsuite.light.generic')).toBe('generic')
    expect(themeGroupOf('paqsuite.light.material')).toBe('material')
    expect(themeGroupOf('paqsuite.light.generic')).not.toBe(themeGroupOf('paqsuite.light.material'))
  })
})
