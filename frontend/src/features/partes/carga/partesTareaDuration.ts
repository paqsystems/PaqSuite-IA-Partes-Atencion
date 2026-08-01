/** Valida duración de tarea Partes (múltiplo de tramo, >0, ≤1440). */
export function isValidDuracionMinutos(duracion: number, tramoMinutos: number): boolean {
  const tramo = tramoMinutos > 0 ? tramoMinutos : 15
  return Number.isInteger(duracion) && duracion > 0 && duracion <= 1440 && duracion % tramo === 0
}

export function buildTramoOptions(tramoMinutos: number, maxMinutos = 480): number[] {
  const tramo = tramoMinutos > 0 ? tramoMinutos : 15
  const options: number[] = []
  for (let value = tramo; value <= maxMinutos; value += tramo) {
    options.push(value)
  }
  return options
}

/** Formato de presentación hh:mm (persistencia sigue en minutos enteros). */
export function formatMinutosAsHhMm(minutos: number): string {
  if (!Number.isFinite(minutos) || minutos < 0) {
    return '00:00'
  }
  const total = Math.round(minutos)
  const hours = Math.floor(total / 60)
  const mins = total % 60
  return `${String(hours).padStart(2, '0')}:${String(mins).padStart(2, '0')}`
}

/** Convierte minutos a horas decimales para sumatoria en grilla (p. ej. 90 → 1.5). */
export function minutosToHorasDecimal(minutos: number): number {
  if (!Number.isFinite(minutos)) {
    return 0
  }
  return Math.round((minutos / 60) * 10000) / 10000
}

/** Parsea "hh:mm" o "h:mm" a minutos; null si inválido. */
export function parseHhMmToMinutos(value: string): number | null {
  const trimmed = String(value ?? '').trim()
  const match = /^(\d{1,2}):([0-5]\d)$/.exec(trimmed)
  if (!match) {
    return null
  }
  const hours = Number(match[1])
  const mins = Number(match[2])
  const total = hours * 60 + mins
  if (total <= 0 || total > 1440) {
    return null
  }
  return total
}

export type TramoHhMmOption = {
  minutos: number
  label: string
}

export function buildTramoHhMmOptions(
  tramoMinutos: number,
  maxMinutos = 480
): TramoHhMmOption[] {
  return buildTramoOptions(tramoMinutos, maxMinutos).map((minutos) => ({
    minutos,
    label: formatMinutosAsHhMm(minutos),
  }))
}

export function todayIsoDate(date = new Date()): string {
  const y = date.getFullYear()
  const m = String(date.getMonth() + 1).padStart(2, '0')
  const d = String(date.getDate()).padStart(2, '0')
  return `${y}-${m}-${d}`
}

export function isFechaFutura(fechaIso: string, hoyIso = todayIsoDate()): boolean {
  return fechaIso > hoyIso
}
