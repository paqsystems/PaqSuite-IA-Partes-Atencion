import type { TFunction } from 'i18next'

const WEEKDAY_KEYS = [
  'domingo',
  'lunes',
  'martes',
  'miercoles',
  'jueves',
  'viernes',
  'sabado',
] as const

/** Extrae YYYY-MM-DD al inicio del string (ignora hora / mes YYYY-MM). */
export function extractIsoDate(value: unknown): string | null {
  const text = String(value ?? '').trim()
  const match = /^(\d{4})-(\d{2})-(\d{2})/.exec(text)
  if (!match) {
    return null
  }
  return `${match[1]}-${match[2]}-${match[3]}`
}

/**
 * Nombre localizado del día de la semana (lunes…domingo).
 * Fecha en calendario local (evita desfase UTC de `new Date('YYYY-MM-DD')`).
 */
export function resolveDiaSemanaLabel(t: TFunction, value: unknown): string {
  const iso = extractIsoDate(value)
  if (!iso) {
    return ''
  }
  const [y, m, d] = iso.split('-').map(Number)
  const date = new Date(y, m - 1, d)
  if (Number.isNaN(date.getTime())) {
    return ''
  }
  const key = WEEKDAY_KEYS[date.getDay()]
  return t(`partes.informe.weekday.${key}`)
}

/** Agrega `diaSemana` a cada fila a partir de un campo fecha ISO. */
export function enrichRowsWithDiaSemana<T extends Record<string, unknown>>(
  rows: T[],
  t: TFunction,
  dateField: keyof T | string
): Array<T & { diaSemana: string }> {
  return rows.map((row) => ({
    ...row,
    diaSemana: resolveDiaSemanaLabel(t, row[dateField as string]),
  }))
}
