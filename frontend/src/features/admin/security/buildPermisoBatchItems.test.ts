import { describe, expect, it } from 'vitest'
import { buildPermisoBatchItems } from './buildPermisoBatchItems'

describe('buildPermisoBatchItems', () => {
  it('cartesiano byUser con empresa mono', () => {
    expect(
      buildPermisoBatchItems('byUser', 10, { rolIds: [1, 2] }, 5)
    ).toEqual([
      { userId: 10, empresaId: 5, rolId: 1 },
      { userId: 10, empresaId: 5, rolId: 2 },
    ])
  })

  it('cartesiano byRole multi empresa', () => {
    expect(
      buildPermisoBatchItems('byRole', 7, { userIds: [1], empresaIds: [2, 3] })
    ).toEqual([
      { userId: 1, empresaId: 2, rolId: 7 },
      { userId: 1, empresaId: 3, rolId: 7 },
    ])
  })

  it('cartesiano byCompany', () => {
    expect(
      buildPermisoBatchItems('byCompany', 9, { userIds: [1, 2], rolIds: [3] })
    ).toEqual([
      { userId: 1, empresaId: 9, rolId: 3 },
      { userId: 2, empresaId: 9, rolId: 3 },
    ])
  })

  it('vacío si falta selección', () => {
    expect(buildPermisoBatchItems('byUser', 10, { rolIds: [] }, 5)).toEqual([])
  })
})
