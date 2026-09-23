import { describe, expect, it } from 'vitest'
import { renderHook } from '@testing-library/react'
import { usePartesGridSummaryTypeLabels } from './partesGridSummary'

describe('usePartesGridSummaryTypeLabels', () => {
  it('expone los cinco tipos de totalizador', () => {
    const { result } = renderHook(() => usePartesGridSummaryTypeLabels())
    expect(result.current).toMatchObject({
      count: expect.any(String),
      sum: expect.any(String),
      min: expect.any(String),
      max: expect.any(String),
      avg: expect.any(String),
    })
  })
})
