import { describe, expect, it } from 'vitest'
import { resolveDefaultTipoId } from './cargaDiariaTipoDefault'

describe('resolveDefaultTipoId', () => {
  it('elige el marcado isDefault y no el primer code', () => {
    const items = [
      { id: 10, code: 'AAA', isDefault: false },
      { id: 22, code: 'GEN', isDefault: true },
      { id: 30, code: 'ZZZ', isDefault: false },
    ]
    expect(resolveDefaultTipoId(items)).toBe(22)
  })

  it('acepta is_default snake_case e id string', () => {
    expect(resolveDefaultTipoId([{ id: '7', code: 'X', is_default: true }])).toBe(7)
  })

  it('no usa el primer ítem si ninguno es default', () => {
    expect(resolveDefaultTipoId([{ id: 1, code: 'A' }, { id: 2, code: 'B' }])).toBe(null)
  })
})
