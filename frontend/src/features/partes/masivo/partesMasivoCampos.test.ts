import { describe, expect, it } from 'vitest'
import { buildMasivoCamposUpdate } from './partesMasivoCampos'

describe('buildMasivoCamposUpdate', () => {
  it('devuelve null si no hay cambios', () => {
    expect(
      buildMasivoCamposUpdate({ tipoTareaId: null, touchSinCargo: false, sinCargo: false })
    ).toBeNull()
  })

  it('incluye tipo y sin cargo cuando se tocan', () => {
    expect(
      buildMasivoCamposUpdate({ tipoTareaId: 7, touchSinCargo: true, sinCargo: true })
    ).toEqual({ tipoTareaId: 7, sinCargo: true })
  })

  it('permite solo sin cargo en false', () => {
    expect(
      buildMasivoCamposUpdate({ tipoTareaId: null, touchSinCargo: true, sinCargo: false })
    ).toEqual({ sinCargo: false })
  })

  it('incluye Should presencial, asistente y fecha', () => {
    expect(
      buildMasivoCamposUpdate({
        tipoTareaId: null,
        touchSinCargo: false,
        sinCargo: false,
        touchPresencial: true,
        presencial: true,
        usuarioId: 3,
        fecha: '2026-07-15',
      })
    ).toEqual({ presencial: true, usuarioId: 3, fecha: '2026-07-15' })
  })
})
