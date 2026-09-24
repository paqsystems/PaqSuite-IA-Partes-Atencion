export type TipoTareaCatalogItem = {
  id: number
  code?: string
  descripcion?: string
  isGenerico?: boolean
  isDefault?: boolean
}

/** SQL Server / envelope pueden devolver `id` como string; DevExtreme exige tipos estables. */
export function normalizeTiposTareaCatalogItems(
  items: Record<string, unknown>[]
): TipoTareaCatalogItem[] {
  return items.map((row) => ({
    ...row,
    id: Number(row.id),
    isGenerico: row.isGenerico != null ? Boolean(row.isGenerico) : undefined,
    isDefault: row.isDefault != null ? Boolean(row.isDefault) : undefined,
  }))
}

export function pickDefaultTipoTareaId(items: TipoTareaCatalogItem[]): number | null {
  const def = items.find((item) => item.isDefault) ?? items[0]
  return def && Number.isFinite(def.id) ? def.id : null
}

export function isTipoTareaInUniverso(
  tipoTareaId: number | null | undefined,
  items: TipoTareaCatalogItem[]
): boolean {
  if (tipoTareaId == null) {
    return false
  }
  return items.some((item) => item.id === Number(tipoTareaId))
}
