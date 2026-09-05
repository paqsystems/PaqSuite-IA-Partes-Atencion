import { describe, expect, it } from 'vitest'
import {
  buildConsultaDetalladaHostContext,
  shouldDisableConsultaDetalladaEmit,
  shouldMountConsultaDetalladaEmit,
} from './consultaDetalladaHostContext'

describe('consultaDetalladaHostContext', () => {
  it('arma snapshot de filtros actuales e ignora usuarioId si no es supervisor', () => {
    const ctx = buildConsultaDetalladaHostContext({
      fechaDesde: '2026-08-01',
      fechaHasta: '2026-08-31',
      clienteId: 4,
      usuarioId: 9,
      tipoTareaId: 2,
      estadoCerrado: 'abiertas',
      esSupervisor: false,
    })
    expect(ctx).toEqual({
      fechaDesde: '2026-08-01',
      fechaHasta: '2026-08-31',
      clienteId: 4,
      usuarioId: null,
      tipoTareaId: 2,
      estadoCerrado: 'abiertas',
    })
  })

  it('conserva usuarioId si es supervisor', () => {
    const ctx = buildConsultaDetalladaHostContext({
      fechaDesde: '2026-08-01',
      fechaHasta: '2026-08-31',
      clienteId: null,
      usuarioId: 9,
      tipoTareaId: null,
      estadoCerrado: 'todas',
      esSupervisor: true,
    })
    expect(ctx.usuarioId).toBe(9)
  })

  it('deshabilita Emitir si loading o total 0 del último Buscar', () => {
    expect(shouldDisableConsultaDetalladaEmit(true, 10)).toBe(true)
    expect(shouldDisableConsultaDetalladaEmit(false, 0)).toBe(true)
    expect(shouldDisableConsultaDetalladaEmit(false, 3)).toBe(false)
  })

  it('no monta Emitir en native ni con capacidad off', () => {
    expect(shouldMountConsultaDetalladaEmit(true, true)).toBe(false)
    expect(shouldMountConsultaDetalladaEmit(false, false)).toBe(false)
    expect(shouldMountConsultaDetalladaEmit(false, true)).toBe(true)
  })
})
