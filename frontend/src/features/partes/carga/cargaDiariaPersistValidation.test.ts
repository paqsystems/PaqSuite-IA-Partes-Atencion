import { describe, expect, it } from 'vitest'
import type { FormState } from './cargaDiariaFormTypes'
import { cargaDiariaPersistErrorKey } from './cargaDiariaPersistValidation'

function validForm(overrides: Partial<FormState> = {}): FormState {
  return {
    usuarioId: 1,
    clienteId: 2,
    tipoTareaId: 3,
    fecha: '2026-09-16',
    duracionMinutos: 15,
    sinCargo: false,
    presencial: false,
    observacion: 'Reunión de avance',
    ...overrides,
  }
}

describe('cargaDiariaPersistErrorKey', () => {
  it('acepta un alta completa', () => {
    expect(cargaDiariaPersistErrorKey(validForm(), 15)).toBeNull()
  })

  it('exige cliente, tipo, usuario y fecha', () => {
    expect(cargaDiariaPersistErrorKey(validForm({ clienteId: null }), 15)).toBe(
      'partes.tarea.camposObligatorios'
    )
    expect(cargaDiariaPersistErrorKey(validForm({ tipoTareaId: null }), 15)).toBe(
      'partes.tarea.camposObligatorios'
    )
    expect(cargaDiariaPersistErrorKey(validForm({ usuarioId: null }), 15)).toBe(
      'partes.tarea.camposObligatorios'
    )
    expect(cargaDiariaPersistErrorKey(validForm({ fecha: '' }), 15)).toBe(
      'partes.tarea.camposObligatorios'
    )
  })

  it('exige observación no vacía', () => {
    expect(cargaDiariaPersistErrorKey(validForm({ observacion: '   ' }), 15)).toBe(
      'partes.tarea.observacionRequerida'
    )
  })

  it('rechaza duración fuera de tramo', () => {
    expect(cargaDiariaPersistErrorKey(validForm({ duracionMinutos: 10 }), 15)).toBe(
      'partes.tarea.duracionInvalida'
    )
  })
})
