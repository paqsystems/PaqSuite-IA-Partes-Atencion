import type { FormState } from './cargaDiariaFormTypes'
import { isValidDuracionMinutos } from './partesTareaDuration'

/** Clave i18n si el alta/edición no puede persistirse; null si el body es enviable. */
export function cargaDiariaPersistErrorKey(form: FormState, tramoMinutos: number): string | null {
  if (form.usuarioId == null || Number(form.usuarioId) <= 0) {
    return 'partes.tarea.camposObligatorios'
  }
  if (form.clienteId == null || Number(form.clienteId) <= 0) {
    return 'partes.tarea.camposObligatorios'
  }
  if (form.tipoTareaId == null || Number(form.tipoTareaId) <= 0) {
    return 'partes.tarea.camposObligatorios'
  }
  if (!String(form.fecha ?? '').trim()) {
    return 'partes.tarea.camposObligatorios'
  }
  if (!String(form.observacion ?? '').trim()) {
    return 'partes.tarea.observacionRequerida'
  }
  if (!isValidDuracionMinutos(Number(form.duracionMinutos), tramoMinutos)) {
    return 'partes.tarea.duracionInvalida'
  }
  return null
}
