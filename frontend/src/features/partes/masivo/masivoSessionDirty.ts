export type MasivoSessionDirtyInput = {
  selectedCount: number
  touchSinCargo: boolean
  touchPresencial: boolean
  touchFecha: boolean
  applyTipoTareaId: number | null
  applyUsuarioId: number | null
}

export function computeMasivoSessionDirty(input: MasivoSessionDirtyInput): boolean {
  if (input.selectedCount > 0) {
    return true
  }
  if (
    input.touchSinCargo ||
    input.touchPresencial ||
    input.touchFecha ||
    input.applyTipoTareaId != null ||
    input.applyUsuarioId != null
  ) {
    return true
  }

  return false
}
