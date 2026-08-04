/** Estado del formulario de alta/edición en Carga diaria (compartido con Smart Capture). */
export type FormState = {
  usuarioId: number | null
  clienteId: number | null
  tipoTareaId: number | null
  fecha: string
  duracionMinutos: number
  sinCargo: boolean
  presencial: boolean
  observacion: string
  rowVersion?: string
}
