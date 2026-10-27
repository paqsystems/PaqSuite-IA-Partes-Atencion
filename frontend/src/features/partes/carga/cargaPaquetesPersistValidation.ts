import type { PaqueteFormState } from './cargaPaquetesFormTypes'
import { isValidPaqueteDuracionMinutos } from './partesPaqueteDuration'

export function cargaPaquetesPersistErrorKey(
  form: PaqueteFormState
): string | null {
  if (form.clienteId == null || Number(form.clienteId) <= 0) {
    return 'partes.paquete.camposObligatorios'
  }
  if (!String(form.fecha ?? '').trim()) {
    return 'partes.paquete.camposObligatorios'
  }
  if (!String(form.observacion ?? '').trim()) {
    return 'partes.paquete.observacionRequerida'
  }
  if (!isValidPaqueteDuracionMinutos(Number(form.duracionMinutos))) {
    return 'partes.paquete.duracionInvalida'
  }
  return null
}
