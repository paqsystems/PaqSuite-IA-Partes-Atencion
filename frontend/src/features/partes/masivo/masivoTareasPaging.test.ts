import { describe, expect, it } from 'vitest'
import { coerceHeaderFilterKey, masivoGridLoadOptionsToQuery } from './masivoTareasPaging'

describe('masivoGridLoadOptionsToQuery', () => {
  it('maps first page from skip 0', () => {
    expect(masivoGridLoadOptionsToQuery({ skip: 0, take: 20 })).toEqual({
      page: 1,
      pageSize: 20,
    })
  })

  it('maps page 2 with take 20', () => {
    expect(masivoGridLoadOptionsToQuery({ skip: 20, take: 20 })).toEqual({
      page: 2,
      pageSize: 20,
    })
  })

  it('maps page 3 with take 40', () => {
    expect(masivoGridLoadOptionsToQuery({ skip: 80, take: 40 })).toEqual({
      page: 3,
      pageSize: 40,
    })
  })

  it('defaults take to 20 when missing', () => {
    expect(masivoGridLoadOptionsToQuery({ skip: 40 })).toEqual({
      page: 3,
      pageSize: 20,
    })
  })

  it('maps filter contains on clienteNombre', () => {
    expect(
      masivoGridLoadOptionsToQuery({
        skip: 0,
        take: 20,
        filter: ['clienteNombre', 'contains', 'ANKAS'],
      }),
    ).toEqual({ page: 1, pageSize: 20, clienteNombre: 'ANKAS' })
  })

  it('maps remote sort on clienteNombre', () => {
    expect(
      masivoGridLoadOptionsToQuery({
        skip: 0,
        take: 20,
        sort: [{ selector: 'clienteNombre', desc: false }],
      }),
    ).toEqual({ page: 1, pageSize: 20, sortField: 'clienteNombre', sortOrder: 'asc' })
  })

  it('maps header filter group to distinctField', () => {
    expect(
      masivoGridLoadOptionsToQuery({
        skip: 0,
        take: 20,
        group: [{ selector: 'clienteNombre', isExpanded: false }],
      }),
    ).toEqual({ page: 1, pageSize: 20, distinctField: 'clienteNombre' })
  })

  it('maps header filter equals to eq', () => {
    expect(
      masivoGridLoadOptionsToQuery({
        skip: 0,
        take: 20,
        filter: ['clienteNombre', '=', 'PaqSystems'],
      }),
    ).toEqual({
      page: 1,
      pageSize: 20,
      eq: { clienteNombre: ['PaqSystems'] },
    })
  })
})

describe('coerceHeaderFilterKey', () => {
  it('normalizes empty and boolean values', () => {
    expect(coerceHeaderFilterKey('clienteNombre', '')).toBeNull()
    expect(coerceHeaderFilterKey('sinCargo', 1)).toBe(true)
    expect(coerceHeaderFilterKey('fecha', '2026-11-07 00:00:00')).toBe('2026-11-07')
  })
})
