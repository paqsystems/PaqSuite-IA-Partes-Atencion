export type ConsultaDetalladaEstadoCerrado = 'todas' | 'abiertas' | 'cerradas'

export type ConsultaDetalladaHostContext = {
  fechaDesde: string
  fechaHasta: string
  clienteId: number | null
  usuarioId: number | null
  tipoTareaId: number | null
  estadoCerrado: ConsultaDetalladaEstadoCerrado
  /** Filas tildadas en la grilla; la emisión solo incluye estos ids (CC ST 29/10/2026). */
  selectedTareaIds: number[]
}

export function buildConsultaDetalladaHostContext(input: {
  fechaDesde: string
  fechaHasta: string
  clienteId: number | null
  usuarioId: number | null
  tipoTareaId: number | null
  estadoCerrado: ConsultaDetalladaEstadoCerrado
  esSupervisor: boolean
  selectedTareaIds: number[]
}): ConsultaDetalladaHostContext {
  return {
    fechaDesde: input.fechaDesde,
    fechaHasta: input.fechaHasta,
    clienteId: input.clienteId,
    usuarioId: input.esSupervisor ? input.usuarioId : null,
    tipoTareaId: input.tipoTareaId,
    estadoCerrado: input.estadoCerrado,
    selectedTareaIds: [...input.selectedTareaIds],
  }
}

export function shouldDisableConsultaDetalladaEmit(
  loading: boolean,
  total: number,
  selectedCount: number,
): boolean {
  return loading || total === 0 || selectedCount === 0
}

export function shouldMountConsultaDetalladaEmit(isNative: boolean, emissionEnabled: boolean): boolean {
  return !isNative && emissionEnabled
}
