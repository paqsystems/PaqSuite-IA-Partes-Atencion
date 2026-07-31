import { describe, expect, it } from 'vitest'
import { currentMonthValue, monthRange } from './PartesDashboardPage'

describe('partesDashboardPeriod', () => {
  it('currentMonthValue formatea YYYY-MM', () => {
    expect(currentMonthValue(new Date(2026, 6, 15))).toBe('2026-07')
    expect(currentMonthValue(new Date(2026, 0, 1))).toBe('2026-01')
  })

  it('monthRange cubre primer y último día del mes', () => {
    expect(monthRange('2026-07')).toEqual({
      fechaDesde: '2026-07-01',
      fechaHasta: '2026-07-31',
    })
    expect(monthRange('2026-02')).toEqual({
      fechaDesde: '2026-02-01',
      fechaHasta: '2026-02-28',
    })
    expect(monthRange('2024-02')).toEqual({
      fechaDesde: '2024-02-01',
      fechaHasta: '2024-02-29',
    })
  })
})
