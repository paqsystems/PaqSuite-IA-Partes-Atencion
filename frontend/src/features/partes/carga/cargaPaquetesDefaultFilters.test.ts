import { describe, expect, it } from 'vitest'
import { defaultPaqueteFiltroMes } from './cargaPaquetesDefaultFilters'

describe('defaultPaqueteFiltroMes', () => {
  it('devuelve primer y último día del mes calendario local', () => {
    const { fechaDesde, fechaHasta } = defaultPaqueteFiltroMes()
    const now = new Date()
    const pad = (n: number) => String(n).padStart(2, '0')
    const first = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-01`
    const lastDate = new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate()
    const last = `${now.getFullYear()}-${pad(now.getMonth() + 1)}-${pad(lastDate)}`
    expect(fechaDesde).toBe(first)
    expect(fechaHasta).toBe(last)
  })
})
