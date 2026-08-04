import { describe, expect, it } from 'vitest'
import { shouldRefreshCargaAfterImport } from './excelImportCargaHelpers'

describe('excelImportCargaHelpers', () => {
  it('refresca solo done/partial con processedRows>0', () => {
    expect(
      shouldRefreshCargaAfterImport({ status: 'done', processedRows: 2 })
    ).toBe(true)
    expect(
      shouldRefreshCargaAfterImport({ status: 'partial', processedRows: 1 })
    ).toBe(true)
    expect(
      shouldRefreshCargaAfterImport({ status: 'queued', processedRows: 0 })
    ).toBe(false)
    expect(
      shouldRefreshCargaAfterImport({ status: 'failed', processedRows: 0 })
    ).toBe(false)
    expect(
      shouldRefreshCargaAfterImport({ status: 'done', processedRows: 0 })
    ).toBe(false)
  })
})
