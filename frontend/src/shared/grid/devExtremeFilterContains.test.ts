import { describe, expect, it } from 'vitest'
import { devExtremeFilterToContains } from './devExtremeFilterContains'

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
