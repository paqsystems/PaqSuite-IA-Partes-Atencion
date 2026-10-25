export const PAQUETE_MIN_HORAS = 8
export const PAQUETE_MAX_HORAS = 240
export const PAQUETE_MIN_MINUTOS = PAQUETE_MIN_HORAS * 60
export const PAQUETE_MAX_MINUTOS = PAQUETE_MAX_HORAS * 60

export function isValidPaqueteDuracionMinutos(duracionMinutos: number): boolean {
  return (
    Number.isInteger(duracionMinutos) &&
    duracionMinutos >= PAQUETE_MIN_MINUTOS &&
    duracionMinutos <= PAQUETE_MAX_MINUTOS &&
    duracionMinutos % 60 === 0
  )
}

export type PaqueteDuracionOption = {
  minutos: number
  horas: number
  label: string
}

export function buildPaqueteDuracionOptions(
  labelForHours: (horas: number) => string = (horas) => `${horas} h`,
): PaqueteDuracionOption[] {
  return Array.from(
    { length: PAQUETE_MAX_HORAS - PAQUETE_MIN_HORAS + 1 },
    (_, index) => {
      const horas = PAQUETE_MIN_HORAS + index
      return {
        minutos: horas * 60,
        horas,
        label: labelForHours(horas),
      }
    },
  )
}
