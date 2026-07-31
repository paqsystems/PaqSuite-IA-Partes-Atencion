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

export function todayIsoDate(date = new Date()): string {
  const y = date.getFullYear()
  const m = String(date.getMonth() + 1).padStart(2, '0')
  const d = String(date.getDate()).padStart(2, '0')
  return `${y}-${m}-${d}`
}

export function isFechaFutura(fechaIso: string, hoyIso = todayIsoDate()): boolean {
  return fechaIso > hoyIso
}
