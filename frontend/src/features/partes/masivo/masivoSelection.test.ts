import { describe, expect, it } from 'vitest'
import { isSpuriousMasivoClear, reduceMasivoSelection } from './masivoSelection'
import type { PartesTareaItem } from '../carga/partesTareaApi'

function row(id: number): PartesTareaItem {
  return {
    id,
    usuarioId: 1,
    clienteId: 1,
    tipoTareaId: 1,
    fecha: '2026-09-15',
    duracionMinutos: 15,
    sinCargo: false,
    presencial: false,
    observacion: 'x',
    cerrado: false,
    rowVersion: '1',
    usuarioCode: 'admin',
  }
}

describe('masivoSelection', () => {
  it('ignora vaciado sin destilde explícito', () => {
    expect(
      isSpuriousMasivoClear([11, 12], { selectedRowKeys: [], currentDeselectedRowKeys: [] })
    ).toBe(true)
    expect(
      isSpuriousMasivoClear([11], {
        selectedRowKeys: [],
        currentDeselectedRowKeys: [11],
      }, true)
    ).toBe(false)
  })

  it('ignora destilde programático de applyState (sin intención de usuario)', () => {
    expect(
      isSpuriousMasivoClear([11, 12], {
        selectedRowKeys: [],
        currentDeselectedRowKeys: [11, 12],
      })
    ).toBe(true)
  })

  it('respeta destilde de cabecera iniciado por el usuario', () => {
    const prev = {
      keys: [1, 2],
      map: {
        1: { id: 1, rowVersion: '1' },
        2: { id: 2, rowVersion: '1' },
      },
    }
    const next = reduceMasivoSelection(
      prev,
      {
        selectedRowKeys: [],
        currentDeselectedRowKeys: [1, 2],
      },
      [1, 2],
      true
    )
    expect(next.keys).toEqual([])
  })

  it('conserva la selección si applyState destilda sin intención de usuario', () => {
    const prev = {
      keys: [1, 2],
      map: {
        1: { id: 1, rowVersion: '1' },
        2: { id: 2, rowVersion: '1' },
      },
    }
    const next = reduceMasivoSelection(
      prev,
      {
        selectedRowKeys: [],
        currentDeselectedRowKeys: [1, 2],
      },
      [1, 2]
    )
    expect(next.keys).toEqual([1, 2])
  })

  it('un click de fila no termina en selectedKeys=[]', () => {
    const prev = { keys: [], map: {} }
    const next = reduceMasivoSelection(
      prev,
      {
        selectedRowKeys: [42],
        currentSelectedRowKeys: [42],
        currentDeselectedRowKeys: [],
        selectedRowsData: [row(42)],
      },
      [41, 42, 43]
    )
    expect(next.keys).toEqual([42])
    expect(next.map[42]?.id).toBe(42)
  })

  it('conserva claves off-page al tildar la página', () => {
    const prev = {
      keys: [1, 2, 99],
      map: {
        1: { id: 1, rowVersion: '1' },
        2: { id: 2, rowVersion: '1' },
        99: { id: 99, rowVersion: '1' },
      },
    }
    const next = reduceMasivoSelection(
      prev,
      {
        selectedRowKeys: [1, 2],
        currentSelectedRowKeys: [1, 2],
        currentDeselectedRowKeys: [],
        selectedRowsData: [row(1), row(2)],
      },
      [1, 2]
    )
    expect(next.keys.sort((a, b) => a - b)).toEqual([1, 2, 99])
  })

  it('el select-all de cabecera no se auto-limpia (evento vacío espurio)', () => {
    const prev = {
      keys: [1, 2],
      map: {
        1: { id: 1, rowVersion: '1' },
        2: { id: 2, rowVersion: '1' },
      },
    }
    const next = reduceMasivoSelection(
      prev,
      { selectedRowKeys: [], currentDeselectedRowKeys: [] },
      [1, 2]
    )
    expect(next.keys).toEqual([1, 2])
  })
})
