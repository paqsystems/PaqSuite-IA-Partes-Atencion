import type { MasivoCamposUpdate } from './partesMasivoApi'

/** Arma payload de actualización masiva (Must + Should; misma regla que la UI). */
export function buildMasivoCamposUpdate(input: {
  tipoTareaId: number | null
  touchSinCargo: boolean
  sinCargo: boolean
  touchPresencial?: boolean
  presencial?: boolean
  usuarioId?: number | null
  fecha?: string | null
}): MasivoCamposUpdate | null {
  const campos: MasivoCamposUpdate = {}
  if (input.tipoTareaId != null) {
    campos.tipoTareaId = input.tipoTareaId
  }
  if (input.touchSinCargo) {
    campos.sinCargo = input.sinCargo
  }
  if (input.touchPresencial) {
    campos.presencial = Boolean(input.presencial)
  }
  if (input.usuarioId != null) {
    campos.usuarioId = input.usuarioId
  }
  const fecha = input.fecha?.trim()
  if (fecha) {
    campos.fecha = fecha
  }
  return Object.keys(campos).length === 0 ? null : campos
}
