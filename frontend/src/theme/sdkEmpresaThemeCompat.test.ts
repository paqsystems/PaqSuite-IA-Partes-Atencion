import { describe, expect, it } from 'vitest'
import {
  mapThemeForApiPersistence,
  normalizeThemeFromApi,
  paqsuiteDxStockBridge,
} from './sdkEmpresaThemeCompat'

describe('sdkEmpresaThemeCompat', () => {
  it('resuelve stock DX a clave paqsuite', () => {
    expect(normalizeThemeFromApi('generic.darkviolet')).toBe('paqsuite.violet.generic')
    expect(paqsuiteDxStockBridge['paqsuite.violet.generic']).toBe('generic.darkviolet')
  })

  it('persiste clave paqsuite como stock DX', () => {
    expect(mapThemeForApiPersistence('paqsuite.orange.material')).toBe('material.orange.light')
  })
})
