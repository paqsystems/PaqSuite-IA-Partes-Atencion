import { describe, expect, it } from 'vitest'
import { maestroGridLoadOptionsToQuery } from './maestroListPaging'

describe('maestroGridLoadOptionsToQuery', () => {
  it('maps filter contains to query params', () => {
    expect(
      maestroGridLoadOptionsToQuery({
        skip: 0,
        take: 20,
        filter: ['nombre', 'contains', 'ANKAS'],
      }),
    ).toEqual({ page: 1, pageSize: 20, nombre: 'ANKAS' })
  })

  it('maps usuarioCodigo and email filters to query params', () => {
    expect(
      maestroGridLoadOptionsToQuery({
        skip: 0,
        take: 20,
        filter: ['usuarioCodigo', 'contains', 'cliAcc'],
      }),
    ).toEqual({ page: 1, pageSize: 20, usuario: 'cliAcc' })

    expect(
      maestroGridLoadOptionsToQuery({
        skip: 0,
        take: 20,
        filter: ['email', 'contains', '@acme'],
      }),
    ).toEqual({ page: 1, pageSize: 20, email: '@acme' })
  })

  it('maps skip/take to page and pageSize', () => {
    expect(maestroGridLoadOptionsToQuery({ skip: 0, take: 20 })).toEqual({ page: 1, pageSize: 20 })
    expect(maestroGridLoadOptionsToQuery({ skip: 20, take: 20 })).toEqual({ page: 2, pageSize: 20 })
    expect(maestroGridLoadOptionsToQuery({ skip: 40, take: 40 })).toEqual({ page: 2, pageSize: 40 })
  })
})
