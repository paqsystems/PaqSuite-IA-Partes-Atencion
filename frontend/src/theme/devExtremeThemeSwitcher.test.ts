import { describe, expect, it } from 'vitest'
import { EMPRESA_THEME_DEFAULT } from '../features/admin/security/empresaThemeCatalog'
import { EMPRESA_THEME_CSS_URLS } from './empresaThemeCssUrls'
import { resolveEmpresaThemeKey } from './devExtremeThemeSwitcher'

describe('resolveEmpresaThemeKey', () => {
  it('acepta clave empaquetada', () => {
    expect(resolveEmpresaThemeKey('material.teal.dark')).toBe('material.teal.dark')
    expect(Object.prototype.hasOwnProperty.call(EMPRESA_THEME_CSS_URLS, 'material.teal.dark')).toBe(true)
  })

  it('cae a default si es desconocida', () => {
    expect(resolveEmpresaThemeKey('no-existe')).toBe(EMPRESA_THEME_DEFAULT)
  })

  it('cubre el catálogo FE de apariencias', () => {
    expect(Object.keys(EMPRESA_THEME_CSS_URLS).length).toBe(44)
  })
})
