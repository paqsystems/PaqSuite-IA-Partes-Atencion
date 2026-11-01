import { describe, expect, it } from 'vitest'
import { masivoGridLoadOptionsToQuery } from './masivoTareasPaging'

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
})
