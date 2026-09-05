export type ConsultaDetalladaEstadoCerrado = 'todas' | 'abiertas' | 'cerradas'

export type ConsultaDetalladaHostContext = {
  fechaDesde: string
  fechaHasta: string
  clienteId: number | null
  usuarioId: number | null
  tipoTareaId: number | null
  estadoCerrado: ConsultaDetalladaEstadoCerrado
}

export function buildConsultaDetalladaHostContext(input: {
  fechaDesde: string
  fechaHasta: string
  clienteId: number | null
  usuarioId: number | null
  tipoTareaId: number | null
  estadoCerrado: ConsultaDetalladaEstadoCerrado
  esSupervisor: boolean
}): ConsultaDetalladaHostContext {
  return {
    fechaDesde: input.fechaDesde,
    fechaHasta: input.fechaHasta,
    clienteId: input.clienteId,
    usuarioId: input.esSupervisor ? input.usuarioId : null,
    tipoTareaId: input.tipoTareaId,
    estadoCerrado: input.estadoCerrado,
  }
}

export function shouldDisableConsultaDetalladaEmit(loading: boolean, total: number): boolean {
  return loading || total === 0
}

export function shouldMountConsultaDetalladaEmit(isNative: boolean, emissionEnabled: boolean): boolean {
  return !isNative && emissionEnabled
}
