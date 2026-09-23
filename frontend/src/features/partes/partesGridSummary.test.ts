import { describe, expect, it } from 'vitest'
import { renderHook } from '@testing-library/react'
import {
  usePartesDuracionHorasSummaryItems,
  usePartesMinutosColumnSummaryItems,
} from './partesGridSummary'

describe('partes domain grid summary items', () => {
  it('duracionHoras expone totalizador sum sobre la columna esperada', () => {
    const { result } = renderHook(() => usePartesDuracionHorasSummaryItems())
    expect(result.current[0]).toMatchObject({
      column: 'duracionHoras',
      summaryType: 'sum',
      name: 'pq-duracionHoras-sum',
    })
  })

  it('minutos usa customizeText para hh:mm', () => {
    const { result } = renderHook(() =>
      usePartesMinutosColumnSummaryItems('duracionMinutos', 'pq-duracionMinutos-sum'),
    )
    const item = result.current[0]
    expect(item.column).toBe('duracionMinutos')
    expect(item.customizeText?.({ value: 90 })).toMatch(/1:30|01:30/)
  })
})
