import { describe, expect, it } from 'vitest'
import {
  buildTramoOptions,
  isFechaFutura,
  isValidDuracionMinutos,
} from './partesTareaDuration'

describe('partesTareaDuration', () => {
  it('valida múltiplo de tramo', () => {
    expect(isValidDuracionMinutos(15, 15)).toBe(true)
    expect(isValidDuracionMinutos(10, 15)).toBe(false)
    expect(isValidDuracionMinutos(0, 15)).toBe(false)
  })

  it('arma opciones de tramo', () => {
    expect(buildTramoOptions(15, 60)).toEqual([15, 30, 45, 60])
  })

  it('detecta fecha futura', () => {
    expect(isFechaFutura('2099-01-01', '2026-07-30')).toBe(true)
    expect(isFechaFutura('2026-07-30', '2026-07-30')).toBe(false)
  })
})
