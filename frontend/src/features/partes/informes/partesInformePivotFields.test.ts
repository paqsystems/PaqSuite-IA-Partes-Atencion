import { describe, expect, it } from 'vitest'
import {
  buildConsultaAgrupadaPivotFields,
  buildConsultaDetalladaPivotFields,
} from './partesInformePivotFields'

const t = ((key: string) => `i18n:${key}`) as unknown as import('i18next').TFunction

describe('partesInformePivotFields', () => {
  it('detalle: captions i18n, diaSemana y sin campos técnicos', () => {
    const fields = buildConsultaDetalladaPivotFields(t)
    expect(fields.every((field) => String(field.caption).startsWith('i18n:'))).toBe(true)
    expect(fields.some((field) => field.dataField === 'diaSemana')).toBe(true)
    expect(fields.some((field) => field.dataField === 'clienteCode')).toBe(true)
    expect(fields.some((field) => field.dataField === 'id')).toBe(false)
    expect(fields.some((field) => field.dataField === 'createdAt')).toBe(false)
  })

  it('agrupada: captions i18n e incluye diaSemana', () => {
    const fields = buildConsultaAgrupadaPivotFields(t)
    expect(fields.map((field) => field.dataField)).toEqual([
      'ejeCodigo',
      'ejeDescripcion',
      'diaSemana',
      'totalMinutos',
      'cantidadTareas',
      'cantidadSinCargo',
      'cantidadPresencial',
    ])
    expect(fields.every((field) => String(field.caption).startsWith('i18n:'))).toBe(true)
  })
})
