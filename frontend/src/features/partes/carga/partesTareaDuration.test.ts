import { describe, expect, it } from 'vitest'
import {
  buildTramoHhMmOptions,
  buildTramoOptions,
  formatMinutosAsHhMm,
  isFechaFutura,
  isoDateFromDateBox,
  isValidDuracionMinutos,
  minutosToHorasDecimal,
  parseHhMmToMinutos,
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

  it('formatea minutos como hh:mm', () => {
    expect(formatMinutosAsHhMm(15)).toBe('00:15')
    expect(formatMinutosAsHhMm(60)).toBe('01:00')
    expect(formatMinutosAsHhMm(90)).toBe('01:30')
    expect(formatMinutosAsHhMm(1440)).toBe('24:00')
  })

  it('convierte minutos a horas decimales', () => {
    expect(minutosToHorasDecimal(30)).toBe(0.5)
    expect(minutosToHorasDecimal(90)).toBe(1.5)
    expect(minutosToHorasDecimal(15)).toBe(0.25)
  })

  it('parsea hh:mm a minutos', () => {
    expect(parseHhMmToMinutos('00:15')).toBe(15)
    expect(parseHhMmToMinutos('1:30')).toBe(90)
    expect(parseHhMmToMinutos('24:00')).toBe(1440)
    expect(parseHhMmToMinutos('00:00')).toBe(null)
    expect(parseHhMmToMinutos('abc')).toBe(null)
  })

  it('arma opciones de tramo en hh:mm', () => {
    expect(buildTramoHhMmOptions(15, 60)).toEqual([
      { minutos: 15, label: '00:15' },
      { minutos: 30, label: '00:30' },
      { minutos: 45, label: '00:45' },
      { minutos: 60, label: '01:00' },
    ])
  })

  it('detecta fecha futura', () => {
    expect(isFechaFutura('2099-01-01', '2026-07-30')).toBe(true)
    expect(isFechaFutura('2026-07-30', '2026-07-30')).toBe(false)
  })

  it('isoDateFromDateBox ignora cambios programáticos', () => {
    expect(isoDateFromDateBox({ value: '2026-08-01' })).toBe(null)
    expect(isoDateFromDateBox({ event: new Event('change'), value: '2026-08-01T12:00:00' })).toBe(
      '2026-08-01'
    )
    expect(isoDateFromDateBox({ event: new Event('change'), value: null })).toBe('')
    expect(
      isoDateFromDateBox({ event: new Event('change'), value: new Date(2026, 7, 1) })
    ).toBe('2026-08-01')
  })
})
