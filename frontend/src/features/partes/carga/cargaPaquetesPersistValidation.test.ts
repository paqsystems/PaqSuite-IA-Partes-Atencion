import { describe, expect, it } from 'vitest'
import { cargaPaquetesPersistErrorKey } from './cargaPaquetesPersistValidation'
import type { PaqueteFormState } from './cargaPaquetesFormTypes'

function form(partial: Partial<PaqueteFormState> = {}): PaqueteFormState {
  return {
    clienteId: 1,
    fecha: '2026-10-21',
    duracionMinutos: 8 * 60,
    observacion: 'Compra',
    ...partial,
  }
}

describe('cargaPaquetesPersistErrorKey', () => {
  it('exige cliente, fecha, observación y horas enteras entre 8 y 240', () => {
    expect(cargaPaquetesPersistErrorKey(form({ clienteId: null }))).toBe(
      'partes.paquete.camposObligatorios'
    )
    expect(cargaPaquetesPersistErrorKey(form({ fecha: '' }))).toBe(
      'partes.paquete.camposObligatorios'
    )
    expect(cargaPaquetesPersistErrorKey(form({ observacion: '  ' }))).toBe(
      'partes.paquete.observacionRequerida'
    )
    expect(cargaPaquetesPersistErrorKey(form({ duracionMinutos: 7 * 60 }))).toBe(
      'partes.paquete.duracionInvalida'
    )
    expect(cargaPaquetesPersistErrorKey(form({ duracionMinutos: 8 * 60 + 30 }))).toBe(
      'partes.paquete.duracionInvalida'
    )
    expect(cargaPaquetesPersistErrorKey(form({ duracionMinutos: 241 * 60 }))).toBe(
      'partes.paquete.duracionInvalida'
    )
    expect(cargaPaquetesPersistErrorKey(form())).toBeNull()
  })
})
