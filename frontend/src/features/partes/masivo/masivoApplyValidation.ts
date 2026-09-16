import { isValidIsoDate } from '../carga/partesTareaDuration'

/** Clave i18n si la fecha de lote no puede aplicarse; null si es válida o no aplica. */
export function masivoApplyFechaErrorKey(touchFecha: boolean, applyFecha: string): string | null {
  if (!touchFecha) {
    return null
  }
  const fecha = applyFecha.trim()
  if (!fecha || !isValidIsoDate(fecha)) {
    return 'partes.tarea.fechaInvalida'
  }
  return null
}
