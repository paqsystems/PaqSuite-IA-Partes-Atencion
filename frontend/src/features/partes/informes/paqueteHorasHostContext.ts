export type PaqueteHorasHostContext = {
  fechaDesde: string
  fechaHasta: string
  clienteId: number
  tipoClienteId: number | null
  /** Ids de filas tildadas (movimientos; excluye saldo inicial). Paridad CC-ST #1. */
  selectedTareaIds: number[]
}

export function buildPaqueteHorasHostContext(input: {
  fechaDesde: string
  fechaHasta: string
  clienteId: number
  tipoClienteId: number | null
  selectedTareaIds: number[]
}): PaqueteHorasHostContext {
  return {
    fechaDesde: input.fechaDesde,
    fechaHasta: input.fechaHasta,
    clienteId: input.clienteId,
    tipoClienteId: input.tipoClienteId,
    selectedTareaIds: [...input.selectedTareaIds],
  }
}

export function shouldDisablePaqueteHorasEmit(
  loading: boolean,
  selectedCount: number,
  needsClienteSelection: boolean,
): boolean {
  return loading || needsClienteSelection || selectedCount === 0
}

export function shouldMountPaqueteHorasEmit(isNative: boolean, emissionEnabled: boolean): boolean {
  return !isNative && emissionEnabled
}
