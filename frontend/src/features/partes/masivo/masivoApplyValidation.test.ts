import { describe, expect, it } from 'vitest'
import { masivoApplyFechaErrorKey } from './masivoApplyValidation'

describe('masivoApplyFechaErrorKey', () => {
  it('no valida si no se toca la fecha', () => {
    expect(masivoApplyFechaErrorKey(false, '0000-09-16')).toBeNull()
  })

  it('rechaza fechas ISO inválidas', () => {
    expect(masivoApplyFechaErrorKey(true, '0000-09-16')).toBe('partes.tarea.fechaInvalida')
    expect(masivoApplyFechaErrorKey(true, '2026-02-30')).toBe('partes.tarea.fechaInvalida')
    expect(masivoApplyFechaErrorKey(true, '')).toBe('partes.tarea.fechaInvalida')
  })

  it('acepta fechas ISO válidas', () => {
    expect(masivoApplyFechaErrorKey(true, '2026-09-16')).toBeNull()
  })
})
