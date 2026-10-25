import { describe, expect, it } from 'vitest'
import {
  buildPaqueteDuracionOptions,
  isValidPaqueteDuracionMinutos,
} from './partesPaqueteDuration'

describe('duración de paquetes', () => {
  it('acepta únicamente horas enteras entre 8 y 240', () => {
    expect(isValidPaqueteDuracionMinutos(8 * 60)).toBe(true)
    expect(isValidPaqueteDuracionMinutos(240 * 60)).toBe(true)
    expect(isValidPaqueteDuracionMinutos(7 * 60)).toBe(false)
    expect(isValidPaqueteDuracionMinutos(8 * 60 + 30)).toBe(false)
    expect(isValidPaqueteDuracionMinutos(241 * 60)).toBe(false)
  })

  it('genera una opción por cada hora permitida', () => {
    const options = buildPaqueteDuracionOptions((hours) => `${hours} h`)

    expect(options).toHaveLength(233)
    expect(options[0]).toEqual({ minutos: 480, horas: 8, label: '8 h' })
    expect(options.at(-1)).toEqual({ minutos: 14400, horas: 240, label: '240 h' })
  })
})
