import { describe, expect, it } from 'vitest'
import {
  enrichRowsWithDiaSemana,
  extractIsoDate,
  resolveDiaSemanaLabel,
} from './partesInformeDiaSemana'

const t = ((key: string) => {
  const map: Record<string, string> = {
    'partes.informe.weekday.domingo': 'Domingo',
    'partes.informe.weekday.lunes': 'Lunes',
    'partes.informe.weekday.martes': 'Martes',
    'partes.informe.weekday.miercoles': 'Miércoles',
    'partes.informe.weekday.jueves': 'Jueves',
    'partes.informe.weekday.viernes': 'Viernes',
    'partes.informe.weekday.sabado': 'Sábado',
  }
  return map[key] ?? key
}) as unknown as import('i18next').TFunction

describe('partesInformeDiaSemana', () => {
  it('extrae YYYY-MM-DD', () => {
    expect(extractIsoDate('2026-07-31')).toBe('2026-07-31')
    expect(extractIsoDate('2026-07-31T12:00:00Z')).toBe('2026-07-31')
    expect(extractIsoDate('2026-07')).toBe(null)
  })

  it('resuelve día de la semana localizado', () => {
    // 2026-07-31 was a Friday
    expect(resolveDiaSemanaLabel(t, '2026-07-31')).toBe('Viernes')
    expect(resolveDiaSemanaLabel(t, '2026-07-27')).toBe('Lunes')
  })

  it('enriquece filas', () => {
    const rows = enrichRowsWithDiaSemana([{ fecha: '2026-07-31', id: 1 }], t, 'fecha')
    expect(rows[0].diaSemana).toBe('Viernes')
  })
})
