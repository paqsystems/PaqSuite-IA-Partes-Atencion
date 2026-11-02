import { describe, expect, it } from 'vitest'
import { maestroGridLoadOptionsToQuery } from './maestroListPaging'

describe('maestroGridLoadOptionsToQuery', () => {
  it('maps skip/take to page and pageSize', () => {
    expect(maestroGridLoadOptionsToQuery({ skip: 0, take: 20 })).toEqual({ page: 1, pageSize: 20 })
    expect(maestroGridLoadOptionsToQuery({ skip: 20, take: 20 })).toEqual({ page: 2, pageSize: 20 })
    expect(maestroGridLoadOptionsToQuery({ skip: 40, take: 40 })).toEqual({ page: 2, pageSize: 40 })
  })
})
