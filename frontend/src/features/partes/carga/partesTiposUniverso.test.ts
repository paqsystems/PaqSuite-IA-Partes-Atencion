import { describe, expect, it } from 'vitest'
import {
  normalizeTiposTareaCatalogItems,
  pickDefaultTipoTareaId,
} from './partesTiposUniverso'

describe('partesTiposUniverso', () => {
  it('normaliza id string y flags', () => {
    const items = normalizeTiposTareaCatalogItems([
      { id: '1', code: 'GEN', descripcion: 'General', isDefault: true, isGenerico: true },
    ])
    expect(items[0]?.id).toBe(1)
    expect(items[0]?.isDefault).toBe(true)
  })

  it('elige default o el primero', () => {
    const items = normalizeTiposTareaCatalogItems([
      { id: '2', code: 'A', isDefault: false },
      { id: '1', code: 'GEN', isDefault: true },
    ])
    expect(pickDefaultTipoTareaId(items)).toBe(1)
    expect(pickDefaultTipoTareaId(normalizeTiposTareaCatalogItems([{ id: '9', code: 'X' }]))).toBe(9)
  })
})
