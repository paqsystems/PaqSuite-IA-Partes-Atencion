import { describe, expect, it } from 'vitest'
import type { PartesTareaItem } from '../carga/partesTareaApi'
import { aggregatePaqueteHorasDesglose } from './aggregatePaqueteHorasDesglose'
import { mapPartesTareaToKardexItem } from './mapPartesTareaToKardexItem'

function stubTarea(partial: Partial<PartesTareaItem> & Pick<PartesTareaItem, 'id'>): PartesTareaItem {
  return {
    usuarioId: 1,
    clienteId: 2,
    tipoTareaId: 3,
    fecha: '2026-08-17',
    duracionMinutos: 30,
    sinCargo: false,
    presencial: false,
    observacion: 'Reunión',
    cerrado: false,
    rowVersion: 'rv',
    clienteCode: 'CLI',
    clienteNombre: 'Acme',
    tipoTareaCode: 'SOP',
    ...partial,
  }
}

describe('mapPartesTareaToKardexItem', () => {
  const t = (key: string) => key

  it('mapea tarea abierta', () => {
    const item = mapPartesTareaToKardexItem(stubTarea({ id: 10, cerrado: false }), t)
    expect(item.id).toBe('10')
    expect(item.title).toContain('CLI')
    expect(item.status?.tone).toBe('neutral')
    expect(item.status?.text).toBe('partes.mobile.abierta')
  })

  it('mapea tarea cerrada', () => {
    const item = mapPartesTareaToKardexItem(stubTarea({ id: 11, cerrado: true }), t)
    expect(item.status?.tone).toBe('success')
    expect(item.status?.text).toBe('partes.mobile.cerrada')
  })
})

describe('aggregatePaqueteHorasDesglose', () => {
  it('agrupa por cliente omitiendo saldo inicial', () => {
    const items = aggregatePaqueteHorasDesglose(
      [
        { esSaldoInicial: true, clienteCode: 'X', duracionMinutos: 100, esTarea: true },
        {
          clienteCode: 'A',
          clienteNombre: 'Uno',
          duracionMinutos: 30,
          esTarea: true,
        },
        {
          clienteCode: 'A',
          clienteNombre: 'Uno',
          duracionMinutos: 15,
          esTarea: true,
        },
      ],
      'cliente',
    )
    expect(items).toHaveLength(1)
    expect(items[0].id).toBe('A')
    expect(items[0].totalMinutos).toBe(45)
    expect(items[0].cantidad).toBe(2)
  })
})
