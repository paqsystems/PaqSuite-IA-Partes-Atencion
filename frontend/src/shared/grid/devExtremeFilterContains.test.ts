import { describe, expect, it } from 'vitest'
import { devExtremeFilterToContains, parseDevExtremeFilter } from './devExtremeFilterContains'

describe('devExtremeFilterToContains', () => {
  it('parses simple contains filter', () => {
    expect(devExtremeFilterToContains(['nombre', 'contains', 'ANKAS'])).toEqual({
      nombre: 'ANKAS',
    })
  })

  it('parses combined and filters', () => {
    expect(
      devExtremeFilterToContains([
        ['code', 'contains', 'PG'],
        'and',
        ['nombre', 'startswith', 'Cli'],
      ]),
    ).toEqual({ code: 'PG', nombre: 'Cli' })
  })

  it('returns empty for null', () => {
    expect(devExtremeFilterToContains(null)).toEqual({})
  })
})

describe('parseDevExtremeFilter', () => {
  it('parses header filter equals and or-list', () => {
    expect(
      parseDevExtremeFilter([
        ['clienteNombre', '=', 'PaqSystems'],
        'or',
        ['clienteNombre', '=', 'Golo Tap'],
      ]),
    ).toEqual({
      contains: {},
      equals: { clienteNombre: ['PaqSystems', 'Golo Tap'] },
    })
  })

  it('parses anyof unique values', () => {
    expect(parseDevExtremeFilter(['usuarioCode', 'anyof', ['PQ', 'ADM']])).toEqual({
      contains: {},
      equals: { usuarioCode: ['PQ', 'ADM'] },
    })
  })
})
